<?php

define("SYMLINK_SEPARATOR", "^"); // this is the separator between the two parts of the symbolic link definition

/*
* This class handles the installation itself. It has functions for installing and for cleaning up.
*/
class Installer
{
	const BASE_COMPONENT = 'base';

	/**
	 * @var resource
	 */
	private $uninstallConfig;

	/**
	 * @var array
	 */
	private $installConfig;

	/**
	 * Array of tasks that should be done once during the installation, for different components
	 * @var array
	 */
	private $runOnce = array();

	/**
	 * Array of the components that should be installed
	 * @var array
	 */
	private $components = array(Installer::BASE_COMPONENT);

	/**
	 * Crteate a new installer, loads installation configurations from installation configuration file
	 * @param array|string $components
	 */
	public function __construct()
	{
		if(OsUtils::isWindows())
			$this->installConfig = parse_ini_file(__DIR__ . '/installation.windows.ini', true);
		else
			$this->installConfig = parse_ini_file(__DIR__ . '/installation.ini', true);

		$components = AppConfig::getCurrentMachineComponents();
		foreach($components as $component)
		{
			if(isset($this->installConfig[$component]))
			{
				$this->components[] = $component;
			}
			elseif ($component == '*')
			{
				foreach($this->installConfig as $component => $config)
					if(isset($config['install_by_default']) && $config['install_by_default'])
						$this->components[] = $component;
			}
		}
		$this->components = array_unique($this->components);
		Logger::logMessage(Logger::LEVEL_INFO, "Installed components: " . implode(', ', $this->components));
	}

	public function __destruct()
	{
		if($this->uninstallConfig)
			fclose($this->uninstallConfig);
	}

	private function createOperatingSystemUsers()
	{
		$users = array();
		
		foreach($this->components as $component)
		{
			if(isset($this->installConfig[$component]) && isset($this->installConfig[$component]['users']))
				$users = array_merge($users, $this->installConfig[$component]['users']);
		}
		$users = implode(',', array_unique($users));
				
		Logger::logMessage(Logger::LEVEL_USER, "Creating operating system users");
		$dir = __DIR__ . '/../osUsers';
		return OsUtils::phing($dir, 'Create-Users', array('users' => $users));
	}

	/**
	 * Saves the uninstaller config file, the values saved are the minimal values subset needed for the uninstaller to run
	 */
	public function saveUninstallerConfig()
	{
		$uninstallerDir = AppConfig::get(AppConfigAttribute::BASE_DIR) . '/uninstaller';
		if(!file_exists($uninstallerDir))
			mkdir($uninstallerDir, 0750, true);

		$this->uninstallConfig = fopen("$uninstallerDir/uninstall.ini", 'w');

		fwrite($this->uninstallConfig, "BASE_DIR=" . AppConfig::get(AppConfigAttribute::BASE_DIR) . PHP_EOL);
		fwrite($this->uninstallConfig, "DB_HOST=" . AppConfig::get(AppConfigAttribute::DB1_HOST) . PHP_EOL);
		fwrite($this->uninstallConfig, "DB_USER=" . AppConfig::get(AppConfigAttribute::DB1_USER) . PHP_EOL);
		fwrite($this->uninstallConfig, "DB_PASS=" . AppConfig::get(AppConfigAttribute::DB1_PASS) . PHP_EOL);
		fwrite($this->uninstallConfig, "DB_PORT=" . AppConfig::get(AppConfigAttribute::DB1_PORT) . PHP_EOL);
		fwrite($this->uninstallConfig, PHP_EOL);
	}

	// detects if there are leftovers of an installation
	// can be used both before installation to verify and when the installation failed for cleaning up
	// $report_only - if set to true only returns the leftovers found and does not removes them
	// returns null if no leftovers are found or it is not report only or a text containing all the leftovers found
	public function detectLeftovers($report_only) {
		$leftovers = null;
	
		if(!$report_only)
		{
			// stop sphinx of installed previous versions
			if(OsUtils::isLinux())
			{
				OsUtils::executeInBackground('/etc/init.d/serviceBatchMgr.sh stop');
				OsUtils::executeInBackground('/etc/init.d/sphinx_watch.sh stop');
			}
		
			Logger::logMessage(Logger::LEVEL_USER, "Removing symbolic links");
		}
					
		$uninstallerConfigPath = AppConfig::get(AppConfigAttribute::BASE_DIR) . '/uninstaller/uninstall.ini';
		if(file_exists($uninstallerConfigPath))
		{
			$uninstallerConfig = parse_ini_file($uninstallerConfigPath);
			if($uninstallerConfig && isset($uninstallerConfig['symlinks']))
			{
				foreach ($uninstallerConfig['symlinks'] as $link)
				{
					if(strpos($link, 'my_kaltura.conf'))
						$uninstallerConfig['symlinks'][] = str_replace('my_kaltura.conf', 'my_kaltura.ssl.conf', $link);
				}
				
				foreach ($uninstallerConfig['symlinks'] as $link)
				{		
					if (is_file($link) && (strpos($link, AppConfig::get(AppConfigAttribute::BASE_DIR)) === false))
					{
						if ($report_only)
						{
							$leftovers .= "   ".$link." symbolic link exists".PHP_EOL;
						}
						else
						{
							Logger::logMessage(Logger::LEVEL_INFO, "Removing symbolic link $link");
							OsUtils::recursiveDelete($link);
						}
					}
				}
			}
		}
		
		foreach($this->installConfig as $component => $config)
		{
			if(!isset($config['symlinks']) && !isset($config['crons']))
				continue;

			Logger::logMessage(Logger::LEVEL_INFO, "Searching symbolic links");
			$symlinks = array();
			if(isset($config['symlinks']) && is_array($config['symlinks']))
				$symlinks = $config['symlinks'];
			if(isset($config['crons']) && is_array($config['crons']))
				$symlinks = array_merge($symlinks, $config['crons']);
				
			foreach ($symlinks as $slink)
			{
				list($target, $link) = explode(SYMLINK_SEPARATOR, AppConfig::replaceTokensInString($slink));
				if (is_file($link) && (strpos($link, AppConfig::get(AppConfigAttribute::BASE_DIR)) === false))
				{
					if ($report_only)
					{
						$leftovers .= "   ".$link." symbolic link exists".PHP_EOL;
					}
					else
					{
						Logger::logMessage(Logger::LEVEL_INFO, "Removing symbolic link $link");
						OsUtils::recursiveDelete($link);
					}
				}
			}
		}

		if(in_array('db', $this->components))
		{
			foreach($this->installConfig as $component => $config)
			{
				if(!isset($config['databases']) || !is_array($config['databases']))
					continue;
	
				Logger::logMessage(Logger::LEVEL_INFO, "Searching databases");
				
				$verify = $this->detectDatabases($config['databases']);
				if (!isset($verify))
					continue;
	
				if(!AppConfig::get(AppConfigAttribute::DB1_CREATE_NEW_DB))
					continue;
	
				if ($report_only)
				{
					$leftovers .= $verify;
				}
				else {
					$this->detectDatabases($config['databases'], true);
				}
			}
		}

		if(!$report_only)
		{
			foreach($this->installConfig as $component => $config)
			{
				if(isset($config['chkconfig']) && is_array($config['chkconfig']))
				{
					Logger::logMessage(Logger::LEVEL_INFO, "Stopping services");
	
					foreach ($config['chkconfig'] as $service)
						OsUtils::stopService($service);
				}
			}
			
			if(OsUtils::isLinux())
				OsUtils::executeInBackground('killall -9 searchd');
		}

		if (is_dir(AppConfig::get(AppConfigAttribute::BASE_DIR)) && (($files = @scandir(AppConfig::get(AppConfigAttribute::BASE_DIR))) && count($files) > 2))
		{
			if ($report_only)
			{
				$leftovers .= "   Target directory ".AppConfig::get(AppConfigAttribute::BASE_DIR)." already exists".PHP_EOL;
			}
			elseif(AppConfig::get(AppConfigAttribute::DB1_CREATE_NEW_DB))
			{
				Logger::logMessage(Logger::LEVEL_USER, "Deleting ".AppConfig::get(AppConfigAttribute::BASE_DIR));
				OsUtils::recursiveDelete(AppConfig::get(AppConfigAttribute::BASE_DIR));
			}
			else
			{
				Logger::logMessage(Logger::LEVEL_USER, "Deleting ".AppConfig::get(AppConfigAttribute::BASE_DIR) . " excluding web content");
				OsUtils::recursiveDelete(AppConfig::get(AppConfigAttribute::BASE_DIR), 'web/content');
			}
		}

		return $leftovers;
	}

	private function stopApache()
	{
		if(!in_array('api', $this->components) && !in_array('apps', $this->components) && !in_array('admin', $this->components) && !in_array('var', $this->components))
			return true;
		
		Logger::logMessage(Logger::LEVEL_USER, "Stopping apache http server");
		return OsUtils::execute("service " . AppConfig::get(AppConfigAttribute::APACHE_SERVICE) . " stop");
	}

	private function restartApache($now = false)
	{
		if(!in_array('api', $this->components) && !in_array('apps', $this->components) && !in_array('admin', $this->components) && !in_array('var', $this->components))
			return true;
	
		if($now)
		{
			if(in_array('generateClients', $this->runOnce))
			{
				if(OsUtils::isWindows())
				{
					$started = false;
					if(function_exists('win32_start_service'))
					{
						$serviceName = 'Apache2.2';
						win32_stop_service($serviceName);
						win32_start_service($serviceName);
						$timer = 0;
						while(win32_query_service_status($serviceName) != 4 && $timer < 20)
						{
							sleep(1);
							$timer++;
						}
						if(win32_query_service_status($serviceName) == 4)
							$started = true;
					}
					if(!$started)
					{
						AppConfig::getInput(null, "Please restart apache web server and click any key to continue.");
					}
				}
				else
				{
					Logger::logMessage(Logger::LEVEL_USER, "Restarting apache http server");
					return OsUtils::execute("service " . AppConfig::get(AppConfigAttribute::APACHE_SERVICE) . " restart");
				}
			}
			return true;
		}

		$this->runOnce[] = 'restartApache';
		return true;
	}

	private function generateClients($now = false)
	{
		if(!in_array('api', $this->components) && !in_array('batch', $this->components) && !in_array('admin', $this->components) && !in_array('var', $this->components))
			return true;
		
		if($now)
		{
			if(in_array('generateClients', $this->runOnce))
			{
				Logger::logMessage(Logger::LEVEL_USER, "Generating client libraries");
				return OsUtils::execute(sprintf("%s %s/generator/generate.php", AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR)));
			}
			return true;
		}

		$this->runOnce[] = 'generateClients';
		return true;
	}

	private function init()
	{
		AppConfig::set(AppConfigAttribute::KMC_VERSION, AppConfig::getServerConfig('kmc_version'));
		AppConfig::set(AppConfigAttribute::KMC_LOGIN_VERSION, AppConfig::getServerConfig('kmc_login_version'));
		AppConfig::set(AppConfigAttribute::CLIPAPP_VERSION, AppConfig::getServerConfig('clipapp_version'));
		AppConfig::set(AppConfigAttribute::HTML5_VERSION, AppConfig::getServerConfig('html5_version'));
		
		if(OsUtils::isWindows())
		{
			AppConfig::set(AppConfigAttribute::BASE_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::BASE_DIR)));
			AppConfig::set(AppConfigAttribute::APP_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::APP_DIR)));
			AppConfig::set(AppConfigAttribute::WEB_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::WEB_DIR)));
			AppConfig::set(AppConfigAttribute::BIN_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::BIN_DIR)));
			AppConfig::set(AppConfigAttribute::LOG_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::LOG_DIR)));
			AppConfig::set(AppConfigAttribute::TMP_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::TMP_DIR)));
			AppConfig::set(AppConfigAttribute::DWH_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::DWH_DIR)));
			AppConfig::set(AppConfigAttribute::ETL_HOME_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::ETL_HOME_DIR)));
			AppConfig::set(AppConfigAttribute::PHP_BIN, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::PHP_BIN)));
			AppConfig::set(AppConfigAttribute::HTTPD_BIN, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::HTTPD_BIN)));
			AppConfig::set(AppConfigAttribute::LOG_ROTATE_BIN, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::LOG_ROTATE_BIN)));
			AppConfig::set(AppConfigAttribute::IMAGE_MAGICK_BIN_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::IMAGE_MAGICK_BIN_DIR)));
			AppConfig::set(AppConfigAttribute::CURL_BIN_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::CURL_BIN_DIR)));
			AppConfig::set(AppConfigAttribute::SPHINX_BIN_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::SPHINX_BIN_DIR)));
			AppConfig::set(AppConfigAttribute::EVENTS_LOGS_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::EVENTS_LOGS_DIR)));
			AppConfig::set(AppConfigAttribute::STORAGE_BASE_DIR, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::STORAGE_BASE_DIR)));
			AppConfig::set(AppConfigAttribute::SSL_CERTIFICATE_FILE, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::SSL_CERTIFICATE_FILE)));
			AppConfig::set(AppConfigAttribute::SSL_CERTIFICATE_KEY_FILE, OsUtils::windowsPath(AppConfig::get(AppConfigAttribute::SSL_CERTIFICATE_KEY_FILE)));
		}
	}

	/**
	 * Installs the application according to the given parameters\
	 * @return string|NULL null if the installation succeeded or an error text if it failed
	 */
	public function install($packageDir = null, $dontValidate = false)
	{
		$this->init();
		$this->stopApache();
		$this->createOperatingSystemUsers();
		$this->saveUninstallerConfig();

		Logger::logMessage(Logger::LEVEL_INFO, sprintf("Current working dir is %s", getcwd()));
		if ($packageDir)
		{
			Logger::logMessage(Logger::LEVEL_USER, sprintf("Copying application files to %s", AppConfig::get(AppConfigAttribute::BASE_DIR)));

			if (!OsUtils::rsync("$packageDir/", AppConfig::get(AppConfigAttribute::BASE_DIR), "--exclude web/content"))
				return "Failed to copy application files to target directory";

			$copyWebContnet = false;
			
			if(AppConfig::get(AppConfigAttribute::DB1_CREATE_NEW_DB))
			{
				$copyWebContnet = true;
			}
			elseif(AppConfig::get(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT))
			{
				if(in_array('db', $this->components))
				{
					$config = AppConfig::getCurrentMachineConfig();
					if($config && isset($config[AppConfigAttribute::DB1_CREATE_NEW_DB]) && $config[AppConfigAttribute::DB1_CREATE_NEW_DB])
						$copyWebContnet = true;
				}
			}

			if ($copyWebContnet)
			{
				Logger::logMessage(Logger::LEVEL_USER, sprintf("Copying web content files to %s", AppConfig::get(AppConfigAttribute::WEB_DIR)));
				if (!OsUtils::rsync("$packageDir/web/content", AppConfig::get(AppConfigAttribute::WEB_DIR)))
					return "Failed to copy default content into ". AppConfig::get(AppConfigAttribute::WEB_DIR);
			}
		}

		Logger::logMessage(Logger::LEVEL_USER, "Creating the uninstaller");
		if (!OsUtils::fullCopy( __DIR__ . '/uninstall.php', AppConfig::get(AppConfigAttribute::BASE_DIR)."/uninstaller/")) {
			return "Failed to create the uninstaller";
		}

		Logger::logMessage(Logger::LEVEL_USER, "Replacing configuration tokens in files");
		if(isset($this->installConfig[Installer::BASE_COMPONENT]['token_files']) && is_array($this->installConfig[Installer::BASE_COMPONENT]['token_files']))
		{
			foreach ($this->installConfig[Installer::BASE_COMPONENT]['token_files'] as $tokenFile)
			{
				$files = glob(AppConfig::replaceTokensInString($tokenFile));
				foreach($files as $file)
				{
					if (!AppConfig::replaceTokensInFile($file))
						return "Failed to replace tokens in $file";
				}
			}
		}

		Logger::logMessage(Logger::LEVEL_USER, "Creating symbolic links");
		foreach($this->components as $component)
			if($component != 'ssl')
				$this->installComponentSymlinks($component, 'symlinks');
		if(in_array('ssl', $this->components))
			$this->installComponentSymlinks('ssl', 'symlinks');

		foreach($this->components as $component)
			$this->installComponent($component);

		if(!$this->createDynamicEnums())
			return "Failed to create plugins dynamic enumerations";

		if(!$this->createInitialContent())
			return "Failed to create initial content";

		if(!$this->createQueryCacheTriggers())
			return "Failed to create query cache triggers";

		if(!$this->createDeployKMC())
			return "Failed to deploy KMC";

		if(!$this->generateClients(true))
			return "Failed generating client libraries";

		if(!$this->changeDirsAndFilesPermissions())
			return "Failed to set files permissions";

		if(!$this->installDWH())
			return "Failed to install DWH";
			
		if(!$this->restartApache(true))
			return "Failed restarting apache http server";
	
		if(OsUtils::isLinux())
		{
			Logger::logMessage(Logger::LEVEL_USER, "Starting services");
			foreach($this->components as $component)
				$this->installComponentServices($component);
		}
				
		if(!$this->upgradeContent())
			return "Failed to upgrade content";
		
		if(!$this->createTemplateContent())
			return "Failed to create template content";
		
		if(!$this->populateSphinx())
			return "Failed to populate sphinx";

		if($packageDir)
			OsUtils::execute("cp $packageDir/version.ini " . AppConfig::get(AppConfigAttribute::APP_DIR) . '/configurations/');

		Logger::logMessage(Logger::LEVEL_USER, "Creating crontab symbolic links");
		foreach($this->components as $component)
			$this->installComponentSymlinks($component, 'crons');
				
		if($dontValidate)
		{
			Logger::logMessage(Logger::LEVEL_USER, "Skipping installation verification");
		}
		else
		{
			$this->verifyInstallation();
		}

		$this->done();

		return null;
	}

	private function createSymlink($slink)
	{
		$matches = null;
		if(preg_match_all('/@([A-Z0-9_]+)@/', $slink, $matches))
		{
			$tokens = $matches[1];
			foreach($tokens as $token)
			{
				$value = AppConfig::get($token);
				if(is_array($value))
				{
					foreach($value as $valueOption)
					{
						if(!self::createSymlink(str_replace("@$token@", $valueOption, $slink)))
							return false;
					}
					return true;
				}
			}
			$slink = AppConfig::replaceTokensInString($slink);
		}
		
		list($target, $link) = explode(SYMLINK_SEPARATOR, $slink);

		if(!file_exists($target))
		{
			Logger::logError(Logger::LEVEL_USER, "Failed to create symbolic link [$link], target [$target] does not exist.");
			return false;
		}

		if (OsUtils::symlink($target, $link))
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Created symbolic link $link -> $target");
		}
		else
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Failed to create symbolic link from $link to $target, retyring.");
			
			if(file_exists($link))
				unlink($link);
				
			OsUtils::symlink($target, $link);
		}

		clearstatcache();
		if(file_exists($link))
			chgrp($link, AppConfig::get(AppConfigAttribute::OS_KALTURA_GROUP));

		fwrite($this->uninstallConfig, "symlinks[]=$link" . PHP_EOL);
		return true;
	}

	private function createSymlinks(array $symlinks)
	{
		foreach ($symlinks as $slink)
		{
			if(!self::createSymlink($slink))
				return false;
		}

		return true;
	}

	private function startServices(array $services)
	{
		foreach ($services as $service)
		{
			if (!OsUtils::startService($service))
				return "Failed starting service [$service]";

			fwrite($this->uninstallConfig, "chkconfig[]=$service" . PHP_EOL);
		}

		return true;
	}

	private function installComponentSymlinks($component, $configName)
	{
		if(!isset($this->installConfig[$component]))
			return "Component [$component] not found";

		if(!isset($this->installConfig[$component][$configName]))
			return true;

		$componentConfig = $this->installConfig[$component];
		Logger::logMessage(Logger::LEVEL_INFO, "Installing component [$component] symbolic links");

		$createSymlinks = $this->createSymlinks($componentConfig[$configName]);
		if($createSymlinks !== true)
			return $createSymlinks;

		return true;
	}

	private function installComponent($component)
	{
		if(!isset($this->installConfig[$component]))
			return "Component [$component] not found";

		$componentConfig = $this->installConfig[$component];
		Logger::logMessage(Logger::LEVEL_USER, "Installing " . $componentConfig['title'] . " component");

		$includeFile = __DIR__ . "/components/$component.php";
		if(file_exists($includeFile))
		{
			$include = require($includeFile);
			if($include !== true)
				return $include;
		}

		return true;
	}

	private function installComponentServices($component)
	{
		if(!isset($this->installConfig[$component]))
			return "Component [$component] not found";

		if(!isset($this->installConfig[$component]['chkconfig']))
			return true;

		$componentConfig = $this->installConfig[$component];
		Logger::logMessage(Logger::LEVEL_INFO, "Installing component [$component] services");

		$startServices = $this->startServices($componentConfig['chkconfig']);
		if($startServices !== true)
			return $startServices;

		return true;
	}

	private function done()
	{
		echo PHP_EOL;
		Logger::logColorMessage(Logger::COLOR_LIGHT_GREEN, Logger::LEVEL_USER, "Installation Completed Successfully.");
		
		if(AppConfig::get(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT))
		{
			$config = AppConfig::getCurrentMachineConfig();
			if(!$config || !isset($config[AppConfigAttribute::VERIFY_INSTALLATION]) || !$config[AppConfigAttribute::VERIFY_INSTALLATION])
			{
				return true;
			}
		}
		elseif(!AppConfig::get(AppConfigAttribute::VERIFY_INSTALLATION))
			return true;

		// send settings mail if possible
		$virtualHostName = AppConfig::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME);
		$url = AppConfig::get(AppConfigAttribute::ENVIRONMENT_PROTOCOL) . '://' . AppConfig::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME);
		$versionType = AppConfig::get(AppConfigAttribute::KALTURA_VERSION_TYPE);
		$adminMail = AppConfig::get(AppConfigAttribute::ADMIN_CONSOLE_ADMIN_MAIL);
		$adminPassword = AppConfig::get(AppConfigAttribute::ADMIN_CONSOLE_PASSWORD);

		$msg = "Thank you for installing the Kaltura Video Platform\n\n";
		$msg .= "To get started, please browse to your kaltura start page at:\n";
		$msg .= "$url/start\n\n";
		$msg .= "Your $versionType administration console can be accessed at:\n";
		$msg .= "$url/admin_console\n\n";
		$msg .= "Your Admin Console credentials are:\n";
		$msg .= "System admin user: $adminMail\n";
		$msg .= "System admin password: $adminPassword\n\n";
		$msg .= "Please keep this information for future use.\n\n";
		$msg .= "Thank you for choosing Kaltura!";

		$mailer = new PHPMailer();
		$mailer->CharSet = 'utf-8';
		$mailer->IsHTML(false);
		$mailer->AddAddress($adminMail);
		$mailer->Sender = "installation_confirmation@$virtualHostName";
		$mailer->From = "installation_confirmation@$virtualHostName";
		$mailer->FromName = AppConfig::get(AppConfigAttribute::ENVIRONMENT_NAME);
		$mailer->Subject = 'Kaltura Installation Settings';
		$mailer->Body = $msg;

		if ($mailer->Send()) {
			Logger::logColorMessage(Logger::COLOR_LIGHT_GREEN, Logger::LEVEL_USER, "Sent post installation settings email to ".AppConfig::get(AppConfigAttribute::ADMIN_CONSOLE_ADMIN_MAIL));
		} else {
			Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, "Post installation email cannot be sent");
		}

		if(in_array('admin', $this->components))
		{
			Logger::logMessage(Logger::LEVEL_USER,
				"Your Kaltura Admin Console credentials:\n" .
				"System Admin user: $adminMail\n" .
				"Please keep this information for future use."
			);
			echo PHP_EOL;
		}

		if(in_array('api', $this->components))
		{
			Logger::logMessage(Logger::LEVEL_USER,
				"To start using Kaltura, please complete the following steps:\n" .
				"1. Add the following line to your /etc/hosts file:\n" .
					"\t127.0.0.1 $virtualHostName\n" .
				"2. Browse to your Kaltura start page at: $url/start\n"
			);
		}
	}

	public function verifyInstallation($force = false)
	{
		if(!$force)
		{
			if(!in_array('api', $this->components))
				return true;
	
			if(AppConfig::get(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT))
			{
				$config = AppConfig::getCurrentMachineConfig();
				if($config && isset($config[AppConfigAttribute::VERIFY_INSTALLATION]) && !$config[AppConfigAttribute::VERIFY_INSTALLATION])
					return true;
			}
			elseif(!AppConfig::get(AppConfigAttribute::VERIFY_INSTALLATION))
				return true;
		}
		
		$this->init();
		
		Logger::logMessage(Logger::LEVEL_USER, "Verifying installation");
			
		$dirName = AppConfig::get(AppConfigAttribute::APP_DIR) . '/tests/sanity';
		if(!file_exists($dirName) || !is_dir($dirName))
		{
			Logger::logError(Logger::LEVEL_USER, "Defaults sanity test files directory [$dirName] is not a valid directory");
			return false;
		}
		$dirName = realpath($dirName);

		$configPath = "$dirName/lib/config.ini";
		if(!file_exists($configPath) || !is_file($configPath) || !parse_ini_file($configPath, true))
		{
			Logger::logError(Logger::LEVEL_USER, "Sanity test configuration file [$configPath] is not a valid ini file");
			return false;
		}

		$dir = dir($dirName);
		/* @var $dir Directory */

		$fileNames = array();
		$errors = array();
		while (false !== ($fileName = $dir->read()))
		{
			if(!preg_match('/^\d+\.\w+\.php$/', $fileName))
				continue;
				
			if(!in_array('dwh', $this->components) && preg_match('/dwh/', $fileName))
			{
				Logger::logMessage(Logger::LEVEL_USER, "Data warehouse is not installed on current machine, test [$fileName] skipped");
				continue;
			}
				
			$fileNames[] = $fileName;
		}
		$dir->close();
		sort($fileNames);

		$returnValue = null;
		foreach($fileNames as $fileName)
		{
			$filePath = realpath("$dirName/$fileName");

			if (!OsUtils::execute(AppConfig::get(AppConfigAttribute::PHP_BIN) . " $filePath $configPath")) {
				Logger::logError(Logger::LEVEL_USER, "Verification failed [$filePath]");
				return false;
			}
		}

		return true;
	}

	// detects if there are databases leftovers
	// can be used both for verification and for dropping the databases
	// $should_drop - whether to drop the databases that are found or not (default - false)
	// returns null if no leftovers are found or a text containing all the leftovers found
	private function detectDatabases(array $databases, $should_drop=false)
	{
		$hosts = array(
			AppConfigAttribute::DB1_HOST => AppConfigAttribute::DB1_PORT,
			AppConfigAttribute::DB2_HOST => AppConfigAttribute::DB2_PORT,
			AppConfigAttribute::DB3_HOST => AppConfigAttribute::DB3_PORT,
			AppConfigAttribute::DWH_HOST => AppConfigAttribute::DWH_PORT,
			AppConfigAttribute::SPHINX_DB_HOST => AppConfigAttribute::SPHINX_DB_PORT,
		);

		$verify = null;
		$checkedDatabases = array();
		foreach ($hosts as $hostAttribute => $portAttribute)
		{
			$host = AppConfig::get($hostAttribute);
			$port = AppConfig::get($portAttribute);

			foreach ($databases as $db)
			{
				if(isset($checkedDatabases["$host:$port:$db"]))
					continue;

				$checkedDatabases["$host:$port:$db"] = true;

				Logger::logMessage(Logger::LEVEL_INFO, "Searching database [$db] on server [$host:$port]");
				$result = DatabaseUtils::dbExists($host, $port, $db);

				if ($result === -1)
				{
					$verify .= "   Cannot verify if '$db' database exists on host '$host' with port $port".PHP_EOL;
				}
				else if ($result === true)
				{
					if (!$should_drop)
					{
						$verify .= "   '$db' database already exists on host '$host' with port $port".PHP_EOL;
					}
					else
					{
						Logger::logMessage(Logger::LEVEL_USER, "Dropping '$db' database on host '$host' with port $port...", false);
						if(DatabaseUtils::dropDb($host, $port, $db))
							Logger::logColorMessage(Logger::COLOR_GREEN, Logger::LEVEL_USER, " - done.", true, 3);
						else
							Logger::logError(Logger::LEVEL_USER, " - failed.", 3);
					}
				}
			}
		}
		return $verify;
	}

	private function changeDirsAndFilesPermissions()
	{
		if(OsUtils::isWindows())
			return true;
			
		Logger::logMessage(Logger::LEVEL_USER, "Changing permissions of directories and files");
		$dir = __DIR__ . '/../directoryConstructor';
		return OsUtils::phing($dir, 'Update-Permissions');
	}

	private function installDB()
	{
		if(AppConfig::get(AppConfigAttribute::DB1_CREATE_NEW_DB))
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Creating databases and database users");
			$dir = __DIR__ . '/../dbSchema';
			return OsUtils::phing($dir);
		}
		elseif(AppConfig::get(AppConfigAttribute::UPGRADE_FROM_VERSION))
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Creating database users");
			$dir = __DIR__ . '/../dbSchema';
			if(!OsUtils::phing($dir, 'Set Permissions'))
				return false;
			
			Logger::logMessage(Logger::LEVEL_USER, "Upgrading existing database");
			$cmd = sprintf('%s %s/deployment/updates/update.php -u "%s"', AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR), AppConfig::get(AppConfigAttribute::DB_ROOT_USER));
			if(AppConfig::get(AppConfigAttribute::DB_ROOT_PASS))
				$cmd .= sprintf(' -p "%s"', AppConfig::get(AppConfigAttribute::DB_ROOT_PASS));
			$cmd .= ' -d';
				
			if (OsUtils::execute($cmd))
			{
				Logger::logMessage(Logger::LEVEL_INFO, "Existing database upgraded");
			} 
			else 
			{
				Logger::logMessage(Logger::LEVEL_ERROR, "Failed to upgrade existing database");
				return false;
			}
		}
		
		return true;
	}

	private function createDynamicEnums()
	{
		Logger::logMessage(Logger::LEVEL_USER, "Creating plugins dynamic enumerations");
		return OsUtils::execute(sprintf("%s %s/deployment/base/scripts/installPlugins.php", AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR)));
	}

	private function createQueryCacheTriggers()
	{
		Logger::logMessage(Logger::LEVEL_USER, "Creating query cache triggers");
		return OsUtils::execute(sprintf("%s %s/deployment/base/scripts/createQueryCacheTriggers.php", AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR)));
	}

	private function createDeployKMC()
	{
		if(!in_array('api', $this->components))
			return true;

		if(AppConfig::get(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT))
		{
			$config = AppConfig::getCurrentMachineConfig();
			if($config && isset($config[AppConfigAttribute::DEPLOY_KMC]) && !$config[AppConfigAttribute::DEPLOY_KMC])
				return true;
		}
		elseif(!AppConfig::get(AppConfigAttribute::DEPLOY_KMC))
			return true;
			
		Logger::logMessage(Logger::LEVEL_USER, "Deploying user interface configuration files (KMC ui-confs)");
		if(isset($this->installConfig[Installer::BASE_COMPONENT]['uiconfs_2']) && is_array($this->installConfig[Installer::BASE_COMPONENT]['uiconfs_2']))
		{
			foreach($this->installConfig[Installer::BASE_COMPONENT]['uiconfs_2'] as $uiconfapp)
			{
				$to_deploy = AppConfig::replaceTokensInString($uiconfapp);
				if(OsUtils::execute(sprintf("%s %s/deployment/uiconf/deploy_v2.php --ini=%s", AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR), $to_deploy)))
				{
					Logger::logMessage(Logger::LEVEL_INFO, "Deployed user interface configuration files $to_deploy");
				}
				else
				{
					return false;
				}
			}
		}
		return true;
	}

	private function createInitialContent ()
	{
		if(!in_array('api', $this->components))
			return true;

		if(AppConfig::get(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT))
		{
			$config = AppConfig::getCurrentMachineConfig();
			if($config && isset($config[AppConfigAttribute::VERIFY_INSTALLATION]) && !$config[AppConfigAttribute::VERIFY_INSTALLATION])
				return true;
		}
		elseif(!AppConfig::get(AppConfigAttribute::VERIFY_INSTALLATION))
			return true;
			
		Logger::logMessage(Logger::LEVEL_USER, "Creating databases initial content");
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/insertDefaults.php %s/deployment/base/scripts/init_data", AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR), AppConfig::get(AppConfigAttribute::APP_DIR)))) {
			Logger::logMessage(Logger::LEVEL_INFO, "Default content inserted");
		} else {
			Logger::logMessage(Logger::LEVEL_ERROR, "Failed to insert default content");
			return false;
		}

		Logger::logMessage(Logger::LEVEL_USER, "Creating databases initial permissions");
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/insertPermissions.php", AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR)))) {
			Logger::logMessage(Logger::LEVEL_INFO, "Default permissions inserted");
		} else {
			Logger::logMessage(Logger::LEVEL_ERROR, "Failed to insert permissions");
			return false;
		}

		$permissionsDir = AppConfig::get(AppConfigAttribute::APP_DIR) . '/deployment/permissions';
		foreach($this->components as $component)
		{
			$componentPermissionsDir = "$permissionsDir/$component";
			if(!file_exists($componentPermissionsDir) || !is_dir($componentPermissionsDir))
				continue;
				
			Logger::logMessage(Logger::LEVEL_USER, "Creating databases initial $component permissions");
			if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/insertPermissions.php -d $componentPermissionsDir", AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR)))) {
				Logger::logMessage(Logger::LEVEL_INFO, "Default $component permissions inserted");
			} else {
				Logger::logMessage(Logger::LEVEL_ERROR, "Failed to insert $component permissions");
				return false;
			}
		}
		
		return true;
	}

	private function installDWH ()
	{
		if(!in_array('dwh', $this->components))
			return true;
			
		$arguments = sprintf('-h %s -P %s -u %s -d %s', AppConfig::get(AppConfigAttribute::DB1_HOST), AppConfig::get(AppConfigAttribute::DB1_PORT), AppConfig::get(AppConfigAttribute::DB_ROOT_USER), AppConfig::get(AppConfigAttribute::DWH_DIR));
		if (AppConfig::get(AppConfigAttribute::DB_ROOT_PASS))
			$arguments .= ' -p ' . AppConfig::get(AppConfigAttribute::DB_ROOT_PASS);
			
		$config = AppConfig::getCurrentMachineConfig();
		if(AppConfig::get(AppConfigAttribute::DB1_CREATE_NEW_DWH) ||
		($config && isset ($config[AppConfigAttribute::DB1_CREATE_NEW_DWH]) && $config[AppConfigAttribute::DB1_CREATE_NEW_DWH]))
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Creating data warehouse");
			$cmd = sprintf("%s/setup/dwh_setup.sh $arguments", AppConfig::get(AppConfigAttribute::DWH_DIR));	
			if (!OsUtils::execute($cmd)){
				return "Failed running data warehouse initialization script";
			}
		}
		elseif(AppConfig::get(AppConfigAttribute::UPGRADE_FROM_VERSION))
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Upgrading data warehouse");
			$cmd = sprintf("%s/ddl/migrations/20130922_gemini_to_IX/gemini2IX.sh $arguments", AppConfig::get(AppConfigAttribute::DWH_DIR));
			if (!OsUtils::execute($cmd)){
				return "Failed running data warehouse upgrade script";
			}
		}
		
		return true;
	}

	private function upgradeContent ()
	{
		if(!in_array('db', $this->components))
			return true;
			
		if(AppConfig::get(AppConfigAttribute::UPGRADE_FROM_VERSION))
		{
			Logger::logMessage(Logger::LEVEL_USER, "Upgrading existing content");
			$cmd = sprintf('%s %s/deployment/updates/update.php -u "%s"', AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR), AppConfig::get(AppConfigAttribute::DB_ROOT_USER));
			if(AppConfig::get(AppConfigAttribute::DB_ROOT_PASS))
				$cmd .= sprintf(' -p "%s"', AppConfig::get(AppConfigAttribute::DB_ROOT_PASS));
			$cmd .= ' -s';
				
			if (OsUtils::execute($cmd))
			{
				Logger::logMessage(Logger::LEVEL_INFO, "Existing content upgraded");
			} 
			else 
			{
				Logger::logMessage(Logger::LEVEL_ERROR, "Failed to upgrade existing content");
				return false;
			}
		}
		
		return true;
	}

	private function populateSphinx ()
	{
		if(!in_array('sphinx', $this->components))
			return true;
			
		OsUtils::execute("/etc/init.d/memcached restart");
		
		if(AppConfig::get(AppConfigAttribute::UPGRADE_FROM_VERSION))
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Populating old content to the sphinx");
			$populateScripts = array(
				"populateSphinxCaptionAssetItem.php",
				"populateSphinxCategories.php",
				"populateSphinxEntries.php",
				"populateSphinxKusers.php",
				"populateSphinxCaptionAssetItem.php",
				"populateSphinxCategoryKusers.php",
				"populateSphinxCuePoints.php",
				"populateSphinxEntryDistributions.php",
				"populateSphinxTags.php",
			);
			
			$appDir = AppConfig::get(AppConfigAttribute::APP_DIR);
			foreach($populateScripts as $populateScript)
			{
				if (!OsUtils::execute("php $appDir/deployment/base/scripts/$populateScript")){
					Logger::logError(Logger::LEVEL_ERROR, "Failed running sphinx populate script [$populateScript]");
					return false;
				}
			}
		}
		
		return true;
	}

	private function createTemplateContent ()
	{
		if(!in_array('api', $this->components))
			return true;

		if(AppConfig::get(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT))
		{
			$config = AppConfig::getCurrentMachineConfig();
			if($config && isset($config[AppConfigAttribute::VERIFY_INSTALLATION]) && !$config[AppConfigAttribute::VERIFY_INSTALLATION])
				return true;
		}
		elseif(!AppConfig::get(AppConfigAttribute::VERIFY_INSTALLATION))
			return true;
			
		$this->determineInsertedContent ();
		Logger::logMessage(Logger::LEVEL_USER, "Creating partner template content");
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/insertContent.php", AppConfig::get(AppConfigAttribute::PHP_BIN), AppConfig::get(AppConfigAttribute::APP_DIR)))) {
			Logger::logMessage(Logger::LEVEL_INFO, "Default content inserted");
		} else {
			Logger::logMessage(Logger::LEVEL_ERROR, "Failed to insert content");
			return false;
		}

		return true;
	}
	
	/**
	 * This function will become deprecated as of version 8. It forcefully adds the entries for the previously created content into the version_management table of the Kaltura DB if it is empty and the installation 
	 * is running upgrade mode.
	 */
	private function determineInsertedContent ()
	{
		if (AppConfig::get(AppConfigAttribute::UPGRADE_FROM_VERSION) && AppConfig::get(AppConfigAttribute::UPGRADE_FROM_VERSION) >= 6)
		{
			Logger::logMessage (Logger::LEVEL_INFO,"Inserting default content into DB as created");
			$link = mysql_connect(AppConfig::get(AppConfigAttribute::DB1_HOST) . ':' . AppConfig::get(AppConfigAttribute::DB1_PORT), AppConfig::get(AppConfigAttribute::DB1_USER), AppConfig::get(AppConfigAttribute::DB1_PASS), null);
			$db_selected = mysql_select_db(AppConfig::get(AppConfigAttribute::DB1_NAME), $link);
			try
			{
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '01.accessControl.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '01.conversionProfile.99.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '01.conversionProfile.-4.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '01.uiConf.99.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '01.uiConf.-4.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '01.UserRole.-2.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '01.UserRole.99.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '02.entry.99.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '02.playlist.99.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '03.EventNotificationTemplate.0.xml', '". time() ."')");
				$result =   mysql_query("insert into version_management (version, filename, created_at) values ('7000', '04.dropFolder.-4.xml', '". time() ."')");
			}
			catch (Exception $e)
			{
				Logger::logMessage(Logger::LEVEL_ERROR, $e->getMessage());
			}	
			
			mysql_close($link);
		}
	}
}

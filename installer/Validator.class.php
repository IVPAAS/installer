<?php

include_once (__DIR__ . '/OsUtils.class.php');
include_once (__DIR__ . '/DatabaseUtils.class.php');

class Validator
{
	/**
	 * Configuration
	 * @var array
	 */
	private $installConfig;

	/**
	 * Array of the components that should be installed
	 * @var array
	 */
	private $components = array(Installer::BASE_COMPONENT);

	/**
	 * Enter description here ...
	 * @var array
	 */
	private $prerequisites = array();

	public function __construct()
	{
		$this->installConfig = AppConfig::getInstallationConfiguration();

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
	}

	private function validateUser($user, $ids, $required = true)
	{
		$systemIdsOutput = OsUtils::executeWithOutput("id $user");
		if(!$systemIdsOutput)
		{
			if($required)
				$this->prerequisites[] = "Mandatory user $user is not defined.";
				
			return;
		}
		
		$systemIds = explode(' ', reset($systemIdsOutput));
		foreach($systemIds as $systemId)
		{
			list($idName, $id) = explode('=', $systemId, 2);
			if(!isset($ids[$idName]))
				continue;
				
			if(intval($ids[$idName]) != intval($id))
				$this->prerequisites[] = "User $user is defined with wrong $idName [$id], expected " . $ids[$idName] . ".";
		}
	}

	private function validateUsers()
	{
		if(OsUtils::isWindows())
			return;
			
		$this->validateUser(AppConfig::get(AppConfigAttribute::OS_ROOT_USER), array(
			'uid' => AppConfig::get(AppConfigAttribute::OS_ROOT_UID),
			'gid' => AppConfig::get(AppConfigAttribute::OS_ROOT_GID),
		));
		$this->validateUser(AppConfig::get(AppConfigAttribute::OS_APACHE_USER), array(
			'uid' => AppConfig::get(AppConfigAttribute::OS_APACHE_UID),
			'gid' => AppConfig::get(AppConfigAttribute::OS_APACHE_GID),
		));
		$this->validateUser(AppConfig::get(AppConfigAttribute::OS_KALTURA_USER), array(
			'uid' => AppConfig::get(AppConfigAttribute::OS_KALTURA_UID),
			'gid' => AppConfig::get(AppConfigAttribute::OS_KALTURA_GID),
		), false);
	}
	
	private function validatePear()
	{
		if(!isset($this->installConfig[Installer::BASE_COMPONENT]["pears"]))
			return;

		$matches = null;
		foreach($this->installConfig[Installer::BASE_COMPONENT]["pears"] as $pear => $version)
		{
			$pearInfo = OsUtils::executeWithOutput(AppConfig::get(AppConfigAttribute::PEAR_BIN) . " info $pear");
			$pearVersion = null;
			if($pearInfo)
			{
				foreach($pearInfo as $pearInfoLine)
				{
					if(preg_match('/^No information found/', $pearInfoLine))
						break;
						
					if(preg_match('/^Release Version[\s]+([\d.]+)/', $pearInfoLine, $matches))
					{
						$pearVersion = $matches[1];
						break;
					}
				}
			}
			
			if($pearVersion)
			{
				if(! $this->checkVersion($pearVersion, $version))
					$this->prerequisites[] = "Pear $pear package version should be >= $version (current version is $pearVersion)";
			}
			else
			{
				$this->prerequisites[] = "Missing pear $pear package, please install $pear-$version";
			}
		}
	}
	
	private function validatePHP()
	{
		// check php version
		$phpversion = phpversion();
		if(! $this->checkVersion($phpversion, $this->installConfig[Installer::BASE_COMPONENT]["php_min_version"]))
			$this->prerequisites[] = "PHP version should be >= " . $this->installConfig[Installer::BASE_COMPONENT]["php_min_version"] . " (current version is $phpversion)";
		elseif(! $this->checkVersion($this->installConfig[Installer::BASE_COMPONENT]["php_min_unsupported_version"], $phpversion))
			$this->prerequisites[] = "PHP version should be < " . $this->installConfig[Installer::BASE_COMPONENT]["php_min_unsupported_version"] . " (current version is $phpversion)";
			
		// check php extensions
		foreach($this->components as $component)
		{
			if(! isset($this->installConfig[$component]["php_extensions"]) || ! is_array($this->installConfig[$component]["php_extensions"]))
				continue;

			foreach($this->installConfig[$component]["php_extensions"] as $ext)
			{
				if(! extension_loaded($ext))
					$this->prerequisites[] = "Missing $ext PHP extension";
			}
		}
	}

	// checks if the mysql settings $key is as $expected using the db $link
	// if $allow_greater it also checks if the value is greater the the $expected (not only equal)
	private function getMysqlSetting(&$link, $key)
	{
		$result = mysqli_query($link, "SELECT @@$key;");
		if($result === false)
			return null;

		/* @var $result mysqli_result */
		$tmp = '@@' . $key;
		$current = $result->fetch_object()->$tmp;
		return $current;
	}

	private function validateMysql()
	{
		if(! in_array('db', $this->components))
		{
			$dbRequired = false;
			foreach($this->components as $component)
			{
				if(isset($this->installConfig[$component]['depends_on']) && in_array('db', $this->installConfig[$component]['depends_on']))
				{
					$dbRequired = true;
					break;
				}
			}
			return;
		}

		// check mysql
		$link = null;
		if(! extension_loaded('mysqli'))
		{
			$this->prerequisites[] = "Cannot check MySQL connection, version and settings because PHP mysqli extension is not loaded";
			return;
		}

		$hosts = array(
			AppConfigAttribute::DB1_HOST => AppConfigAttribute::DB1_PORT,
			AppConfigAttribute::DB2_HOST => AppConfigAttribute::DB2_PORT,
			AppConfigAttribute::DB3_HOST => AppConfigAttribute::DB3_PORT,
			AppConfigAttribute::DWH_HOST => AppConfigAttribute::DWH_PORT,
			AppConfigAttribute::SPHINX_DB_HOST => AppConfigAttribute::SPHINX_DB_PORT,
		);

		$checkedConnections = array();
		foreach ($hosts as $hostAttribute => $portAttribute)
		{
			$host = AppConfig::get($hostAttribute);
			$port = AppConfig::get($portAttribute);

			if(isset($checkedConnections["$host:$port"]))
				continue;

			$checkedConnections["$host:$port"] = true;

			$link = DatabaseUtils::connect($host, $port);
			if(!$link)
			{
				$this->prerequisites[] = "Failed to connect to database host $host:$port user:" . AppConfig::get(AppConfigAttribute::DB_ROOT_USER) . ". Please check the database settings you provided and verify that MySQL is up and running.";
				continue;
			}

			// check mysql version and settings
			$mysql_version = $this->getMysqlSetting($link, 'version'); // will always return the value
			if(! $this->checkVersion($mysql_version, $this->installConfig['db']["mysql_min_version"]))
			{
				$this->prerequisites[] = "MySQL host $host:$port version should be >= " . $this->installConfig['db']["mysql_min_version"] . " (current version is $mysql_version)";
			}

			$mysqlPrerequisites = array();
			
			$mysql_timezone = $this->getMysqlSetting($link, 'time_zone'); // will always return the value
			if(!preg_match('/^[-+]?[0-9]{2}:[0-9]{2}$/', $mysql_timezone))
			{
				if(OsUtils::isWindows())
					$system_timezone = OsUtils::executeWithOutput('systeminfo | findstr  /L Zone');
				else
					$system_timezone = OsUtils::executeWithOutput('date +%:z');
					
				if($system_timezone)
					$mysql_timezone = reset($system_timezone);
			}
				
			if(is_null($mysql_timezone))
			{
				$mysqlPrerequisites[] = "Please set MySQL host $host:$port default_time_zone directive in my.cnf and restart MySQL.";
			}
			else
			{
				$mysql_timezone = $this->intTimezoneSeconds($mysql_timezone);
				
				$dateTimeZone = new DateTimeZone(AppConfig::get(AppConfigAttribute::TIME_ZONE));
				$dateTime = new DateTime('now', $dateTimeZone);
				$php_timezone = $dateTime->getOffset();
				if($mysql_timezone != $php_timezone)
				{
					$php_timezone /= 60;
					$php_timezone_str = ($php_timezone >= 0 ? '+' : '-') . str_pad(intval(abs($php_timezone) / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad($php_timezone % 60, 2, '0', STR_PAD_LEFT);
					$mysqlPrerequisites[] = "Please set MySQL host $host:$port default_time_zone directive in my.cnf to match the php timezone ($php_timezone_str) and restart MySQL.";
				}
			}
			
			foreach($this->installConfig['db']['mysql_settings'] as $field => $value)
			{
				$actualValue = $this->getMysqlSetting($link, $field);
				if(is_null($actualValue) || $actualValue != $this->intConfigValue($value))
					$mysqlPrerequisites[] = "$field = $value" . ($actualValue ? " (current value is $actualValue)" : '');
					
			}
			
			if(count($mysqlPrerequisites))
			{
				$dataDir = $this->getMysqlSetting($link, 'datadir');
				$logFiles = glob("$dataDir/ib_logfile*");
				
				$prerequisite = "Please set MySQL host $host:$port in my.cnf:\n - " . implode("\n - ", $mysqlPrerequisites);
				
				if($logFiles)
					$prerequisite .= "\nPlease delete the following log files:\n - " . implode("\n - ", $logFiles);
				
				$prerequisite .= "\nPlease restart MySQL in order to apply the changed configuration.";
				$this->prerequisites[] = $prerequisite;
			}
		}
	}

	private function intTimezoneSeconds($value)
	{
		if(!preg_match('/[-+]?[0-9]{2}:[0-9]{2}/', $value))
			return null;
			
		$parts = explode(':', $value, 3);
		$ret = (intval($parts[0]) * 60 * 60) + (intval($parts[1]) * 60);
		if(isset($parts[2]))
			$ret += intval($parts[2]);
			
		return $ret;
	}

	private function intConfigValue($value)
	{
		$matches = null;
		if(preg_match('/^(\d+)([KMG])B?$/i', trim($value), $matches))
		{
			switch (strtoupper($matches[2]))
			{
				case 'K':
					return intval($matches[1]) * 1024;
					
				case 'M':
					return intval($matches[1]) * 1024 * 1024;
					
				case 'G':
					return intval($matches[1]) * 1024 * 1024 * 1024;
			}
		}
		
		return intval($value);
	}

	private function validateApache()
	{
		$requiredModules = array();
		foreach($this->components as $component)
		{
			if(! isset($this->installConfig[$component]["apache_modules"]) || ! is_array($this->installConfig[$component]["apache_modules"]))
				continue;

			foreach($this->installConfig[$component]["apache_modules"] as $module)
				$requiredModules[$module] = true;
		}

		if(!count($requiredModules))
			return;

		$httpdBin = AppConfig::get(AppConfigAttribute::HTTPD_BIN);
		$modules1 = OsUtils::executeWithOutput("\"$httpdBin\" -M", true);
		$modules2 = OsUtils::executeWithOutput("\"$httpdBin\" -M", false);
		
		if(is_array($modules1) && is_array($modules2))
			$currentModules = array_merge($modules1, $modules2);
		elseif(is_array($modules1))
			$currentModules = $modules1;
		elseif(is_array($modules2))
			$currentModules = $modules2;
		else
		{
			$this->prerequisites[] = "Cannot check apache modules, please make sure that '$httpdBin -t' command runs properly";
			return;
		}
		$currentModules = array_map('trim', $currentModules);

		foreach($requiredModules as $module => $true)
		{
			$found = false;
			foreach($currentModules as $currentModule)
			{
				if(strpos($currentModule, $module) === 0)
				{
					$found = true;
					break;
				}
			}

			if(! $found)
				$this->prerequisites[] = "Missing $module Apache module";
		}
	}

	private function validateBinaries()
	{
		if(OsUtils::isWindows())
			return;
			
		$validated = array();
		
		foreach($this->components as $component)
		{
			if(! isset($this->installConfig[$component]["binaries"]) || ! is_array($this->installConfig[$component]["binaries"]))
				continue;

			foreach($this->installConfig[$component]["binaries"] as $bin)
			{
				if(isset($validated[$bin]))
					continue;
				$validated[$bin] = true;
				
				$bins = explode('|', $bin);
				$found = false;
				foreach($bins as $optionalBin)
				{
					system("which $optionalBin 1>/dev/null 2>&1", $exitCode);
					if($exitCode === 0)
					{
						$found = true;
						break;
					}
				}

				if(!$found)
					$this->prerequisites[] = "Missing $bin binary file";
			}
		}
	}

	// check if the given $version is equal or bigger than the $expected
	// both $version and $expected are version strings which means that they are numbers separated by dots ('.')
	// if $version has less parts, the missing parts are treated as zeros
	private function checkVersion($version, $expected)
	{
		$version_parts = explode('.', $version);
		$expected_parts = explode('.', $expected);

		for($i = 0; $i < count($expected_parts); $i ++)
		{
			// allow the version to have less parts than the expected, fill the missing with zeros
			$comparison = 0;
			if($i < count($version_parts))
			{
				$comparison = intval($version_parts[$i]);
			}

			// if the part is smaller the version is not ok
			if($comparison < intval($expected_parts[$i]))
			{
				return false;

		// if the part is bigger the version is ok
			}
			else if($comparison > intval($expected_parts[$i]))
			{
				return true;
			}
		}

		return true;
	}

	private function validateDependency()
	{
		foreach($this->components as $component)
		{
			if($component == '*' || $component == 'base')
				continue;
				
			if(!isset($this->installConfig[$component]["depends_on"]) || ! is_array($this->installConfig[$component]["depends_on"]))
				continue;

			foreach($this->installConfig[$component]["depends_on"] as $dependency)
			{
				if(in_array($dependency, $this->components))
					continue;
			
				switch($dependency)
				{
					case 'db':
						$host = AppConfig::get(AppConfigAttribute::DB1_HOST);
						$port = AppConfig::get(AppConfigAttribute::DB1_PORT);
						$db = AppConfig::get(AppConfigAttribute::DB1_NAME);
						
						if(!DatabaseUtils::dbExists($host, $port, $db))
						{
							$this->prerequisites[] = "Database not installed: $db on host $host (port $port) user:" . AppConfig::get(AppConfigAttribute::DB_ROOT_USER) . ".";
						}
						break;
						
					case 'sphinx':
						$hosts = array(AppConfig::get(AppConfigAttribute::SPHINX_SERVER1), AppConfig::get(AppConfigAttribute::SPHINX_SERVER2));
						foreach($hosts as $host)
						{
							$connectionString = "mysql:host=$host;port=9312;";
							try
							{
								new PDO($connectionString);
							}
							catch(PDOException $e)
							{
								$this->prerequisites[] = "Sphinx not installed on host $host.";
							}
						}
						break;
						
					case 'dwh':
						$host = AppConfig::get(AppConfigAttribute::DWH_HOST);
						$port = AppConfig::get(AppConfigAttribute::DWH_PORT);
						$db = AppConfig::get(AppConfigAttribute::DWH_DATABASE_NAME);
						
						if(!DatabaseUtils::dbExists($host, $port, $db))
						{
							$this->prerequisites[] = "Data warehouse database not installed: $db on host $host (port $port) user:" . AppConfig::get(AppConfigAttribute::DB_ROOT_USER) . ".";
						}
						break;
						
					default:
						$this->prerequisites[] = "Missing $dependency component, it's required for $component component installation.";
				}
			}
		}
	}

	public function validate()
	{
		$this->validateUsers();
		$this->validatePHP();
		$this->validatePear();
		$this->validateMysql();
		$this->validateApache();
		$this->validateBinaries();
		$this->validateDependency();

		// Check that SELinux is not enabled (enforcing)
		exec("which getenforce 2>/dev/null", $out, $rc);
		if($rc === 0)
		{
			exec("getenforce", $out, $rc);
			if($out[1] === 'Enforcing')
				$this->prerequisites[] = "SELinux is Enabled, please edit file '/etc/sysconfig/selinux' and set SELINUX to permissive, to apply the change in current session execute 'setenforce permissive'.";
		}

		return $this->prerequisites;
	}
}


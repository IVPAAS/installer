<?php
require_once __DIR__ . '/Log.php';
require_once __DIR__ . '/progress/ProgressBarProcess.php';

/*
* This is a static OS utilities class
*/
class OsUtils {
	const WINDOWS_OS = 'windows';
	const LINUX_OS   = 'linux';

	private static $log = null;

	public static function setLogPath($path)
	{
		self::$log = $path;
	}

	public static function getLogPath()
	{
		return self::$log;
	}

	// returns true if the OS is linux, false otherwise
	public static function verifyOS() {
		Logger::logMessage(Logger::LEVEL_INFO, "OS: ".OsUtils::getOsName());
		return self::isLinux();
	}

	// returns true if the OS is linux, false otherwise
	public static function isLinux() {
		return (OsUtils::getOsName() === OsUtils::LINUX_OS);
	}

	// returns true if the OS is windows, false otherwise
	public static function isWindows() {
		return (OsUtils::getOsName() === OsUtils::WINDOWS_OS);
	}

	public static function windowsPath($path)
	{
		if(!$path)
			return null;
			
		$path = str_replace('/', '\\', $path);
		if($path[0] == '\\')
			$path = realpath('/') . ltrim($path, '\\');
			
		return $path;
	}

	// returns the computer hostname if found, 'unknown' if not found
	public static function getComputerName() {
		if(isset($_ENV['COMPUTERNAME'])) {
			Logger::logMessage(Logger::LEVEL_INFO, "Host name: ".$_ENV['COMPUTERNAME']);
	    	return $_ENV['COMPUTERNAME'];
		} else if (isset($_ENV['HOSTNAME'])) {
			Logger::logMessage(Logger::LEVEL_INFO, "Host name: ".$_ENV['HOSTNAME']);
			return $_ENV['HOSTNAME'];
		} else if (function_exists('gethostname')) {
			Logger::logMessage(Logger::LEVEL_INFO, "Host name: ".gethostname());
			return gethostname();
		} else {
			Logger::logMessage(Logger::LEVEL_WARNING, "Host name unkown");
			return 'unknown';
		}
	}

	public static function clearScreen()
	{
		if(OsUtils::isWindows())
		{
			echo str_repeat("\n", 1000);
		}
		else
		{
			system('clear');
		}
	}

	// returns the OS name or empty string if not recognized
	public static function getOsName() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return self::WINDOWS_OS;
		} else if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') {
			return self::LINUX_OS;
		} else {
			Logger::logMessage(Logger::LEVEL_WARNING, "OS not recognized: ".PHP_OS);
			return "";
		}
	}

	// returns the linux distribution
	public static function getOsLsb() {
		if(!self::isLinux())
			return null;
			
		$dist = OsUtils::executeWithOutput("lsb_release -d");
		if($dist)
		{
			$dist = implode('\n', $dist);
		}
		else
		{
			$dist = PHP_OS;
		}
		Logger::logMessage(Logger::LEVEL_INFO, "Distribution: ".$dist);
		return $dist;
	}

	// returns '32bit'/'64bit' according to current system architecture - if not found, default is 32bit
	public static function getSystemArchitecture() {
		$arch = php_uname('m');
		Logger::logMessage(Logger::LEVEL_INFO, "OS architecture: ".$arch);
		if ($arch && (stristr($arch, 'x86_64') || stristr($arch, 'amd64'))) {
			return '64bit';
		} else {
			// stristr($arch, 'i386') || stristr($arch, 'i486') || stristr($arch, 'i586') || stristr($arch, 'i686') ||
			// return 32bit as default when not recognized
			return '32bit';
		}
	}

	// Write $config to ini $filename key = value
	public static function writeConfigToFile($config, $filename)
	{
		Logger::logMessage(Logger::LEVEL_INFO, "Writing config to file $filename");
		$data = '';
		$sections = array();
		foreach ($config as $key => $value)
		{
			if(is_array($value))
			{
				$sections[$key] = $value;
			}
			else
			{
				if(preg_match('/[=&;!@#$%^*()-+{}:?]/', $value))
					$value = '"' . trim($value, '"') . '"';

				$data .= "$key=$value" . PHP_EOL;
			}
		}

		foreach ($sections as $section => $sectionsConfig)
		{
			$data .= PHP_EOL;
			$data .= "[$section]" . PHP_EOL;

			foreach ($sectionsConfig as $key => $value)
			{
				if(preg_match('/[=&;]/', $value))
					$value = '"' . trim($value, '"') . '"';

				$data .= "$key=$value" . PHP_EOL;
			}
		}
		return file_put_contents($filename, $data);
	}

	/**
	 * Build phing command line
	 * @param string $target
	 * @param array $attributes
	 * @return string
	 */
	public static function getPhingCommand($target = '', array $attributes = array())
	{
		$propertyFile = AppConfig::getFilePath();
		$options = array();
		
		if(AppConfig::get(AppConfigAttribute::VERBOSE))
			$options[] = '-verbose';
			
		foreach($attributes as $attribute => $value)
		{
			if(OsUtils::isWindows())
				$value = "\"$value\"";
			else
				$value = "'$value'";

			$options[] = "-D{$attribute}={$value}";
		}
		$options = implode(' ', $options);
		
		if($target)
			$target = '"' . $target . '"';
			
		return "phing -propertyfile $propertyFile $options $target";
	}

	/**
	 * Executes the phing and returns true/false according to the execution return value
	 * @param string $dir
	 * @param string $target
	 * @param array $attributes
	 * @return boolean
	 */
	public static function phing($dir, $target = '', array $attributes = array())
	{
		$command = self::getPhingCommand($target, $attributes);
		if(self::$log)
			$command .= " >> " . self::$log . " 2>&1";

		Logger::logMessage(Logger::LEVEL_INFO, "Executing $command");
		$returnedValue = null;
		$originalDir = getcwd();
		chdir($dir);
		passthru($command, $returnedValue);
		chdir($originalDir);

		if($returnedValue != 0)
			return false;

		return true;
	}

	/**
	 * Executes commands in different process and reports their progress
	 * @param array $processes array of ProgressProcess objects
	 * @return boolean
	 */
	public static function runProgressBar(array $processes)
	{
		foreach($processes as $process)
		{
			/* @var $process ProgressProcess */
			$process->exec();
		}

		return ProgressBarProcess::listen($processes);
	}

	public static function startService($service, $alwaysStartAutomtically = true)
	{
		if(OsUtils::isWindows())
		{
			OsUtils::execute(AppConfig::get(AppConfigAttribute::PHP_BIN) . " $service install");
			return OsUtils::execute(AppConfig::get(AppConfigAttribute::PHP_BIN) . " $service stop");
		}
			
		if($alwaysStartAutomtically)
			OsUtils::execute("chkconfig $service on");

		return OsUtils::execute("/etc/init.d/$service restart");
	}

	public static function stopService($service, $neverStartAutomtically = true)
	{
		if(OsUtils::isWindows())
		{
			if(!file_exists($service))
				return true;
								
			$ret = OsUtils::executeInBackground(AppConfig::get(AppConfigAttribute::PHP_BIN) . " $service stop");
			
			if($neverStartAutomtically)
				OsUtils::executeInBackground(AppConfig::get(AppConfigAttribute::PHP_BIN) . " $service uninstall");
				
			return $ret;
		}
			
		if(!file_exists("/etc/init.d/$service"))
			return true;
			
		if($neverStartAutomtically)
			OsUtils::executeInBackground("chkconfig $service off");

		return OsUtils::executeInBackground("/etc/init.d/$service stop");
	}

	// executes the shell $commands and returns true/false according to the execution return value
	public static function execute($cmd) {
		if(self::$log)
			$cmd .= ' >> ' . self::$log .' 2>&1';

		Logger::logMessage(Logger::LEVEL_INFO, "Executing  [$cmd]");
		if(self::$log)
			exec($cmd, $output, $return_var);
		else
			passthru($cmd, $return_var);

		if ($return_var === 0)
			return true;

		Logger::logError(Logger::LEVEL_ERROR, "Executing command failed: $cmd");

		if(self::$log)
		{
			Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_ERROR, "Output from command is: ");
			Logger::logError(Logger::LEVEL_ERROR, "\t" . implode("\n\t", $output));
			Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_ERROR, "End of Output");
		}

		return false;
	}

	public static function executeWithOutput($cmd, $getStandardError = false) {

		$stdErrPath = null;
		if($getStandardError)
		{
			$stdErrPath = tempnam(sys_get_temp_dir(), 'stdErr');
			$cmd .= " 2> $stdErrPath";
		}
		else
		{
			$cmd .= ' 2>&1';
		}

		Logger::logMessage(Logger::LEVEL_INFO, "Executing  [$cmd]");
		exec($cmd, $output, $return_var);

		if($getStandardError)
			$output = file($stdErrPath);

		if ($return_var === 0)
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Output from command is: ");
			Logger::logMessage(Logger::LEVEL_INFO, "\t" . implode("\n\t", $output));
			Logger::logMessage(Logger::LEVEL_INFO, "End of Output");
			return $output;
		}

		Logger::logError(Logger::LEVEL_ERROR, "Executing command failed: $cmd");
		Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_ERROR, "Output from command is: ");
		Logger::logError(Logger::LEVEL_ERROR, "\t" . implode("\n\t", $output));
		Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_ERROR, "End of Output");
		return false;
	}

	public static function executeInBackground($cmd) {
		if(self::$log)
			$cmd .= ' >> ' . self::$log . ' 2>&1 &';
		Logger::logMessage(Logger::LEVEL_INFO, "Executing in background [$cmd]");
		passthru($cmd, $return_var);
	}

	/**
	 * Execute 'which' on each of the given $file_name (array or string) and returns the first one found (null if not found)
	 * @param string $file_name
	 * @return string
	 */
	public static function findBinary($file_name)
	{
		if(!OsUtils::isLinux())
			return null;

		if (!is_array($file_name))
			$file_name = array ($file_name);

		foreach ($file_name as $file)
		{
			$which_path = OsUtils::executeWithOutput("which $file");

			if (isset($which_path[0]) && (trim($which_path[0]) != '') && (substr($which_path[0],0,1) == "/"))
				return $which_path[0];
		}

		return null;
	}

	/**
	 * Execute 'service --status-all', grepping on each of the given $serviceName (array or string) and returns the first one found (null if not found)
	 * @param string $file_name
	 * @return string
	 */
	public static function findService($serviceName)
	{
		if(!OsUtils::isLinux())
			return null;

		if (!is_array($serviceName))
			$serviceName = array ($serviceName);

		foreach ($serviceName as $service)
		{
			$output = OsUtils::executeWithOutput("service --status-all 2>&1 | grep -c $service");
			$count = trim(reset($output));
			if(is_numeric($count) && intval($count) > 0)
			{
				Logger::logMessage(Logger::LEVEL_INFO, "Service $service found");
				return $service;
			}
		}

		return null;
	}

	// full copy $source to $target and return true/false according to success
	public static function fullCopy($source, $target) {
		if(self::isWindows())
		{
			$source = self::windowsPath($source);
			$target = self::windowsPath($target);
			if(is_dir($source))
				return self::execute("xcopy /Y /S /R /Q $source $target");
				
			return self::execute("copy /Y $source $target");
		}
		
		return self::execute("cp -r $source $target");
	}
	
	public static function symlink($target, $link)
	{
		Logger::logMessage(Logger::LEVEL_INFO, "Create symbolic link target [$target], link [$link]");
		if(strpos($link, '*') > 0)
		{
			list($basePath, $linkPath) = explode('*', $link, 2);
			$basePath .= '*';
			$basePaths = glob($basePath);
			if(!$basePaths)
			{
				Logger::logMessage(Logger::LEVEL_INFO, "Failed to create symbolic link from $link to $target, path [$basePath] not found.");
				return false;
			}
			
			foreach($basePaths as $basePath)
			{
				$link = "$basePath/$linkPath";
				if(!self::symlink($target, $link))
				{
					Logger::logMessage(Logger::LEVEL_INFO, "Failed to create symbolic link from $link to $target.");
					return false;
				}
			}
			return true;
		}
		
		if(!file_exists(dirname($link)))
			mkdir(dirname($link), 0755, true);

		if(file_exists($link))
			unlink($link);
	
		if(self::isWindows())
		{
			$target = self::windowsPath($target);
			$link = self::windowsPath($link);
			if(is_dir($target))
				return self::execute("mklink /D $link $target");
			else
				return self::execute("mklink $link $target");
		}
		
		return symlink($target, $link);
	}
	
	// full copy $source to $target and return true/false according to success
	public static function rsync($source, $target, $options = "") {
		if(self::isWindows())
		{
			$source = self::windowsPath($source);
			$target = self::windowsPath($target);
			return self::fullCopy($source, $target);
		}
			
		return self::execute("rsync -rl $options $source $target");
	}

	// recursive delete the $path and return true/false according to success
	public static function recursiveDelete($path, $exclude = null)
	{
		if(! $exclude)
		{
			if(self::isWindows())
			{
				$path = self::windowsPath($path);
				if(is_dir($path))
					return self::execute("rd /S /Q $path");
					
				return self::execute("del /S /Q $path");
			}
				
			return self::execute("rm -rf $path");
		}

		if(is_array($exclude))
		{
			$excludes = $exclude;
		}
		else
		{
			if($exclude[0] != '/')
				$exclude = realpath("$path/$exclude");

			$excludes = array();
			while(realpath($path) != realpath($exclude))
			{
				$excludes[] = $exclude;
				$exclude = dirname($exclude);
			}
		}

		$dir = dir($path);
		while(false !== ($subPath = $dir->read()))
		{
			if($subPath[0] == '.')
				continue;

			$subPath = realpath("$path/$subPath");
			$currentDir = array_search($subPath, $excludes);
			if($currentDir === false)
			{
				self::recursiveDelete($subPath);
				continue;
			}

			unset($excludes[$currentDir]);
			if(count($excludes))
				self::recursiveDelete($subPath, $excludes);
		}
		$dir->close();

		return true;
	}

	/**
	 * Function receives an .ini file path and an array of values and writes the array into the file.
	 * @param string $file
	 * @param array $valuesArray
	 */
	public static function writeToIniFile ($file, $valuesArray)
	{
		$res = array();
		foreach($valuesArray as $key => $val)
	    {
	        if(is_array($val))
	        {
	            $res[] = "[$key]";
	            foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
	        }
	        else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
	    }
		file_put_contents($file, implode("\r\n", $res));
	}
}
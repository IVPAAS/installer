<?php

require_once(__DIR__ . '/installer/DatabaseUtils.class.php');
require_once(__DIR__ . '/installer/OsUtils.class.php');
require_once(__DIR__ . '/installer/InstallReport.class.php');
require_once(__DIR__ . '/installer/AppConfig.class.php');
require_once(__DIR__ . '/installer/Installer.class.php');
require_once(__DIR__ . '/installer/Validator.class.php');
require_once(__DIR__ . '/installer/phpmailer/class.phpmailer.php');

$options = getopt('hscvakt:');
if($argc < 2 || isset($options['h']))
{
	echo 'Usage is php ' . __FILE__ . ' [arguments] <outputdir>'.PHP_EOL;
	echo "<outputdir> = directory in which to create the package".PHP_EOL;
	if(OsUtils::getOsName() == OsUtils::LINUX_OS)
		echo "(if it does not start with a '/' the running directory will be used as base directory)".PHP_EOL;
	elseif(OsUtils::getOsName() == OsUtils::WINDOWS_OS)
		echo "(if it does not start with driver letter (e.g. C:\\) the running directory will be used as base directory)".PHP_EOL;

	echo " -h - Show this help." . PHP_EOL;
	echo " -s - Silent mode, no questions will be asked." . PHP_EOL;
	echo " -v - Verbose output." . PHP_EOL;
	echo " -c - Run configurator." . PHP_EOL;
	echo " -k - Keep temporary directory." . PHP_EOL;
	
	// don't tell anyone it's possibler
	// echo "-a - Auto-generate activation key." . PHP_EOL;
	
	echo PHP_EOL;
	echo "Examples:" . PHP_EOL;
	echo 'php ' . __FILE__ . ' -s /root/kaltura/packages' . PHP_EOL;
	echo 'php ' . __FILE__ . ' -c /root/kaltura/packages' . PHP_EOL;

	if(isset($options['h']))
		exit(0);

	exit(-1);
}

$baseDir = trim($argv[$argc - 1]);
if(OsUtils::getOsName() == OsUtils::LINUX_OS && $baseDir[0] != '/')
{
	$baseDir = __DIR__ . "/$baseDir";
}
elseif(OsUtils::getOsName() == OsUtils::WINDOWS_OS && !preg_match('/^[A-Za-z]:[\\/\\\\]/', $baseDir))
{
	$baseDir = __DIR__ . "/$baseDir";
}
$baseDir = str_replace('\\', '/', $baseDir);

// installation might take a few minutes
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);
ini_set('max_input_time ', 0);

date_default_timezone_set(@date_default_timezone_get());

$silentRun = isset($options['s']);
$verbose = isset($options['v']);
$autoGenerateKey = isset($options['a']);
$keepTemp = isset($options['k']);

// start the log
$logPath = __DIR__ . '/package.' . date("Y.m.d_H.i.s") . '.log';
$detailsLogPath = null;
Logger::init($logPath, $verbose);
Logger::logMessage(Logger::LEVEL_INFO, "Command: " . implode(' ', $argv));
if(!$verbose)
{
	$detailsLogPath = __DIR__ . '/package.' . date("Y.m.d_H.i.s") . '.details.log';
	OsUtils::setLogPath($detailsLogPath);
}

OsUtils::setLogPath($detailsLogPath);

echo PHP_EOL;
Logger::logColorMessage(Logger::COLOR_LIGHT_BLUE, Logger::LEVEL_USER, "Kaltura Video Platform - Server Installation Packager");

$type = AppConfig::K_TM_TYPE;

AppConfig::init(__DIR__, $type);
AppConfig::set(AppConfigAttribute::VERBOSE, $verbose);
if($autoGenerateKey)
	AppConfig::set(AppConfigAttribute::ACTIVATION_KEY, true);

$configure = false;
if(isset($options['c']))
	$configure = true;
if(!$silentRun && !$configure && AppConfig::getTrueFalse(null, "Would you like to configure the package?", 'y'))
	$configure = true;
if ($configure)
	AppConfig::configure($silentRun, true);

$packageName = AppConfig::get(AppConfigAttribute::KALTURA_VERSION_TYPE) . "-" . AppConfig::get(AppConfigAttribute::KALTURA_VERSION);
$directoryConstructorDir = __DIR__ . '/directoryConstructor';
$xmlUri = "$directoryConstructorDir/directories." . AppConfig::get(AppConfigAttribute::KALTURA_VERSION_TYPE) . '.xml';
$xmlUri = str_replace('\\', '/', $xmlUri);
$tempDir = $baseDir . "/$packageName";
$tempDir = str_replace('//', '/', $tempDir);

$attributes = array(
	'package.dir' => $baseDir,
	'package.type' => AppConfig::get(AppConfigAttribute::KALTURA_VERSION_TYPE),
	'package.version' => AppConfig::get(AppConfigAttribute::KALTURA_VERSION),
	'BASE_DIR' => $tempDir,
	'xml.uri' => $xmlUri,
);

ProgressBar::addHeader(Logger::colorMessage(Logger::COLOR_LIGHT_BLUE, 'Kaltura Video Platform - Server Installation Packager'));
ProgressBar::addHeader(" - Build code directories tree", 'directories');
ProgressBar::addHeader(" - Convert code from Dos format to Unix", 'dos2Unix');
ProgressBar::addHeader(" - Compressing and archiving", 'package');
ProgressBar::addHeader('');

$phingProcess = new ProgressProcess(OsUtils::getPhingCommand('Pack', $attributes), 'Pack', $directoryConstructorDir);
if($detailsLogPath)
{
	$phingProcess->setStandardOutput($detailsLogPath);
	$phingProcess->setStandardError($detailsLogPath);
}

Logger::logColorMessage(Logger::COLOR_YELLOW, Logger::LEVEL_USER, "Packaging...", false);
OsUtils::runProgressBar(array($phingProcess));

$tarPath = "$tempDir.tgz";
if(!file_exists($tarPath))
{
	Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, "Packaging failed", true);
	Logger::logMessage(Logger::LEVEL_USER, "Packaging log files:");
	Logger::logMessage(Logger::LEVEL_USER, "\t - $logPath");
	if($detailsLogPath)
		Logger::logMessage(Logger::LEVEL_USER, "\t - $detailsLogPath");
	exit(-1);
}

Logger::logColorMessage(Logger::COLOR_GREEN, Logger::LEVEL_USER, "Packaging successfully finished", true);
if(!$keepTemp && ($silentRun || AppConfig::getTrueFalse(null, "Would you like to delete the package temporary directory ($tempDir)?", 'y')))
{
	Logger::logMessage(Logger::LEVEL_USER, "Deleting temporary directory ($tempDir)...", false);
	OsUtils::recursiveDelete($tempDir);
	Logger::logMessage(Logger::LEVEL_USER, " - done", true, 3);
}

Logger::logColorMessage(Logger::COLOR_LIGHT_GREEN, Logger::LEVEL_USER, "Package available at $tarPath");
exit(0);

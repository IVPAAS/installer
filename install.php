<?php

include_once('installer/DatabaseUtils.class.php');
include_once('installer/OsUtils.class.php');
include_once('installer/Log.php');
include_once('installer/InstallReport.class.php');
include_once('installer/AppConfig.class.php');
include_once('installer/Installer.class.php');
include_once('installer/Validator.class.php');
include_once('installer/phpmailer/class.phpmailer.php');

// installation might take a few minutes
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);
ini_set('max_input_time ', 0);

date_default_timezone_set(@date_default_timezone_get());

$options = getopt('hsudvafC:p:g::e::');
if(isset($options['h']))
{
	echo 'Usage is php ' . __FILE__ . ' [arguments]'.PHP_EOL;
	echo " -h - Show this help." . PHP_EOL;
	echo " -s - Silent mode, no questions will be asked." . PHP_EOL;
	echo " -u - Uninstall previous installation." . PHP_EOL;
	echo " -f - Force installation." . PHP_EOL;
	echo " -p - Package XML path or URL." . PHP_EOL;
	echo " -d - Don't validate installation." . PHP_EOL;
	echo " -v - Verbose output." . PHP_EOL;
	echo " -g - Upgrade from version (8 - Hercules)." . PHP_EOL;
	echo " -e - E-mail logs and results." . PHP_EOL;
	echo " -C - Comma seperated components list (api,db,sphinx,populate,batch,dwh,admin,var,apps,cleanup,ssl,monitor)." . PHP_EOL;
	echo "      Use * for all default components, for example, *,ssl." . PHP_EOL;
	
	// don't tell anyone it's possible
	// echo " -a - Auto-generate activation key." . PHP_EOL;
	
	echo PHP_EOL;
	echo "Examples:" . PHP_EOL;
	echo 'php ' . __FILE__ . ' -s' . PHP_EOL;
	echo 'php ' . __FILE__ . ' -C api,db,sphinx' . PHP_EOL;

	exit(0);
}

$silentRun = isset($options['s']);
$uninstall = isset($options['u']);
$dontValidate = isset($options['d']);
$verbose = isset($options['v']);
$force = isset($options['f']);
$autoGenerateKey = isset($options['a']);
$emailResults = isset($options['e']) ? $options['e'] : null;

$upgrade = false;
if(isset($options['g']))
	$upgrade = is_bool($options['g']) ? '7' : $options['g']; // if version not specified, upgrade from falcon

// start the log
$logPath = __DIR__ . '/install.' . date("Y.m.d_H.i.s") . '.log';
$detailsLogPath = null;
Logger::init($logPath, $verbose);
Logger::setEmail($emailResults);
Logger::logMessage(Logger::LEVEL_INFO, "Command: " . implode(' ', $argv));
Logger::logMessage(Logger::LEVEL_INFO, "Options: [" . print_r($options, true) . "]");
if(!$verbose)
{
	$detailsLogPath = __DIR__ . '/install.' . date("Y.m.d_H.i.s") . '.details.log';
	OsUtils::setLogPath($detailsLogPath);
}

echo PHP_EOL;
Logger::logColorMessage(Logger::COLOR_LIGHT_BLUE, Logger::LEVEL_USER, "Kaltura Video Platform - Server Installation");

$downloadCode = false;
$packageDir = realpath(__DIR__ . '/../package');
if($packageDir)
	AppConfig::init($packageDir);
else
	$downloadCode = true;

if(isset($options['C']))
	AppConfig::setCurrentMachineComponents(explode(',', $options['C']));
	
AppConfig::set(AppConfigAttribute::VERBOSE, $verbose);
if($autoGenerateKey)
	AppConfig::set(AppConfigAttribute::ACTIVATION_KEY, true);

AppConfig::configure($silentRun, false, $upgrade);

$downloadAttributes = array();
if(isset($options['p']))
{
	$xmlUri = $options['p'];
	if(parse_url($xmlUri, PHP_URL_SCHEME))
	{
		$tmp = tempnam(sys_get_temp_dir(), 'dirs.');
		file_put_contents($tmp, file_get_contents($xmlUri));
		$xmlUri = $tmp;
	}

	$downloadAttributes = array(
		'xml.uri' => $xmlUri,
	);
	$downloadCode = true;
}

echo PHP_EOL;
Logger::logColorMessage(Logger::COLOR_YELLOW, Logger::LEVEL_USER, "Installing Kaltura " . AppConfig::get(AppConfigAttribute::KALTURA_VERSION));
Logger::logMessage(Logger::LEVEL_USER, "Thank you for installing Kaltura Video Platform");

echo PHP_EOL;



$report = null;

{
	$report_message = "If you wish, please provide your email address so that we can offer you future assistance (leave empty to pass)";
	$report_error_message = "Email must be in a valid email format";
	$report_validator = InputValidator::createEmailValidator(true);

	$email = AppConfig::getInput(AppConfigAttribute::REPORT_ADMIN_EMAIL, $report_message, $report_error_message, $report_validator, null);
	if($email)
	{
		AppConfig::set(AppConfigAttribute::REPORT_ADMIN_EMAIL, $email);
		AppConfig::set(AppConfigAttribute::TRACK_KDPWRAPPER, 'true');
		AppConfig::set(AppConfigAttribute::USAGE_TRACKING_OPTIN, 'true');
		$report = new InstallReport($email, AppConfig::get(AppConfigAttribute::KALTURA_VERSION), AppConfig::get(AppConfigAttribute::INSTALLATION_SEQUENCE_UID), AppConfig::get(AppConfigAttribute::INSTALLATION_UID));
		$report->reportInstallationStart();
	}
}

// verify prerequisites
echo PHP_EOL;
Logger::logColorMessage(Logger::COLOR_YELLOW, Logger::LEVEL_USER, "Verifing prerequisites");

$validator = new Validator();
$prerequisites = $validator->validate();

if (count($prerequisites))
{
	if ($report)
		$report->reportInstallationFailed("One or more prerequisites required to install Kaltura failed:\n" . implode("\n", $prerequisites));

	Logger::recordEmail();
	Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, "One or more prerequisites required to install Kaltura failed:");
	Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, implode("\n", $prerequisites));
	Logger::sendEmail();
	
	if(!$force && !AppConfig::getTrueFalse(null, "Please resolve the issues and run the installation again. Do you want to install Kaltura server anyway and resolve all issues later?", 'n'))
		exit(-1);
}

// verify that there are no leftovers from previous installations
echo PHP_EOL;
Logger::logColorMessage(Logger::COLOR_YELLOW, Logger::LEVEL_USER, "Checking for leftovers from a previous installation");

$installer = new Installer();
$leftovers = $installer->detectLeftovers(true);
if (isset($leftovers)) {
	Logger::logMessage(Logger::LEVEL_USER, $leftovers);

	if($silentRun)
	{
		if(!$uninstall && !$force)
		{
			Logger::recordEmail();
			$description = "Installation cannot continue because a previous installation of Kaltura was detected.\n" . $leftovers;
			Logger::sendEmail();
			
			if ($report)
				$report->reportInstallationFailed($description);
	
			Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, "Please manually uninstall Kaltura before running the installation or select yes to remove the leftovers.");
			exit(-2);
		}
	}
	elseif (!$uninstall && !$force && AppConfig::getTrueFalse(AppConfigAttribute::UNINSTALL, "Leftovers from a previouse Kaltura installation have been detected. In order to continue with the current installation these leftovers must be removed. Do you wish to remove them now?", 'n'))
	{
		$uninstall = true;
	}
	
	if ($uninstall || $upgrade)
	{
		echo PHP_EOL;
		Logger::logColorMessage(Logger::COLOR_YELLOW, Logger::LEVEL_USER, "Removing leftovers from a previous installation");
		$installer->detectLeftovers(false);
	}
}

if($downloadCode)
{
	Logger::recordEmail();
	Logger::logMessage(Logger::LEVEL_USER, "Downloading Kaltura server...", false);
	if(!OsUtils::phing(__DIR__ . '/directoryConstructor', 'Construct', $downloadAttributes))
	{
		Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, " failed.", true, 3);
		Logger::sendEmail();
		exit(-1);
	}
	Logger::clearEmail();
	Logger::logColorMessage(Logger::COLOR_GREEN, Logger::LEVEL_USER, " - done.", true, 3);
	echo PHP_EOL;
}

// run the installation
echo PHP_EOL;
Logger::logColorMessage(Logger::COLOR_YELLOW, Logger::LEVEL_USER, "Installing Kaltura Server");
$install_output = $installer->install($packageDir, $dontValidate);
if ($install_output !== null)
{
	Logger::recordEmail();
	$description = "Installation failed.";
	Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, $install_output);
	Logger::sendEmail();

	if ($report)
		$report->reportInstallationFailed($description);

	$leftovers = $installer->detectLeftovers(true);
	if (isset($leftovers) && AppConfig::getTrueFalse(null, "Do you want to cleanup?", 'n')) {
		$installer->detectLeftovers(false);
	}


	Logger::logMessage(Logger::LEVEL_USER, "For assistance, please contact the support team at support@kaltura.com with the installation log files attached");

	Logger::logMessage(Logger::LEVEL_USER, "Installation log files:");
	Logger::logMessage(Logger::LEVEL_USER, "\t - $logPath");

	if($detailsLogPath)
		Logger::logMessage(Logger::LEVEL_USER, "\t - $detailsLogPath");

	exit(1);
}

if ($report)
	$report->reportInstallationSuccess();

Logger::sendErrors();
exit(0);

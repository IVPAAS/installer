<?php
include_once( __DIR__ . '/InputValidator.class.php');

define('TOKEN_CHAR', '@'); // this character is user to surround parameters that should be replaced with configurations in config files
define('TEMPLATE_FILE', '.template'); // how to recognize a template file, template files are copyed to non-template and then the tokens are replaced


class AppConfigAttribute
{
	const BASE_DIR = 'BASE_DIR';
	const APP_DIR = 'APP_DIR';
	const WEB_DIR = 'WEB_DIR';
	const BIN_DIR = 'BIN_DIR';
	const LOG_DIR = 'LOG_DIR';
	const TMP_DIR = 'TMP_DIR';
	const DWH_DIR = 'DWH_DIR';
	const ETL_HOME_DIR = 'ETL_HOME_DIR';

	const PHP_BIN = 'PHP_BIN';
	const PEAR_BIN = 'PEAR_BIN';
	const HTTPD_BIN = 'HTTPD_BIN';
	const LOG_ROTATE_BIN = 'LOG_ROTATE_BIN';
	const MYSQL_BIN = 'MYSQL_BIN';
	const IMAGE_MAGICK_BIN_DIR = 'IMAGE_MAGICK_BIN_DIR';
	const CURL_BIN_DIR = 'CURL_BIN_DIR';
	const SPHINX_BIN_DIR = 'SPHINX_BIN_DIR';

	const OS_TYPE = 'OS_TYPE';
	const OS_DISTRO = 'OS_DISTRO';
	
	const OS_ROOT_USER = 'OS_ROOT_USER';
	const OS_APACHE_USER = 'OS_APACHE_USER';
	const OS_NAGIOS_USER = 'OS_NAGIOS_USER';
	const OS_KALTURA_USER = 'OS_KALTURA_USER';

	const OS_ROOT_GROUP = 'OS_ROOT_GROUP';
	const OS_APACHE_GROUP = 'OS_APACHE_GROUP';
	const OS_NAGIOS_GROUP = 'OS_NAGIOS_GROUP';
	const OS_KALTURA_GROUP = 'OS_KALTURA_GROUP';

	const OS_ROOT_UID = 'OS_ROOT_UID';
	const OS_APACHE_UID = 'OS_APACHE_UID';
	const OS_KALTURA_UID = 'OS_KALTURA_UID';

	const OS_ROOT_GID = 'OS_ROOT_GID';
	const OS_APACHE_GID = 'OS_APACHE_GID';
	const OS_KALTURA_GID = 'OS_KALTURA_GID';
	
	const DB_ROOT_USER = 'DB_ROOT_USER';
	const DB_ROOT_PASS = 'DB_ROOT_PASS';

	const DB1_HOST = 'DB1_HOST';
	const DB1_PORT = 'DB1_PORT';
	const DB1_USER = 'DB1_USER';
	const DB1_PASS = 'DB1_PASS';
	const DB1_NAME = 'DB1_NAME';

	const DB2_HOST = 'DB2_HOST';
	const DB2_PORT = 'DB2_PORT';
	const DB2_USER = 'DB2_USER';
	const DB2_PASS = 'DB2_PASS';
	const DB2_NAME = 'DB2_NAME';

	const DB3_HOST = 'DB3_HOST';
	const DB3_PORT = 'DB3_PORT';
	const DB3_USER = 'DB3_USER';
	const DB3_PASS = 'DB3_PASS';
	const DB3_NAME = 'DB3_NAME';

	const SPHINX_SERVER = 'SPHINX_SERVER';
	const SPHINX_SERVER_INDEX = 'SPHINX_SERVER_INDEX';
	const SPHINX_SERVER1 = 'SPHINX_SERVER1';
	const SPHINX_SERVER2 = 'SPHINX_SERVER2';
	const SPHINX_DB_HOST = 'SPHINX_DB_HOST';
	const SPHINX_DB_PORT = 'SPHINX_DB_PORT';
	const SPHINX_DB_NAME = 'SPHINX_DB_NAME';

	const DWH_HOST = 'DWH_HOST';
	const DWH_PORT = 'DWH_PORT';
	const DWH_USER = 'DWH_USER';
	const DWH_PASS = 'DWH_PASS';
	const DWH_DATABASE_NAME = 'DWH_DATABASE_NAME';

	const DB1_CREATE_NEW_DB = 'DB1_CREATE_NEW_DB';
	const DB1_CREATE_NEW_DWH = 'DB1_CREATE_NEW_DWH';
	const UNINSTALL = 'UNINSTALL';
	const UPGRADE_FROM_VERSION = 'UPGRADE_FROM_VERSION';
	const MULTIPLE_SERVER_ENVIRONMENT = 'MULTIPLE_SERVER_ENVIRONMENT';

	const EVENTS_LOGS_DIR = 'EVENTS_LOGS_DIR';
	const EVENTS_WILDCARD = 'EVENTS_WILDCARD';
	const EVENTS_FETCH_METHOD = 'EVENTS_FETCH_METHOD';
	const ADMIN_CONSOLE_ADMIN_MAIL = 'ADMIN_CONSOLE_ADMIN_MAIL';
	const ADMIN_CONSOLE_PASSWORD = 'ADMIN_CONSOLE_PASSWORD';
	const REPORT_ADMIN_EMAIL = 'REPORT_ADMIN_EMAIL';

	const BATCH_SCHEDULER_ID = 'BATCH_SCHEDULER_ID';
	const BATCH_SCHEDULER_TEMPLATE = 'BATCH_SCHEDULER_TEMPLATE';

	const BATCH_PARTNER_ADMIN_SECRET = 'BATCH_PARTNER_ADMIN_SECRET';
	const PARTNER_ZERO_ADMIN_SECRET = 'PARTNER_ZERO_ADMIN_SECRET';
	const ADMIN_CONSOLE_PARTNER_ADMIN_SECRET = 'ADMIN_CONSOLE_PARTNER_ADMIN_SECRET';
	const HOSTED_PAGES_PARTNER_ADMIN_SECRET = 'HOSTED_PAGES_PARTNER_ADMIN_SECRET';
	const TEMPLATE_PARTNER_ADMIN_SECRET = 'TEMPLATE_PARTNER_ADMIN_SECRET';
	const MONITOR_PARTNER_SECRET = 'MONITOR_PARTNER_SECRET';
	const MONITOR_PARTNER_ADMIN_SECRET = 'MONITOR_PARTNER_ADMIN_SECRET';

	const PARTNERS_USAGE_REPORT_SEND_FROM = 'PARTNERS_USAGE_REPORT_SEND_FROM';
	const PARTNERS_USAGE_REPORT_SEND_TO = 'PARTNERS_USAGE_REPORT_SEND_TO';
	const DC0_SECRET = 'DC0_SECRET';
	const STORAGE_BASE_DIR = 'STORAGE_BASE_DIR';
	const DELIVERY_HTTP_BASE_URL = 'DELIVERY_HTTP_BASE_URL';
	const DELIVERY_RTMP_BASE_URL = 'DELIVERY_RTMP_BASE_URL';
	const DELIVERY_ISS_BASE_URL = 'DELIVERY_ISS_BASE_URL';

	const PROVISION_PROVIDE_USER = 'PROVISION_PROVIDE_USER';
	const PROVISION_PROVIDE_PASS = 'PROVISION_PROVIDE_PASS';
	const PROVISION_PROVIDE_CPCODE = 'PROVISION_PROVIDE_CPCODE';
	const PROVISION_PROVIDE_EMAIL_ID = 'PROVISION_PROVIDE_EMAIL_ID';
	const PROVISION_PROVIDE_PRIMARY_CONTACT = 'PROVISION_PROVIDE_PRIMARY_CONTACT';
	const PROVISION_PROVIDE_SECONDARY_CONTACT = 'PROVISION_PROVIDE_SECONDARY_CONTACT';

	const SERVICE_URL = 'SERVICE_URL';
	const TIME_ZONE = 'TIME_ZONE';
	const CDN_HOST = 'CDN_HOST';
	const IIS_HOST = 'IIS_HOST';
	const WWW_HOST = 'WWW_HOST';
	const RTMP_URL = 'RTMP_URL';
	const CORP_REDIRECT = 'CORP_REDIRECT';
	const MEMCACHE_HOST = 'MEMCACHE_HOST';
	const GLOBAL_MEMCACHE_HOST = 'GLOBAL_MEMCACHE_HOST';
	const KALTURA_VIRTUAL_HOST_NAME = 'KALTURA_VIRTUAL_HOST_NAME';
	const KALTURA_VIRTUAL_HOST_PORT = 'KALTURA_VIRTUAL_HOST_PORT';
	const KALTURA_FULL_VIRTUAL_HOST_NAME = 'KALTURA_FULL_VIRTUAL_HOST_NAME';
	const POST_INST_VIRTUAL_HOST_NAME = 'POST_INST_VIRTUAL_HOST_NAME';
	const POST_INST_ADMIN_CONSOLE_ADMIN_MAIL = 'POST_INST_ADMIN_CONSOLE_ADMIN_MAIL';
	const ENVIRONMENT_NAME = 'ENVIRONMENT_NAME';
	const APACHE_SERVICE = 'APACHE_SERVICE';
	const ENVIRONMENT_PROTOCOL = 'ENVIRONMENT_PROTOCOL';
	const INSTALLED_HOSNAME = 'INSTALLED_HOSNAME';

	const CONTACT_URL = 'CONTACT_URL';
	const SIGNUP_URL = 'SIGNUP_URL';
	const CONTACT_PHONE_NUMBER = 'CONTACT_PHONE_NUMBER';
	const BEGINNERS_TUTORIAL_URL = 'BEGINNERS_TUTORIAL_URL';
	const QUICK_START_GUIDE_URL = 'QUICK_START_GUIDE_URL';
	const FORUMS_URLS = 'FORUMS_URLS';
	const UNSUBSCRIBE_EMAIL_URL = 'UNSUBSCRIBE_EMAIL_URL';
	const GOOGLE_ANALYTICS_ACCOUNT = 'GOOGLE_ANALYTICS_ACCOUNT';
	const INSTALLATION_UID = 'INSTALLATION_UID';
	const INSTALLATION_SEQUENCE_UID = 'INSTALLATION_SEQUENCE_UID';
	const APP_REMOTE_ADDR_HEADER_SALT = 'APP_REMOTE_ADDR_HEADER_SALT';

	const ACTIVATION_KEY = 'ACTIVATION_KEY';
	const KALTURA_VERSION_TYPE = 'KALTURA_VERSION_TYPE';
	const KALTURA_VERSION = 'KALTURA_VERSION';
	
	const UICONF_TAB_ACCESS = 'UICONF_TAB_ACCESS';

	const TRACK_KDPWRAPPER = 'TRACK_KDPWRAPPER';
	const USAGE_TRACKING_OPTIN = 'USAGE_TRACKING_OPTIN';

	const API_MACHINES_NAMES = 'API_MACHINES_NAMES';
	const BATCH_MACHINES_NAMES = 'BATCH_MACHINES_NAMES';
	const ADMIN_MACHINES_NAMES = 'ADMIN_MACHINES_NAMES';
	const VAR_MACHINES_NAMES = 'VAR_MACHINES_NAMES';
	const DB_MACHINES_NAMES = 'DB_MACHINES_NAMES';
	const SPHINX_MACHINES_NAMES = 'SPHINX_MACHINES_NAMES';
	const POPULATE_MACHINES_NAMES = 'POPULATE_MACHINES_NAMES';
	const DWH_MACHINES_NAMES = 'DWH_MACHINES_NAMES';
	const RED5_MACHINES_NAMES = 'RED5_MACHINES_NAMES';
	
	const MACHINE_NAME = 'MACHINE_NAME';
        
	const SSL_CERTIFICATE_FILE = 'SSL_CERTIFICATE_FILE';
	const SSL_CERTIFICATE_KEY_FILE = 'SSL_CERTIFICATE_KEY_FILE';
	const SSL_CERTIFICATE_CHAIN_FILE = 'SSL_CERTIFICATE_CHAIN_FILE';

	const KMC_VERSION = 'KMC_VERSION';
	const KMC_LOGIN_VERSION = 'KMC_LOGIN_VERSION';
	const CLIPAPP_VERSION = 'CLIPAPP_VERSION';
	const HTML5_VERSION = 'HTML5_VERSION';

	const VERIFY_INSTALLATION = 'VERIFY_INSTALLATION';
	const DEPLOY_KMC = 'DEPLOY_KMC';
	const VERBOSE = 'VERBOSE';
}

/**
 * This class handles all the configuration of the application:
 * Defining application configuration values according to user input,
 * replaceing configuration tokens in needed files and other application configuration actions
 */
class AppConfig
{
	const K_TM_TYPE = 'TM';
	const K_CE_TYPE = 'CE';
	
	const KEY_NO_EXPIRE = 'never';
	const KEY_TYPE_ACTIVATION = 1; // activation key type
	const KEY_TYPE_EXTENSION  = 2; // extension key type

	/**
	 * @var string
	 */
	private static $hostname = null;

	/**
	 * @var array
	 */
	private static $config = array();

	/**
	 * Configuration file path, used as configuration for installer sub-processes
	 * @var string
	 */
	private static $tempFilePath = null;

	/**
	 * Configuration file path, as supplied to the installer, the wizard output will be saved to the same path for future installtaions
	 * @var string
	 */
	private static $inputFilePath = null;

	/**
	 * @var array
	 */
	private static $kConf = null;

	/**
	 * @var string
	 */
	private static $packageDir = null;

	/**
	 * @var array
	 */
	private static $components = null;

	/**
	 * Initialize all configuration variables from ini file or from wizard
	 */
	public static function init($packageDir, $type = null)
	{
		self::$packageDir = $packageDir;
		$versionPath = self::$packageDir . "/version.ini";
		if(file_exists($versionPath))
		{
			$version = parse_ini_file($versionPath);

			if(is_null($type))
				$type = $version['type'];

			self::set(AppConfigAttribute::KALTURA_VERSION, 'Kaltura-' . $type . '-' . $version['number']);
			self::set(AppConfigAttribute::KALTURA_VERSION_TYPE, $type);
		}
		else
		{
			if(is_null($type))
				$type = self::K_CE_TYPE;

			self::initField(AppConfigAttribute::KALTURA_VERSION_TYPE, $type);
			self::initField(AppConfigAttribute::KALTURA_VERSION, "Kaltura-$type");
		}
		
		self::initField(AppConfigAttribute::OS_TYPE, OsUtils::getOsName());
		$distroName = OsUtils::getOsDistroName();
		self::initField(AppConfigAttribute::OS_DISTRO, $distroName);
		
		// Override apache values in Ubuntu
		if ( $distroName == OsUtils::OS_DISTRO_UBUNTU )
		{
			// Set the apache user to www-data
			self::initField(AppConfigAttribute::OS_APACHE_USER, 'www-data');
		}		
	}

	public static function validateActivationKey($key)
	{
		if(!trim($key) || $key == 'false')
			throw new InputValidatorException("To start your evaluation please supply a valid activation key");
			
		$data = explode('|', base64_decode($key));
		if(count($data) != 4)
			throw new InputValidatorException("Activation key is invalid");
			
		list($adminEmailMd5, $type, $expiryTime, $token) = $data;
		if($type != self::KEY_TYPE_ACTIVATION && $type != self::KEY_TYPE_EXTENSION)
			throw new InputValidatorException("Activation key type is invalid");
			
//		$adminEmail = AppConfig::get(AppConfigAttribute::ADMIN_CONSOLE_ADMIN_MAIL);
//		if($adminEmailMd5 != md5($adminEmail))
//			throw new InputValidatorException("Activation key is not valid for admin e-mail: $adminEmail");
			
		if($expiryTime != self::KEY_NO_EXPIRE)
		{
			$daysLeft = $expiryTime - time();
			$daysLeft = ceil($daysLeft / (60*60*24));
			if ($daysLeft <= 0)
				throw new InputValidatorException("Activation key expired");
		}
		
		return true;
	}

	public static function calculateActivationKey()
	{
		if (AppConfig::get(AppConfigAttribute::KALTURA_VERSION_TYPE) == AppConfig::K_CE_TYPE)
			AppConfig::set(AppConfigAttribute::ACTIVATION_KEY, 'false');

		if(AppConfig::get(AppConfigAttribute::ACTIVATION_KEY) === true)
		{
			$admin_email = AppConfig::get(AppConfigAttribute::ADMIN_CONSOLE_ADMIN_MAIL);
			$token = md5(uniqid(rand(), true));
			$str = implode("|", array(md5($admin_email), self::KEY_TYPE_ACTIVATION, self::KEY_NO_EXPIRE, $token));
			AppConfig::set(AppConfigAttribute::ACTIVATION_KEY, base64_encode($str));
		}
	}

	public static function getUserInputFilePath()
	{
		if(!self::$inputFilePath)
			self::$inputFilePath = realpath(__DIR__ . '/../') . '/user_input.ini';
			
		return self::$inputFilePath;
	}

	/**
	 * Initialize all configuration variables from ini file or from wizard
	 */
	public static function configure($silentRun = false, $configOnly = false, $upgrade = false)
	{
		$inputFilePath = self::getUserInputFilePath();

		if(file_exists($inputFilePath) && ($silentRun || self::getTrueFalse(null, "Installation configuration has been detected, do you want to use it?", 'y')))
		{
			$fileConfig = parse_ini_file($inputFilePath, true);
			if(isset($fileConfig[AppConfigAttribute::VERBOSE]))
				unset($fileConfig[AppConfigAttribute::VERBOSE]);
			
			self::$config = array_merge(self::$config, $fileConfig);
			if(!$upgrade && !$configOnly && !$silentRun && self::componentDefined('db'))
				unset(self::$config[AppConfigAttribute::DB1_CREATE_NEW_DB]);
				
			if(AppConfig::get(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT) && !self::componentDefined('db'))
			{
				self::set(AppConfigAttribute::DB1_CREATE_NEW_DB, false);
			}
		}

		$hostname = self::getHostname();
		
		self::calculateActivationKey();

		$defaultBaseDir = OsUtils::isWindows() ? 'C:\opt\kaltura' : '/opt/kaltura';
		if($silentRun)
		{
			self::initField(AppConfigAttribute::TIME_ZONE, date_default_timezone_get());
			self::initField(AppConfigAttribute::BASE_DIR, $defaultBaseDir);
			self::initField(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME, $hostname);
			self::initField(AppConfigAttribute::ADMIN_CONSOLE_ADMIN_MAIL, "admin@$hostname");
			self::initField(AppConfigAttribute::ADMIN_CONSOLE_PASSWORD, 'admin');
			self::initField(AppConfigAttribute::DB1_HOST, $hostname);
			self::initField(AppConfigAttribute::DB1_PORT, 3306);
			self::initField(AppConfigAttribute::DB_ROOT_USER, 'root');
			self::initField(AppConfigAttribute::DB_ROOT_PASS, 'root');
			self::initField(AppConfigAttribute::DB1_CREATE_NEW_DB, 'y');
			self::initField(AppConfigAttribute::DB1_CREATE_NEW_DWH, 'y');
			self::initField(AppConfigAttribute::SPHINX_SERVER1, ($hostname == 'localhost' ? '127.0.0.1' : $hostname));
			self::initField(AppConfigAttribute::ENVIRONMENT_PROTOCOL, 'http');
		}
		else
		{
			self::getInput(AppConfigAttribute::TIME_ZONE, "Default time zone for Kaltura application (leave empty to use system timezone: " . date_default_timezone_get() . ")", "Timezone must be a valid timezone, please enter again", InputValidator::createTimezoneValidator(), date_default_timezone_get());

			self::getInput(AppConfigAttribute::BASE_DIR, "Full target directory path for Kaltura application (leave empty for $defaultBaseDir)", "Target directory must be a valid directory path, please enter again", InputValidator::createDirectoryValidator(), $defaultBaseDir);

			self::getInput(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME, "Please enter the domain name that will be used for the Kaltura server (without http://, leave empty for $hostname)", 'Must be a valid hostname or ip, please enter again', InputValidator::createHostValidator(), $hostname);

			self::getInput(AppConfigAttribute::ADMIN_CONSOLE_ADMIN_MAIL, "Your primary system administrator email address", "Email must be in a valid email format, please enter again", InputValidator::createEmailValidator(false), null);

			self::getInput(AppConfigAttribute::ADMIN_CONSOLE_PASSWORD, "The password you want to set for your primary administrator", "Password should not be empty and should not contain whitespaces, please enter again", InputValidator::createNoWhitespaceValidator(), null, true);
		
			if(AppConfig::get(AppConfigAttribute::KALTURA_VERSION_TYPE) == AppConfig::K_TM_TYPE)
			{
				self::getInput(AppConfigAttribute::ACTIVATION_KEY, "Kaltura server activation key", null, InputValidator::createCallbackValidator(array('AppConfig', 'validateActivationKey')));
			}
			else
			{
				self::set(AppConfigAttribute::ACTIVATION_KEY, 'false');
			}
			
			self::getTrueFalse(AppConfigAttribute::DB1_CREATE_NEW_DB, "Would you like to create a new kaltura database or use an exisiting one (choose yes (y) for new database)?", 'y');
			if (AppConfigAttribute::DB1_CREATE_NEW_DB)
			{
				self::initField(AppConfigAttribute::DB1_CREATE_NEW_DWH, true);
			}
			
			self::getInput(AppConfigAttribute::DB_ROOT_USER, "Database username (with create & write privileges on all database servers, leave empty for root)", "Database username cannot be empty, please enter again", InputValidator::createNonEmptyValidator(), 'root');

			self::getInput(AppConfigAttribute::DB_ROOT_PASS, "Database password on all database servers (leave empty for no password)", null, null, null, true);

			if(!$configOnly || !self::configureMultipleServers())
			{
				self::getInput(AppConfigAttribute::DB1_HOST, "Database host (leave empty for 'localhost')", "Must be a valid hostname or ip, please enter again (leave empty for 'localhost')", InputValidator::createHostValidator(), 'localhost');

				self::getInput(AppConfigAttribute::DB1_PORT, "Database port (leave empty for '3306')", "Must be a valid port (1-65535), please enter again (leave empty for '3306')", InputValidator::createRangeValidator(1, 65535), '3306');

				self::getInput(AppConfigAttribute::SPHINX_SERVER1, "Sphinx host (leave empty to use 127.0.0.1).", null, InputValidator::createHostValidator(), '127.0.0.1');

				if(is_array(self::$components) && in_array('ssl', self::$components))
					self::initField(AppConfigAttribute::ENVIRONMENT_PROTOCOL, 'https');
				else
					self::getInput(AppConfigAttribute::ENVIRONMENT_PROTOCOL, "Environment protocol - enter http/https (leave empty for http)", 'Invalid environment protocol - please enter http or https', InputValidator::createEnumValidator(array('http', 'https'), false, true), 'http');
			}
			
			if(self::get(AppConfigAttribute::ENVIRONMENT_PROTOCOL) == 'https')
			{
				self::getInput(AppConfigAttribute::SSL_CERTIFICATE_FILE, "SSL certificate file path", 'File not found', InputValidator::createFileValidator());
				self::getInput(AppConfigAttribute::SSL_CERTIFICATE_KEY_FILE, "SSL certificate key file path", 'File not found', InputValidator::createFileValidator());
				self::getInput(AppConfigAttribute::SSL_CERTIFICATE_CHAIN_FILE, "SSL certificate chain file path", 'File not found', InputValidator::createFileValidator());
			}
			
			self::initField(AppConfigAttribute::ENVIRONMENT_PROTOCOL, 'http');
		}
	
		if(self::$components)
		{
			if(in_array('api', self::$components) || in_array('apps', self::$components) || in_array('var', self::$components) || in_array('admin', self::$components))
			{
				if(self::get(AppConfigAttribute::ENVIRONMENT_PROTOCOL) == 'https' && !in_array('ssl', self::$components))
					self::$components[] = 'ssl';
			}
		
			if(in_array('populate', self::$components))
			{
				$sphinxServerIndex = self::getCurrentMachineConfig(AppConfigAttribute::SPHINX_SERVER_INDEX);
				if($sphinxServerIndex)
					self::set(AppConfigAttribute::SPHINX_SERVER, self::get(AppConfigAttribute::SPHINX_SERVER . $sphinxServerIndex));
			}
		}
		self::initField(AppConfigAttribute::SPHINX_SERVER, self::get(AppConfigAttribute::SPHINX_SERVER1));

		self::initField(AppConfigAttribute::KALTURA_VIRTUAL_HOST_PORT, (self::get(AppConfigAttribute::ENVIRONMENT_PROTOCOL) == 'https' ? 443 : 80));
		if(strpos(self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME), ":"))
			self::set(AppConfigAttribute::KALTURA_VIRTUAL_HOST_PORT, parse_url(self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME), PHP_URL_PORT));
		elseif(self::get(AppConfigAttribute::KALTURA_VIRTUAL_HOST_PORT) != 80 && self::get(AppConfigAttribute::KALTURA_VIRTUAL_HOST_PORT) != 443)
			self::set(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME, self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME) . ':' . self::get(AppConfigAttribute::KALTURA_VIRTUAL_HOST_PORT));

		if(!self::$packageDir)
			self::init(self::get(AppConfigAttribute::BASE_DIR));

		self::initField(AppConfigAttribute::DB1_NAME, 'kaltura');

		self::set(AppConfigAttribute::INSTALLATION_UID, uniqid("IID")); // unique id per installation

		// load or create installation sequence id
		$installSeqFilePath = __DIR__ . '/../install_seq';
		if(file_exists($installSeqFilePath) && is_file($installSeqFilePath))
		{
			self::set(AppConfigAttribute::INSTALLATION_SEQUENCE_UID, file_get_contents($installSeqFilePath));
		}
		else
		{
			$install_seq = uniqid("ISEQID"); // unique id per a set of installations
			self::set(AppConfigAttribute::INSTALLATION_SEQUENCE_UID, $install_seq);
			file_put_contents($installSeqFilePath, $install_seq);
		}

		// allow ui conf tab only for CE installation
		if (self::get(AppConfigAttribute::KALTURA_VERSION_TYPE) == AppConfig::K_TM_TYPE)
			self::initField(AppConfigAttribute::UICONF_TAB_ACCESS, 'SYSTEM_ADMIN_BATCH_CONTROL');

		// directories
		self::initField(AppConfigAttribute::APP_DIR, self::get(AppConfigAttribute::BASE_DIR) . '/app');
		self::initField(AppConfigAttribute::WEB_DIR, self::get(AppConfigAttribute::BASE_DIR) . '/web');
		self::initField(AppConfigAttribute::LOG_DIR, self::get(AppConfigAttribute::BASE_DIR) . '/log');
		self::initField(AppConfigAttribute::BIN_DIR, self::get(AppConfigAttribute::BASE_DIR) . '/bin');
		self::initField(AppConfigAttribute::TMP_DIR, self::get(AppConfigAttribute::BASE_DIR) . '/tmp/');
		self::initField(AppConfigAttribute::DWH_DIR, self::get(AppConfigAttribute::BASE_DIR) . '/dwh');
		self::initField(AppConfigAttribute::ETL_HOME_DIR, self::get(AppConfigAttribute::BASE_DIR) . '/dwh'); // For backward compatibility
		self::initField(AppConfigAttribute::SPHINX_BIN_DIR, self::get(AppConfigAttribute::BIN_DIR) . '/sphinx');

		self::initField(AppConfigAttribute::IMAGE_MAGICK_BIN_DIR, "/usr/bin");
		self::initField(AppConfigAttribute::CURL_BIN_DIR, "/usr/bin");

		// site settings
		self::initField(AppConfigAttribute::KALTURA_VIRTUAL_HOST_NAME, self::extractHostName(self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME)));
		self::initField(AppConfigAttribute::ENVIRONMENT_NAME, self::get(AppConfigAttribute::KALTURA_VIRTUAL_HOST_NAME));
		self::initField(AppConfigAttribute::CORP_REDIRECT, '');
		self::initField(AppConfigAttribute::CDN_HOST, self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME));
		self::initField(AppConfigAttribute::IIS_HOST, self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME));
		self::initField(AppConfigAttribute::RTMP_URL, 'rtmp://' . self::get(AppConfigAttribute::ENVIRONMENT_NAME) . '/oflaDemo');
		self::initField(AppConfigAttribute::MEMCACHE_HOST, self::get(AppConfigAttribute::KALTURA_VIRTUAL_HOST_NAME));
		self::initField(AppConfigAttribute::GLOBAL_MEMCACHE_HOST, self::get(AppConfigAttribute::KALTURA_VIRTUAL_HOST_NAME));
		self::initField(AppConfigAttribute::WWW_HOST, self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME));
		self::initField(AppConfigAttribute::SERVICE_URL, self::get(AppConfigAttribute::ENVIRONMENT_PROTOCOL) . '://' . self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME));

		// admin console defaults
		self::initField(AppConfigAttribute::UICONF_TAB_ACCESS, 'SYSTEM_ADMIN_BATCH_CONTROL');

		// data warehouse
		self::initField(AppConfigAttribute::DWH_HOST, self::get(AppConfigAttribute::DB1_HOST));
		self::initField(AppConfigAttribute::DWH_PORT, self::get(AppConfigAttribute::DB1_PORT));
		self::initField(AppConfigAttribute::DWH_DATABASE_NAME, 'kalturadw');
		self::initField(AppConfigAttribute::EVENTS_LOGS_DIR, self::get(AppConfigAttribute::WEB_DIR) . '/log');
		self::initField(AppConfigAttribute::EVENTS_WILDCARD, '.*kaltura.*_apache_access.*.log-.*');
		self::initField(AppConfigAttribute::EVENTS_FETCH_METHOD, 'local');

		// other configurations
		if(OsUtils::getOsName() == OsUtils::WINDOWS_OS)
		{
			$mysqlBin = 'C:\xampp\mysql\bin\mysql.exe';
			if(file_exists($mysqlBin))
				self::initField(AppConfigAttribute::MYSQL_BIN, $mysqlBin);
			elseif (!$silentRun)
				self::getInput(AppConfigAttribute::MYSQL_BIN, 'Please specify the path to mysql.exe client file', "File not found, please specify full path of mysql.exe, e.g. $mysqlBin", InputValidator::createFileValidator());
		
			$httpBin = 'C:\xampp\apache\bin\httpd.exe';
			if(file_exists($httpBin))
				self::initField(AppConfigAttribute::HTTPD_BIN, $httpBin);
			elseif (!$silentRun)
				self::getInput(AppConfigAttribute::HTTPD_BIN, 'Please specify the path to apache httpd.exe file', "File not found, please specify full path of httpd.exe, e.g. $httpBin", InputValidator::createFileValidator());
		
			$phpBin = 'C:\xampp\php\php.exe';
			if(file_exists($phpBin))
				self::initField(AppConfigAttribute::PHP_BIN, $phpBin);
			elseif(isset($_SERVER['PHP_PEAR_PHP_BIN']))
				self::initField(AppConfigAttribute::PHP_BIN, $_SERVER['PHP_PEAR_PHP_BIN']);
			elseif ($silentRun)
				self::initField(AppConfigAttribute::PHP_BIN, 'php');
			else
				self::getInput(AppConfigAttribute::PHP_BIN, 'Please specify the path to php.exe file', "File not found, please specify full path of php.exe, e.g. $phpBin", InputValidator::createFileValidator());
				
			$pearBin = dirname(self::get(AppConfigAttribute::PHP_BIN)) . '\pear.bat';
			if(file_exists($pearBin))
				self::initField(AppConfigAttribute::PEAR_BIN, $pearBin);
			elseif ($silentRun)
				self::initField(AppConfigAttribute::PEAR_BIN, 'pear');
			else
				self::getInput(AppConfigAttribute::PEAR_BIN, 'Please specify the path to pear.exe file', "File not found, please specify full path of pear.exe, e.g. $pearBin", InputValidator::createFileValidator());
		}
		else
		{
			self::initField(AppConfigAttribute::HTTPD_BIN, OsUtils::findBinary(array('apachectl', 'apache2ctl')));
			self::initField(AppConfigAttribute::MYSQL_BIN, OsUtils::findBinary('mysql'));
			self::initField(AppConfigAttribute::PHP_BIN, OsUtils::findBinary('php'));
			self::initField(AppConfigAttribute::PEAR_BIN, OsUtils::findBinary('pear'));
			self::initField(AppConfigAttribute::LOG_ROTATE_BIN, OsUtils::findBinary('logrotate'));
		}

		// other configurations
		self::initField(AppConfigAttribute::APACHE_SERVICE, OsUtils::findService(array('httpd', 'apache2')));
		date_default_timezone_set(self::get(AppConfigAttribute::TIME_ZONE));
		self::initField(AppConfigAttribute::GOOGLE_ANALYTICS_ACCOUNT, 'UA-7714780-1');
		self::initField(AppConfigAttribute::PARTNERS_USAGE_REPORT_SEND_FROM, '');
		self::initField(AppConfigAttribute::PARTNERS_USAGE_REPORT_SEND_TO, '');
		self::initField(AppConfigAttribute::DC0_SECRET, '');

		// storage profile related
		self::initField(AppConfigAttribute::STORAGE_BASE_DIR, self::get(AppConfigAttribute::WEB_DIR));
		self::initField(AppConfigAttribute::DELIVERY_HTTP_BASE_URL, self::get(AppConfigAttribute::SERVICE_URL));
		self::initField(AppConfigAttribute::DELIVERY_RTMP_BASE_URL, self::get(AppConfigAttribute::RTMP_URL));
		self::initField(AppConfigAttribute::DELIVERY_ISS_BASE_URL, self::get(AppConfigAttribute::SERVICE_URL));

		// set the usage tracking for Kaltura TM
		if(self::get(AppConfigAttribute::KALTURA_VERSION_TYPE) == AppConfig::K_TM_TYPE)
		{
			self::initField(AppConfigAttribute::PARTNERS_USAGE_REPORT_SEND_FROM, self::get(AppConfigAttribute::ADMIN_CONSOLE_ADMIN_MAIL));
			self::initField(AppConfigAttribute::PARTNERS_USAGE_REPORT_SEND_TO, "on-prem-monthly@kaltura.com");
		}

		// mails configurations
		self::initField(AppConfigAttribute::FORUMS_URLS, 'http://www.kaltura.org/forum');
		self::initField(AppConfigAttribute::CONTACT_URL, 'http://corp.kaltura.com/contact');
		self::initField(AppConfigAttribute::CONTACT_PHONE_NUMBER, '+1 800 871-5224');
		self::initField(AppConfigAttribute::BEGINNERS_TUTORIAL_URL, 'http://corp.kaltura.com/about/dosignup');
		self::initField(AppConfigAttribute::QUICK_START_GUIDE_URL, self::get(AppConfigAttribute::ENVIRONMENT_PROTOCOL) . '://' . self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME) . '/content/docs/pdf/KMC_User_Manual.pdf');
		self::initField(AppConfigAttribute::UNSUBSCRIBE_EMAIL_URL, self::get(AppConfigAttribute::ENVIRONMENT_PROTOCOL) . '://' . self::get(AppConfigAttribute::KALTURA_FULL_VIRTUAL_HOST_NAME) . '/index.php/extwidget/blockMail?e=');

		self::initField(AppConfigAttribute::OS_ROOT_USER, (isset($_SERVER['USER']) ? $_SERVER['USER'] : 'root'));
		self::initField(AppConfigAttribute::OS_APACHE_USER, 'apache');
		self::initField(AppConfigAttribute::OS_KALTURA_USER, 'kaltura');
		self::initField(AppConfigAttribute::OS_NAGIOS_USER, 'nagios');
		self::initField(AppConfigAttribute::OS_ROOT_GROUP, self::get(AppConfigAttribute::OS_ROOT_USER));
		self::initField(AppConfigAttribute::OS_APACHE_GROUP, self::get(AppConfigAttribute::OS_APACHE_USER));
		self::initField(AppConfigAttribute::OS_KALTURA_GROUP, self::get(AppConfigAttribute::OS_KALTURA_USER));
		self::initField(AppConfigAttribute::OS_NAGIOS_GROUP, self::get(AppConfigAttribute::OS_NAGIOS_USER));
		
		self::initField(AppConfigAttribute::OS_ROOT_UID, 0); // that's how it's created by the OS
		self::initField(AppConfigAttribute::OS_APACHE_UID, 48); // that's how it's created by the apache
		self::initField(AppConfigAttribute::OS_KALTURA_UID, 613); // the number doesn't matter, as long as it's identical in all machine in the environment
		self::initField(AppConfigAttribute::OS_ROOT_GID, self::get(AppConfigAttribute::OS_ROOT_UID));
		self::initField(AppConfigAttribute::OS_APACHE_GID, self::get(AppConfigAttribute::OS_APACHE_UID));
		self::initField(AppConfigAttribute::OS_KALTURA_GID, self::get(AppConfigAttribute::OS_KALTURA_UID));

		self::initField(AppConfigAttribute::REPORT_ADMIN_EMAIL, '');
		self::initField(AppConfigAttribute::TRACK_KDPWRAPPER, 'false');
		self::initField(AppConfigAttribute::USAGE_TRACKING_OPTIN, 'false');

		self::initField(AppConfigAttribute::DB1_USER, 'kaltura');
		self::initField(AppConfigAttribute::DB1_PASS, 'kaltura');
		self::initField(AppConfigAttribute::DWH_USER, 'kaltura_etl');
		self::initField(AppConfigAttribute::DWH_PASS, 'kaltura_etl');

		self::initField(AppConfigAttribute::DB2_HOST, self::get(AppConfigAttribute::DB1_HOST));
		self::initField(AppConfigAttribute::DB2_PORT, self::get(AppConfigAttribute::DB1_PORT));
		self::initField(AppConfigAttribute::DB2_NAME, self::get(AppConfigAttribute::DB1_NAME));
		self::initField(AppConfigAttribute::DB2_USER, self::get(AppConfigAttribute::DB1_USER));
		self::initField(AppConfigAttribute::DB2_PASS, self::get(AppConfigAttribute::DB1_PASS));

		self::initField(AppConfigAttribute::DB3_HOST, self::get(AppConfigAttribute::DB1_HOST));
		self::initField(AppConfigAttribute::DB3_PORT, self::get(AppConfigAttribute::DB1_PORT));
		self::initField(AppConfigAttribute::DB3_NAME, self::get(AppConfigAttribute::DB1_NAME));
		self::initField(AppConfigAttribute::DB3_USER, self::get(AppConfigAttribute::DB1_USER));
		self::initField(AppConfigAttribute::DB3_PASS, self::get(AppConfigAttribute::DB1_PASS));

		//sphinx
		self::initField(AppConfigAttribute::SPHINX_SERVER2, self::get(AppConfigAttribute::SPHINX_SERVER1));
		
		self::initField(AppConfigAttribute::SPHINX_DB_NAME, 'kaltura_sphinx_log');
		self::initField(AppConfigAttribute::SPHINX_DB_HOST, self::get(AppConfigAttribute::DB1_HOST));
		self::initField(AppConfigAttribute::SPHINX_DB_PORT, self::get(AppConfigAttribute::DB1_PORT));

		if(self::get(AppConfigAttribute::DB1_CREATE_NEW_DB))
		{
			self::initField(AppConfigAttribute::PARTNER_ZERO_ADMIN_SECRET, self::generateSecret());
			self::initField(AppConfigAttribute::BATCH_PARTNER_ADMIN_SECRET, self::generateSecret());
			self::initField(AppConfigAttribute::ADMIN_CONSOLE_PARTNER_ADMIN_SECRET, self::generateSecret());
			self::initField(AppConfigAttribute::HOSTED_PAGES_PARTNER_ADMIN_SECRET, self::generateSecret());
			self::initField(AppConfigAttribute::TEMPLATE_PARTNER_ADMIN_SECRET, self::generateSecret());
			self::initField(AppConfigAttribute::MONITOR_PARTNER_SECRET, self::generateSecret());
			self::initField(AppConfigAttribute::MONITOR_PARTNER_ADMIN_SECRET, self::generateSecret());
		}
		else
		{
			$output = OsUtils::executeWithOutput('echo "select admin_secret from partner where id=0" | ' . self::get(AppConfigAttribute::MYSQL_BIN) . ' -h' . self::get(AppConfigAttribute::DB1_HOST) . ' -P' . self::get(AppConfigAttribute::DB1_PORT) . ' -u' . self::get(AppConfigAttribute::DB1_USER) . ' -p' . self::get(AppConfigAttribute::DB1_PASS) . ' ' . self::get(AppConfigAttribute::DB1_NAME) . ' --skip-column-names');
			if(count($output) && $output[0])
				self::initField(AppConfigAttribute::PARTNER_ZERO_ADMIN_SECRET, $output[0]);
			else
				self::initField(AppConfigAttribute::PARTNER_ZERO_ADMIN_SECRET, self::generateSecret());
				
			$output = OsUtils::executeWithOutput('echo "select admin_secret from partner where id=-1" | ' . self::get(AppConfigAttribute::MYSQL_BIN) . ' -h' . self::get(AppConfigAttribute::DB1_HOST) . ' -P' . self::get(AppConfigAttribute::DB1_PORT) . ' -u' . self::get(AppConfigAttribute::DB1_USER) . ' -p' . self::get(AppConfigAttribute::DB1_PASS) . ' ' . self::get(AppConfigAttribute::DB1_NAME) . ' --skip-column-names');
			if(count($output) && $output[0])
				self::initField(AppConfigAttribute::BATCH_PARTNER_ADMIN_SECRET, $output[0]);
			else
				self::initField(AppConfigAttribute::BATCH_PARTNER_ADMIN_SECRET, self::generateSecret());
				
			$output = OsUtils::executeWithOutput('echo "select admin_secret from partner where id=-2" | ' . self::get(AppConfigAttribute::MYSQL_BIN) . ' -h' . self::get(AppConfigAttribute::DB1_HOST) . ' -P' . self::get(AppConfigAttribute::DB1_PORT) . ' -u' . self::get(AppConfigAttribute::DB1_USER) . ' -p' . self::get(AppConfigAttribute::DB1_PASS) . ' ' . self::get(AppConfigAttribute::DB1_NAME) . ' --skip-column-names');
			if(count($output) && $output[0])
				self::initField(AppConfigAttribute::ADMIN_CONSOLE_PARTNER_ADMIN_SECRET, $output[0]);
			else
				self::initField(AppConfigAttribute::ADMIN_CONSOLE_PARTNER_ADMIN_SECRET, self::generateSecret());
				
			$output = OsUtils::executeWithOutput('echo "select admin_secret from partner where id=-3" | ' . self::get(AppConfigAttribute::MYSQL_BIN) . ' -h' . self::get(AppConfigAttribute::DB1_HOST) . ' -P' . self::get(AppConfigAttribute::DB1_PORT) . ' -u' . self::get(AppConfigAttribute::DB1_USER) . ' -p' . self::get(AppConfigAttribute::DB1_PASS) . ' ' . self::get(AppConfigAttribute::DB1_NAME) . ' --skip-column-names');
			if(count($output) && $output[0])
				self::initField(AppConfigAttribute::HOSTED_PAGES_PARTNER_ADMIN_SECRET, $output[0]);
			else
				self::initField(AppConfigAttribute::HOSTED_PAGES_PARTNER_ADMIN_SECRET, self::generateSecret());
				
			$output = OsUtils::executeWithOutput('echo "select admin_secret from partner where id=99" | ' . self::get(AppConfigAttribute::MYSQL_BIN) . ' -h' . self::get(AppConfigAttribute::DB1_HOST) . ' -P' . self::get(AppConfigAttribute::DB1_PORT) . ' -u' . self::get(AppConfigAttribute::DB1_USER) . ' -p' . self::get(AppConfigAttribute::DB1_PASS) . ' ' . self::get(AppConfigAttribute::DB1_NAME) . ' --skip-column-names');
			if(count($output) && $output[0])
				self::initField(AppConfigAttribute::TEMPLATE_PARTNER_ADMIN_SECRET, $output[0]);
			else
				self::initField(AppConfigAttribute::TEMPLATE_PARTNER_ADMIN_SECRET, self::generateSecret());
				
			$output = OsUtils::executeWithOutput('echo "select admin_secret from partner where id=-4" | ' . self::get(AppConfigAttribute::MYSQL_BIN) . ' -h' . self::get(AppConfigAttribute::DB1_HOST) . ' -P' . self::get(AppConfigAttribute::DB1_PORT) . ' -u' . self::get(AppConfigAttribute::DB1_USER) . ' -p' . self::get(AppConfigAttribute::DB1_PASS) . ' ' . self::get(AppConfigAttribute::DB1_NAME) . ' --skip-column-names');
			if(count($output) && $output[0])
				self::initField(AppConfigAttribute::MONITOR_PARTNER_ADMIN_SECRET, $output[0]);
			else
				self::initField(AppConfigAttribute::MONITOR_PARTNER_ADMIN_SECRET, self::generateSecret());
				
			$output = OsUtils::executeWithOutput('echo "select secret from partner where id=4" | ' . self::get(AppConfigAttribute::MYSQL_BIN) . ' -h' . self::get(AppConfigAttribute::DB1_HOST) . ' -P' . self::get(AppConfigAttribute::DB1_PORT) . ' -u' . self::get(AppConfigAttribute::DB1_USER) . ' -p' . self::get(AppConfigAttribute::DB1_PASS) . ' ' . self::get(AppConfigAttribute::DB1_NAME) . ' --skip-column-names');
			if(count($output) && $output[0])
				self::initField(AppConfigAttribute::MONITOR_PARTNER_SECRET, $output[0]);
			else
				self::initField(AppConfigAttribute::MONITOR_PARTNER_SECRET, self::generateSecret());
		}

		self::initField(AppConfigAttribute::VERIFY_INSTALLATION, true);
		self::initField(AppConfigAttribute::DEPLOY_KMC, true);

		OsUtils::writeConfigToFile(self::$config, $inputFilePath);

		$scheulderTemplate = self::getCurrentMachineConfig(AppConfigAttribute::BATCH_SCHEDULER_TEMPLATE);
		if(!$scheulderTemplate)
			$scheulderTemplate = 'mainTemplate';

		$scheulderId = self::getCurrentMachineConfig(AppConfigAttribute::BATCH_SCHEDULER_ID);
		if(!$scheulderId)
			$scheulderId = 1;

		self::set(AppConfigAttribute::BATCH_SCHEDULER_TEMPLATE, $scheulderTemplate);
		self::set(AppConfigAttribute::BATCH_SCHEDULER_ID, $scheulderId);
		self::set(AppConfigAttribute::INSTALLED_HOSNAME, self::getHostname());
		self::set(AppConfigAttribute::APP_REMOTE_ADDR_HEADER_SALT, uniqid());
		
		
		self::set(AppConfigAttribute::API_MACHINES_NAMES, implode(',', self::getMachinesByComponent('api')));
		self::set(AppConfigAttribute::BATCH_MACHINES_NAMES, implode(',', self::getMachinesByComponent('batch')));
		self::set(AppConfigAttribute::ADMIN_MACHINES_NAMES, implode(',', self::getMachinesByComponent('admin')));
		self::set(AppConfigAttribute::VAR_MACHINES_NAMES, implode(',', self::getMachinesByComponent('var')));
		self::set(AppConfigAttribute::DB_MACHINES_NAMES, implode(',', self::getMachinesByComponent('db')));
		self::set(AppConfigAttribute::SPHINX_MACHINES_NAMES, implode(',', self::getMachinesByComponent('sphinx')));
		self::set(AppConfigAttribute::POPULATE_MACHINES_NAMES, implode(',', self::getMachinesByComponent('populate')));
		self::set(AppConfigAttribute::DWH_MACHINES_NAMES, implode(',', self::getMachinesByComponent('dwh')));
		self::set(AppConfigAttribute::RED5_MACHINES_NAMES, implode(',', self::getMachinesByComponent('red5')));
		
		self::set(AppConfigAttribute::MACHINE_NAME, self::getMachinesNames());
	}

	protected static function getMachinesNames()
	{
		if(!AppConfig::get(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT))
			return array(AppConfig::get(AppConfigAttribute::KALTURA_VIRTUAL_HOST_NAME));
			
		$machines = array();
		foreach(self::$config as $name => $value)
		{
			if(is_array($value) && isset($value['components']))
				$machines[] = $name;
		}
		return $machines;
	}

	protected static function getMachinesByComponent($component)
	{
		if(!AppConfig::get(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT))
			return array(AppConfig::get(AppConfigAttribute::KALTURA_VIRTUAL_HOST_NAME));
			
		$machines = array();
		foreach(self::$config as $name => $value)
		{
			if(is_array($value) && isset($value['components']))
			{
				$components = explode(',', $value['components']);
				if(in_array($component, $components))
					$machines[] = $name;
			}
		}
		return $machines;
	}

	protected static function configureMultipleServers()
	{
		// if already defined or if no need to define
		if (isset(self::$config[AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT]) || !AppConfig::getTrueFalse(AppConfigAttribute::MULTIPLE_SERVER_ENVIRONMENT, "Would you like to configure multiple servers?", 'n'))
			return false;

		$numbers = array(
			'first',
			'second',
			'third',
			'fourth',
			'fifth',
			'sixth',
			'seventh',
			'eighth',
			'ninth',
			'tenth',
		);

		$availableComponents = array(
			'api' => 'API Server',
			'ssl' => 'Secured web Server (ssl)',
			'apps' => 'Applications (html5 player, clipup, hosted pages)',
			'admin' => 'Administration Console',
			'var' => 'Multi-Account Console',
			'db' => 'Database Server (mySql)',
			'sphinx' => 'Indexing Server (sphinx)',
			'populate' => 'Indexing synchronizer (sphinx populate)',
			'dwh' => 'Data Warehouse',
			'batch' => 'Batch Server',
			'red5' => 'Media Server (red5)',
			'monitor' => 'Monitor (nagios)',
			'cleanup' => 'Old content cleanup',
		);

		$sphinxPopulateAvailableServers = array(
			1 => 'Primary sphinx server',
			2 => 'Secondary sphinx server',
		);

		$definedComponents = array();

		$serversCount = 0;
		do
		{
			$currentAvailableComponents = $availableComponents;
			if($serversCount)
			{
				foreach($currentAvailableComponents as $component => $title)
				{
					if(!isset($definedComponents[$component]))
						$currentAvailableComponents[$component] = $title . ' - none defined yet.';
				}
			}
			Logger::logMessage(Logger::LEVEL_USER, '');
			$number = $numbers[$serversCount];
			$serversCount++;
			$message = "Please enter the host name of your $number server";
			$hostname = self::getInput(null, $message, "Host name is invalid, please enter again", InputValidator::createHostValidator());
			$hostConfig = array();

			Logger::logMessage(Logger::LEVEL_USER, "Available components:");
			$componentsNumbers = array();
			$index = 1;
			foreach($currentAvailableComponents as $component => $title)
			{
				Logger::logMessage(Logger::LEVEL_USER, " - $index. $title");
				$componentsNumbers[$index] = $component;
				$index++;
			}
			$message = "Please select the components that should be installed on the machine ($hostname), please enter the components numbers seperated with commas";
			$message .= " (for example, to install API and Database server on $hostname, type '1,2', '1' for API server and '2' for Database server, avoid spaces).";

			$selectedComponentsNumbers = self::getInput(null, $message, "Invalid components selected, please enter again", InputValidator::createEnumValidator(array_keys($componentsNumbers), true));
			$selectedComponentsNumbers = explode(',', $selectedComponentsNumbers);
			$selectedComponents = array();
			foreach($selectedComponentsNumbers as $selectedComponentsNumber)
			{
				if(!isset($componentsNumbers[$selectedComponentsNumber]))
					continue;

				$component = $componentsNumbers[$selectedComponentsNumber];

				if($component == 'api')
				{
					if(!isset($definedComponents[$component]))
						$hostConfig[AppConfigAttribute::VERIFY_INSTALLATION] = true;
					if(!isset($definedComponents[$component]))
						$hostConfig[AppConfigAttribute::DEPLOY_KMC] = true;
				}

				if($component == 'db')
				{
					if(!isset($definedComponents[$component]))
						$hostConfig[AppConfigAttribute::DB1_CREATE_NEW_DB] = true;

					$dbAvailableServers = array();
					if(!isset(self::$config[AppConfigAttribute::DB1_HOST]))
						$dbAvailableServers[1] = 'Master (read and write)';

					if(!isset(self::$config[AppConfigAttribute::DB2_HOST]))
						$dbAvailableServers[2] = 'Primary Slave (read only)';

					if(!isset(self::$config[AppConfigAttribute::DB3_HOST]))
						$dbAvailableServers[3] = 'Secondary Slave (read only)';

					if(!isset(self::$config[AppConfigAttribute::SPHINX_DB_HOST]))
						$dbAvailableServers[4] = 'Sphinx log (read and write)';

					if(!count($dbAvailableServers))
					{
						Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, "All database servers are already defined, database won't be installed on $hostname.");
						continue;
					}

					$dbSelectedServers = array_keys($dbAvailableServers);
					if(count($dbAvailableServers) > 1)
					{
						Logger::logMessage(Logger::LEVEL_USER, "Available database connections:");
						foreach($dbAvailableServers as $index => $title)
							Logger::logMessage(Logger::LEVEL_USER, " - $index. $title");

						$message = "Please select the database connections that will be installed on $hostname database server, please enter the connections numbers seperated with commas";
						$message .= " (for example, to define $hostname as master and primary slave, type '1,2', '1' for master connection and '2' for primary slave, avoid spaces, leave empty for all connections).";

						$dbSelectedServersInput = self::getInput(null, $message, 'Invalid database connections selected, please enter again', InputValidator::createEnumValidator(array_keys($dbAvailableServers), true, true), implode(',', $dbSelectedServers));
						if($dbSelectedServersInput)
							$dbSelectedServers = explode(',', $dbSelectedServersInput);
					}

					foreach($dbSelectedServers as $dbSelectedServer)
					{

						if($dbSelectedServer == 4)
						{
							self::set(AppConfigAttribute::SPHINX_DB_HOST, $hostname);
							self::getInput(AppConfigAttribute::SPHINX_DB_PORT, $dbAvailableServers[$dbSelectedServer] . " database port (leave empty for '3306')", "Must be a valid port (1-65535), please enter again (leave empty for '3306')", InputValidator::createRangeValidator(1, 65535), '3306');
						}
						else
						{
							self::set("DB{$dbSelectedServer}_HOST", $hostname);
							self::getInput("DB{$dbSelectedServer}_PORT", $dbAvailableServers[$dbSelectedServer] . " database port (leave empty for '3306')", "Must be a valid port (1-65535), please enter again (leave empty for '3306')", InputValidator::createRangeValidator(1, 65535), '3306');
						}
					}
				}

				if($component == 'sphinx')
				{
					$sphinxAvailableServers = array();
					if(!isset(self::$config[AppConfigAttribute::SPHINX_SERVER1]))
						$sphinxAvailableServers[1] = 'Primary sphinx server';

					if(!isset(self::$config[AppConfigAttribute::SPHINX_SERVER2]))
						$sphinxAvailableServers[2] = 'Secondary sphinx server';

					if(!count($sphinxAvailableServers))
					{
						Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, "All sphinx servers are already defined, sphinx won't be installed on $hostname.");
						continue;
					}

					$sphinxSelectedServers = array_keys($sphinxAvailableServers);
					if(count($sphinxAvailableServers) > 1)
					{
						Logger::logMessage(Logger::LEVEL_USER, "Available sphinx connections:");
						foreach($sphinxAvailableServers as $index => $title)
							Logger::logMessage(Logger::LEVEL_USER, " - $index. $title");

						$message = "Please select the sphinx connections that will be installed on $hostname sphinx server, ";
						$message .= "please enter the connections numbers seperated with commas ";
						$message .= "(leave empty for all connections).";

						$sphinxSelectedServersInput = self::getInput(null, $message, 'Invalid sphinx connections selected, please enter again', InputValidator::createEnumValidator(array_keys($sphinxAvailableServers), true, true), implode(',', $sphinxSelectedServers));
						if($sphinxSelectedServersInput)
							$sphinxSelectedServers = explode(',', $sphinxSelectedServersInput);
					}

					foreach($sphinxSelectedServers as $sphinxSelectedServer)
						self::set("SPHINX_SERVER{$sphinxSelectedServer}", $hostname);
				}

				if($component == 'populate')
				{
					if(count($sphinxPopulateAvailableServers) > 1)
					{
						Logger::logMessage(Logger::LEVEL_USER, "Available sphinx synchronizers:");
						foreach($sphinxPopulateAvailableServers as $index => $title)
							Logger::logMessage(Logger::LEVEL_USER, " - $index. $title");

						$message = "Please select the sphinx synchronizer that will be installed on $hostname sphinx synchronizer server.";
						$sphinxPopulateAvailableServer = self::getInput(null, $message, 'Invalid sphinx synchronizer selected, please enter again', InputValidator::createEnumValidator(array_keys($sphinxPopulateAvailableServers)));
						$hostConfig[AppConfigAttribute::SPHINX_SERVER_INDEX] = $sphinxPopulateAvailableServer;
						unset($sphinxPopulateAvailableServers[$sphinxPopulateAvailableServer]);
					}
					elseif(count($sphinxPopulateAvailableServers) == 1)
					{
						$hostConfig[AppConfigAttribute::SPHINX_SERVER_INDEX] = reset(array_keys($sphinxPopulateAvailableServers));
					}
					else
					{
						Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, "All sphinx synchronizers are already defined, sphinx synchronizer won't be installed on $hostname.");
					}
				}

				if($component == 'dwh')
				{
					if(isset($definedComponents[$component]))
					{
						Logger::logColorMessage(Logger::COLOR_LIGHT_RED, Logger::LEVEL_USER, "Data warehouse server is already defined and won't be installed on $hostname.");
						continue;
					}
					
					$hostConfig[AppConfigAttribute::DB1_CREATE_NEW_DWH] = true;
				}

				if($component == 'ssl')
				{
					self::set(AppConfigAttribute::ENVIRONMENT_PROTOCOL, 'https');
					self::initField(AppConfigAttribute::KALTURA_VIRTUAL_HOST_PORT, 443);
				}

				if($component == 'batch')
				{
					$hostConfig[AppConfigAttribute::BATCH_SCHEDULER_ID] = isset($definedComponents[$component]) ? ($definedComponents[$component] + 1) : 1;
					$hostConfig[AppConfigAttribute::BATCH_SCHEDULER_TEMPLATE] = isset($definedComponents[$component]) ? 'template' : 'mainTemplate';
				}

				$selectedComponents[] = $component;
				if(isset($definedComponents[$component]))
					$definedComponents[$component]++;
				else
					$definedComponents[$component] = 1;
			}

			$hostConfig['components'] = implode(',', $selectedComponents);
			self::$config[$hostname] = $hostConfig;

			$notDefined = array();
			foreach($availableComponents as $component => $title)
			{
				if(!isset($definedComponents[$component]))
					$notDefined[] = strtolower(preg_replace('/ \(.+\)/', '', $title));
			}

			$default = 'n';
			$message = 'Would you like to configure another server';
			if(count($notDefined))
			{
				$default = 'y';
				$message .= ' (the followed components are not defined yet: ' . implode(', ', $notDefined) . ')';
			}

			$message .= '?';
		}
		while(AppConfig::getTrueFalse(null, $message, $default));

		return true;
	}
	
	public static function getInstallationConfiguration()
	{
		if(OsUtils::isWindows())
			return parse_ini_file(__DIR__ . '/installation.windows.ini', true);
			
		return parse_ini_file(__DIR__ . '/installation.ini', true);
	}

	public static function setCurrentMachineComponents(array $components = null)
	{
		if(!$components)
		{
			$components = array('*');
			
			$config = self::getCurrentMachineConfig();
			if($config && isset($config['components']))
				$components = explode(',', $config['components']);
		}
		self::$components = $components;

		if(in_array('*', self::$components) || in_array('api', self::$components) || in_array('apps', self::$components) || in_array('var', self::$components) || in_array('admin', self::$components))
		{
			if(self::get(AppConfigAttribute::ENVIRONMENT_PROTOCOL) == 'https' && !in_array('ssl', self::$components))
				self::$components[] = 'ssl';
		}
		
		if(OsUtils::isWindows() && in_array('dwh', self::$components))
			unset(self::$components[array_search('dwh', self::$components)]);

		Logger::logMessage(Logger::LEVEL_INFO, "Selected components: " . implode(', ', self::$components));
	}

	public static function componentDefined($component)
	{
		$components = self::getCurrentMachineComponents();
		return in_array($component, $components);
	}
	
	public static function getCurrentMachineComponents()
	{
		if(!self::$components)
			self::setCurrentMachineComponents();
		
		return self::$components;
	}

	public static function getCurrentMachineConfig($field = null)
	{
		$hostname = self::getHostname();
		if(isset(self::$config[$hostname]))
		{
			if(!$field)
				return self::$config[$hostname];

			if(isset(self::$config[$hostname][$field]))
				return self::$config[$hostname][$field];
		}

		return null;
	}

	public static function getServerConfig($field, $section = null)
	{
		if(!self::$packageDir)
			return null;

		if(!self::$kConf)
		{
			if(!file_exists(self::$packageDir . '/app/configurations/base.ini'))
				return null;

			self::$kConf = parse_ini_file(self::$packageDir . '/app/configurations/base.ini', true);
		}

		$config = self::$kConf;
		if($section)
		{
			if(!isset(self::$kConf[$section]))
				return null;

			$config = self::$kConf[$section];
		}

		if(isset($config[$field]))
			return $config[$field];

		return null;
	}

	private static function getHostname()
	{
		if(self::$hostname)
			return self::$hostname;

		if(isset($_SERVER['HOSTNAME']))
			self::$hostname = $_SERVER['HOSTNAME'];

		if(is_null(self::$hostname))
			self::$hostname = gethostname();

		if(is_null(self::$hostname))
			self::$hostname = $_SERVER['SERVER_NAME'];

		if(is_null(self::$hostname))
			self::$hostname = 'localhost';

		Logger::logMessage(Logger::LEVEL_INFO, "Installing host [" . self::$hostname . "]");
		return self::$hostname;
	}

	/**
	 * Sets the application value for the given key, only if it's not set yet
	 * @param string $key
	 * @param string $value
	 * @throws Exception
	 */
	private static function initField($key, $value)
	{
		if(! defined("AppConfigAttribute::$key"))
			throw new Exception("Configuration key [$key] not defined");

		if(!isset(self::$config[$key]))
			self::$config[$key] = $value;
	}

	/**
	 * Gets the application value set for the given key
	 * @param string $key
	 * @throws Exception
	 * @return string
	 */
	public static function get($key)
	{
		if(! defined("AppConfigAttribute::$key"))
			throw new Exception("Configuration key [$key] not defined");

		if(!isset(self::$config[$key]))
			return null;

		return self::$config[$key];
	}

	/**
	 * Sets the application value for the given key
	 * @param string $key
	 * @param string $value
	 * @throws Exception
	 */
	public static function set($key, $value)
	{
		if(! defined("AppConfigAttribute::$key"))
			throw new Exception("Configuration key [$key] not defined");

		self::$config[$key] = $value;
	}

	// replaces all tokens in the given string with the configuration values and returns the new string
	public static function replaceTokensInString($string, array $overwriteValues = null)
	{
		$config = self::$config;
		if($overwriteValues)
		{
			foreach($overwriteValues as $key => $var)
				$config[$key] = $var;
		}
			
		foreach($config as $key => $var)
		{
			if(is_array($var))
				continue;

			$key = TOKEN_CHAR . $key . TOKEN_CHAR;
			$string = str_replace($key, $var, $string);
		}
		return $string;
	}

	// replaces all the tokens in the given file with the configuration values and returns true/false upon success/failure
	// will override the file if it is not a template file
	// if it is a template file it will save it to a non template file and then override it
	public static function replaceTokensInFile($file, array $overwriteValues = null)
	{
		if($overwriteValues)
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Replacing configuration tokens in file [$file], overwrite values [" . print_r($overwriteValues, true) . "]");
		}
		else
		{
			Logger::logMessage(Logger::LEVEL_INFO, "Replacing configuration tokens in file [$file]");
		}
		
		$newfile = self::copyTemplateFileIfNeeded($file);
		if(is_array($newfile))
		{
			foreach($newfile as $filePath => $values)
			{
				if(!self::replaceTokensInFile($filePath, $values))
					return false;
			}
			return true;
		}
		$data = @file_get_contents($newfile);
		
		if(! $data)
		{
			Logger::logMessage(Logger::LEVEL_ERROR, "Cannot replace token in file $newfile");
			return false;
		}
		else
		{
			$data = self::replaceTokensInString($data, $overwriteValues);
			
			if(preg_match('/\.ini$/', $newfile))
			{
				$count = null;
				$tmpData = str_replace("\r", '', $data, $count);
				if($tmpData && $count)
				{
					Logger::logMessage(Logger::LEVEL_INFO, "Removed dos carriage return from ini file [$newfile]");
					$data = $tmpData;
				}
				
				$tmpData = preg_replace('/^([^=\n]+)=([ \t]*)([^"\n]*[=&][^"\n]*)$/m', '$1=$2"$3"', $data, -1, $count);
				if($tmpData && $count)
				{
					Logger::logMessage(Logger::LEVEL_INFO, "Wrapped complex string values with qoutes in ini file [$newfile]");
					$data = $tmpData;
				}
			}
			
			if(! file_put_contents($newfile, $data))
			{
				Logger::logMessage(Logger::LEVEL_ERROR, "Cannot replace token in file, cannot write to file $newfile");
				return false;
			}
			else
			{
				Logger::logMessage(Logger::LEVEL_INFO, "Replaced tokens in file $newfile");
			}
		}
		return true;
	}

	public static function getFilePath()
	{
		if(! self::$tempFilePath)
		{
			self::$tempFilePath = tempnam(sys_get_temp_dir(), 'kaltura.installer.');
			OsUtils::writeConfigToFile(self::$config, self::$tempFilePath);
		}

		return self::$tempFilePath;
	}

	/**
	 * Generates a secret for Kaltura and returns it
	 * @return string
	 */
	private static function generateSecret()
	{
		Logger::logMessage(Logger::LEVEL_INFO, "Generating secret");
		$secret = md5(self::makeRandomString(5, 10, true, false, true));
		return $secret;
	}

	// checks if the given file is a template file and if so copies it to a non template file
	// returns the non template file if it was copied or the original file if it was not copied
	private static function copyTemplateFileIfNeeded($file)
	{
		$returnFile = $file;
		// Replacement in a template file, first copy to a non .template file
		if(strpos($file, TEMPLATE_FILE) !== false)
		{
			$returnFile = str_replace(TEMPLATE_FILE, "", $file);
			$matches = null;
			if(preg_match('/@([A-Z0-9_]+)@/', $returnFile, $matches) && defined('AppConfigAttribute::' . $matches[1]))
			{
				$token = $matches[1];
				$value = self::get($token);
				
				if(is_array($value))
				{
					$returnFiles = array();
					foreach($value as $valueOption)
					{
						$filePath = str_replace("@$token@", $valueOption, $returnFile);
						
						OsUtils::fullCopy($file, $filePath);
						Logger::logMessage(Logger::LEVEL_INFO, "$file token file translated and copied to $filePath");
						
						$returnFiles[$filePath] = array($token => $valueOption);
					}
					return $returnFiles;
				}
				else
				{
					$returnFile = str_replace("@$token@", $value, $returnFile);
				}
			}
			
			OsUtils::fullCopy($file, $returnFile);
			Logger::logMessage(Logger::LEVEL_INFO, "$file token file copied to $returnFile");
		}
		return $returnFile;
	}

	/**
	 * Creates a random key used to generate a secret
	 * @param int $minlength
	 * @param int $maxlength
	 * @param boolean $useupper
	 * @param boolean $usespecial
	 * @param boolean $usenumbers
	 * @return string
	 */
	private static function makeRandomString($minlength, $maxlength, $useupper, $usespecial, $usenumbers)
	{
		$charset = "abcdefghijklmnopqrstuvwxyz";
		if($useupper)
			$charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if($usenumbers)
			$charset .= "0123456789";
		if($usespecial)
			$charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if($minlength > $maxlength)
			$length = mt_rand($maxlength, $minlength);
		else
			$length = mt_rand($minlength, $maxlength);
		$key = "";
		for($i = 0; $i < $length; $i ++)
			$key .= $charset[(mt_rand(0, (strlen($charset) - 1)))];
		return $key;
	}

	/**
	 * Extract the host name out of URL
	 * @param string $url
	 * @return string
	 */
	private static function extractHostName($url)
	{
		$ret = parse_url($url, PHP_URL_HOST);
		if($ret)
			return $ret;

		return $url;
	}

	/**
	 * gets input from the user and returns it
	 * if $key was already loaded from config it will be taked from there and user will not have to insert
	 *
	 * @param string $key
	 * @param string $request_text text to show the user
	 * @param string $not_valid_text text to show the user if the input is invalid (according to the validator)
	 * @param InputValidator $validator the input validator to user (default is null, no validation)
	 * @param string $default the default value (default's default is '' :))
	 * @param bool $hideValue do not show the value on the screen, in case it's password for example
	 * @return string
	 */
	public static function getInput($key, $request_text, $not_valid_text = null, InputValidator $validator = null, $default = '', $hideValue = false)
	{
		if($key && isset(self::$config[$key]))
			return self::$config[$key];

		if(isset($validator) && ! empty($default))
			$validator->emptyIsValid = true;

		Logger::logMessage(Logger::LEVEL_USER, $request_text);

		$inputOk = false;
		while(! $inputOk)
		{
			echo '> ';

			if($hideValue)
			{
				if(OsUtils::getOsName() == OsUtils::LINUX_OS)
					system('stty -echo');

				$input = trim(fgets(STDIN));

				if(OsUtils::getOsName() == OsUtils::LINUX_OS)
				{
					system('stty echo');
					echo PHP_EOL;
				}

				Logger::logMessage(Logger::LEVEL_INFO, "User input accepted");
			}
			else
			{
				$input = trim(fgets(STDIN));
				Logger::logMessage(Logger::LEVEL_INFO, "User input is $input");
			}

			try
			{
				if($validator && ! $validator->validateInput($input))
				{
					Logger::logMessage(Logger::LEVEL_USER, $not_valid_text);
				}
				else
				{
	
					if(!$input && !is_null($default))
					{
						$input = $default;
						if($hideValue)
							Logger::logMessage(Logger::LEVEL_USER, "Using default value");
						else
							Logger::logMessage(Logger::LEVEL_USER, "Using default value: $default");
					}
					echo PHP_EOL;
					$inputOk = true;
				}
			}
			catch(InputValidatorException $e)
			{
				Logger::logMessage(Logger::LEVEL_USER, $e->getMessage());
			}
		}

		if(isset($key))
			self::$config[$key] = $input;

		return $input;
	}

	/**
	 * Get a y/n input from the user
	 *
	 * @param string $key if already loaded from config it will be taken from there and user will not have to insert
	 * @param string $request_text text to show the user
	 * @param string $default the default value (show be 'y'/'n')
	 * @return boolean
	 */
	public static function getTrueFalse($key, $request_text, $default)
	{
		if($key && isset(self::$config[$key]))
		{
			return self::$config[$key];
		}

		$default = strtolower(trim($default));
		if($default == 'y' || $default == 'yes')
		{
			$request_text .= ' (Y/n)';
		}
		else
		{
			$request_text .= ' (y/N)';
		}

		$validator = InputValidator::createYesNoValidator();
		$input = self::getInput(null, $request_text, "Input must be yes (y) or no (n)", $validator, $default);
		$input = strtolower(trim($input));
		$input = ($input == 'y' || $input == 'yes');

		if(isset($key))
			self::$config[$key] = $input;

		return $input;
	}
}
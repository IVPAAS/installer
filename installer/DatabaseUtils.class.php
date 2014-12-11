<?php

/*
* This class is a static class with database utility functions
*/
class DatabaseUtils
{
	/**
	 * Connect to mySQL database
	 * @param string $db_name database name
	 * @return true on success, false otherwise
	 */
	public static function connect($host, $port, $db_name = null)
	{
		// set mysqli to connect via tcp
		$password = trim(AppConfig::get(AppConfigAttribute::DB_ROOT_PASS));
		if (!$password) {
			$password = null;
		}
		$link = @mysqli_init();
		/* @var $link mysqli */
		$result = @mysqli_real_connect($link, $host, AppConfig::get(AppConfigAttribute::DB_ROOT_USER), $password, $db_name, $port);
		if (!$result) {
			Logger::logMessage(Logger::LEVEL_ERROR, sprintf("Cannot connect to db: $host, %s, %s", AppConfig::get(AppConfigAttribute::DB_ROOT_USER), $link->error));
			return false;
		}
		return $link;
	}

	/**
	 * Execute a mySQL query or multi queries
	 * @param string $query mySQL query, or multiple queries seperated by a ';'
	 * @param string $db_name database name
	 * @param mysqli $link mysqli link
	 * @return true on success, false otherwise
	 */
	public static function executeQuery($query, $host, $port, $db_name = null, $link = null)
	{
		// connect if not yet connected
		if (!$link)
			$link = self::connect($host, $port, $db_name);
		if (!$link)
			return false;

		// use desired database
		else if ($db_name && !mysqli_select_db($link, $db_name)) {
			Logger::logMessage(Logger::LEVEL_ERROR, "Cannot execute query: could not find the db: $db_name");
			return false;
		}

		// execute all queries
		if (!mysqli_multi_query($link, $query) || $link->error != '') {
			Logger::logMessage(Logger::LEVEL_ERROR, "Cannot execute query: error with query: $query, error: ".$link->error);
			return false;
		}

		// flush
		while (mysqli_more_results($link) && mysqli_next_result($link)) {
			$discard = mysqli_store_result($link);
		}
		$link->commit();

		return true;
	}

	/**
	 * Create a new mySQL database
	 * @param string $db_name database name
	 * @return true on success, false otherwise
	 */
	public static function createDb($host, $port, $db_name)
	{
		Logger::logMessage(Logger::LEVEL_INFO, "Creating database $db_name");
		$create_db_query = "CREATE DATABASE $db_name;";
		return self::executeQuery($create_db_query, $host, $port);
	}

	/**
	 * Drop a mySQL database
	 * @param string $db_name database name
	 * @return true on success, false otherwise
	 */
	public static function dropDb($host, $port, $db_name)
	{
		Logger::logMessage(Logger::LEVEL_INFO, "Dropping database $db_name");
		$drop_db_query = "DROP DATABASE $db_name;";
		return self::executeQuery($drop_db_query, $host, $port);
	}

	/**
	 * Check if a mySQL database exists
	 * @param string $db_name database name
	 * @return true/false according to existence
	 */
	public static function dbExists($host, $port, $db_name)
	{
		$link = self::connect($host, $port);
		if (!$link)
		{
			Logger::logMessage(Logger::LEVEL_ERROR, "Could not database $db_name: could not connect to host");
			return -1;
		}
		return mysqli_select_db($link, $db_name);
	}

	/**
	 * Execute mySQL queries from a given sql file
	 * @param string $file sql file
	 * @param string $db_name database name
	 * @return true on success, false otherwise
	 */
	public static function runScript($file, $db_name) {
		if (!is_file($file)) {
			Logger::logMessage(Logger::LEVEL_ERROR, "Could not run script: script not found $file");
			return false;
		}

		if (!AppConfig::get(AppConfigAttribute::DB_ROOT_PASS)) {
			$cmd = sprintf(AppConfig::get('"%s" -h%s -u%s -P%s %s < %s', AppConfigAttribute::MYSQL_BIN), AppConfig::get(AppConfigAttribute::DB1_HOST), AppConfig::get(AppConfigAttribute::DB_ROOT_USER), AppConfig::get(AppConfigAttribute::DB1_PORT), $db_name, $file);
		} else {
			$cmd = sprintf(AppConfig::get('"" -h%s -u%s -p%s -P%s %s < %s', AppConfigAttribute::MYSQL_BIN), AppConfig::get(AppConfigAttribute::DB1_HOST), AppConfig::get(AppConfigAttribute::DB_ROOT_USER), AppConfig::get(AppConfigAttribute::DB_ROOT_PASS), AppConfig::get(AppConfigAttribute::DB1_PORT), $db_name, $file);
		}
		Logger::logMessage(Logger::LEVEL_INFO, "Executing $cmd");
		@exec($cmd . ' 2>/dev/null', $output, $return_var);
		if ($return_var === 0) {
			return true;
		} else {
			Logger::logMessage(Logger::LEVEL_ERROR, "Executing command failed: ".implode("\n",$output));
			return false;
		}
	}
}

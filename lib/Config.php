<?php

/**
* The singleton factory for all of our libraries which require secrets.
* @author Matt Holden
*
*/
class Config extends Secrets {

	private static $database = null;
	private static $logger = null;
	
	/**
	 * Returns the library instance for the database, creating it if it doesn't exist yet
	 * @return Database the Database object
	 */
	public static function getDatabase() {

		if (self::$database == null) {
			self::$database = new Database(self::$DATABASE);
		}
		return self::$database;
	}

	/**
	 * Returns the library instance for the logger, creating it if it doesn't exist yet
	 * @return Logger the Logger object
	 */
	public static function getLog() {
		if (self::$logger == null) {
			self::$logger = new Logger(self::$LOGGER);
		}
		return self::$logger;
	}

}
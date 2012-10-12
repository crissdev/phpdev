<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */

/**
 *
 */
class DatabaseFactory
{
	private static $_instances = array();

	private function __construct() { }


	/**
	 *
	 * @param string $name The name of the database for which to retrieve a Database object.
	 * @throws ArgumentException
	 * @throws NotSupportedException
	 * @return Database
	 */
	public static function getDatabase($name)
	{
		SecUtil::checkStringArgument($name, 'name', 128, false, false, false);

		$connectionInfo = AppSettings::getDbConnectionSettings($name);

		if ($connectionInfo === null) {
			throw new ArgumentException("Connection information is not available for: '$name'.", 'name');
		}

		if (!array_key_exists($name, self::$_instances))
		{
			switch (strtolower($connectionInfo['provider']))
			{
				case 'postgres':
					$connectionString = self::getPostgresConnectionString($connectionInfo);
					self::$_instances[$name] = new PgDatabase($connectionString);

					if (array_key_exists('logger', $connectionInfo))
					{
						try
						{
							$logger = LoggerManager::getLogger($connectionInfo['logger']);
							self::$_instances[$name]->setLogger($logger);
						}
						catch (Exception $e)
						{
							App::logEvent(LOGGER_LEVEL_WARN, __METHOD__ . ' - An error occurred while setting the logger for database instance.', null, array('exception' => $e, 'db' => $connectionInfo['host']));
						}
					}
					break;
				default:
					throw new NotSupportedException('The selected database driver is not supported.');
					break;
			}
		}
		return self::$_instances[$name];
	}


	private static function getPostgresConnectionString($connectionInfo)
	{
		$validKeys = array('host', 'port', 'dbname', 'user', 'password');
		$keyValuePairs = array();

		foreach ($validKeys as $key) {
			if (array_key_exists($key, $connectionInfo))
				$keyValuePairs[] = "{$key} = {$connectionInfo[$key]}";
		}

		$connectionString = implode(' ', $keyValuePairs);
		return $connectionString;
	}
}

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
class LoggerManager
{
	private static $_loggers = array();

	private function __construct()
	{
		// This class is static
	}

	public static function getLogger($name)
	{
		SecUtil::checkStringArgument($name, 'name', -1, true, true, false);

		if (array_key_exists($name, self::$_loggers))
		{
			$value = self::$_loggers[$name];

			if (is_array($value))
			{
				$c = new ReflectionClass($value['type']);
				$instance = $c->newInstanceArgs($value['args']);
				self::$_loggers[$name] = $instance;
			}
			return self::$_loggers[$name];
		}
		return null;
	}

	public static function registerInstance($name, $instance)
	{
		SecUtil::checkStringArgument($name, 'name', 128, false, false, false);

		if ($instance === null)
			throw new ArgumentNullException(null, 'instance');

		if (!is_a($instance, 'Logger'))
			throw new ArgumentException('Instance must derive from Logger.', 'instance');

		self::$_loggers[$name] = $instance;
	}

	public static function registerLazyInstance($name, $type, array $constructorParameters = null)
	{
		SecUtil::checkStringArgument($name, 'name', 128, false, false, false);
		SecUtil::checkStringArgument($type, 'type', 128, false, false, false);
		SecUtil::checkArrayArgument($constructorParameters, 'constructorParameters', false, true);

		self::$_loggers[$name] = array('type' => $type, 'args' => $constructorParameters);
	}
}
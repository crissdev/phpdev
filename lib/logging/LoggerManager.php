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
	private static $_typeMappings = array();

	private function __construct()
	{
		// This class is static
	}

	public static function getLogger($name)
	{
		if (array_key_exists($name, self::$_typeMappings))
		{
			$value = self::$_typeMappings[$name];

			if (is_array($value))
			{
				$c = new ReflectionClass($value['type']);
				$instance = $c->newInstanceArgs($value['args']);
				self::$_typeMappings[$name] = $instance;
			}
			return self::$_typeMappings[$name];
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

		self::$_typeMappings[$name] = $instance;
	}

	public static function registerLazyInstance($name, $type, array $ctorParameters = null)
	{
		SecUtil::checkStringArgument($name, 'name', 128, false, false, false);
		SecUtil::checkStringArgument($type, 'type', 128, false, false, false);
		SecUtil::checkArrayArgument($ctorParameters, 'ctorParameters', false, true);

		self::$_typeMappings[$name] = array('type' => $type, 'args' => $ctorParameters);
	}

	private static function getKey($type, $name)
	{
		if (strlen($name) === 0)
			return $type;

		return $type . '_' . $name;
	}

}
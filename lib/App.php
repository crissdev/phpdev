<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */
class App
{
	/**
	 *
	 * @var Logger
	 */
	private static $_logger;
	private static $_includePaths = array();

	/**
	 * Initializes the current application.
	 * Sets up include path, error reporting and logging.
	 */
	public static function init()
	{
		try
        {
			// Setup our include path
			$basePath = dirname(__FILE__);
			foreach (array('base', 'db', 'logging', 'util', 'sec', 'auth', 'rpc') as $path)
            {
				$fullPath = $basePath . DIRECTORY_SEPARATOR . $path;

				if (file_exists($fullPath))
					self::$_includePaths[] = $fullPath;
			}
			self::$_includePaths[] = $basePath;

			$result = spl_autoload_register(array('App', 'autoloader'), false, false);

			if (!$result) {
				throw new Exception('The autoload function could not be registered.');
			}

			if (function_exists('App_onInit')) {
				App_onInit();
			}

			// Set-up custom php error log
			//
			self::setCustomPhpErrorLog();

			// Set-up date time settings
			//
			self::setDateTimeSettings();

			// Set-up error reporting
			//
			self::setupErrorReporting();

			if (function_exists('App_onInitComplete')) {
				App_onInitComplete();
			}
		}
		catch (Exception $e) {
			throw new Exception('Application failed to initialize.', -1, $e);
		}
	}


	public static function addIncludePath(array $paths, $basePath = null)
	{
		SecUtil::checkStringArgument($basePath, 'basePath');
		SecUtil::checkArrayArgument($paths, 'paths', false, false);

		if ($basePath !== null && strlen($basePath) > 0)
		{
			if (substr($basePath, -1, 1) !== '/' && substr($basePath, -1, 1) !== '\\') {
				$basePath .= DIRECTORY_SEPARATOR;
			}
			foreach($paths as $path) {
				SecUtil::checkStringArgument($path, 'path', MAX_PATH_LENGTH, false, false, true);
				if (substr($path, -1, 1) === '/' || substr($path, -1, 1) === '\\') {
					$path = substr($path, 1);
					SecUtil::checkStringArgument($path, 'path', MAX_PATH_LENGTH, false, false, true);
				}
				$incPath = $basePath . $path;

				if (!in_array($incPath, self::$_includePaths))
					self::$_includePaths[] = $incPath;
			}
		}
	}

	public static function getIncludePaths()
	{
		return self::$_includePaths;
	}

	private function setCustomPhpErrorLog()
	{
		$customPhpErrorLog = AppSettings::getPhpErrorLogFileName();

		if ($customPhpErrorLog !== null && strlen($customPhpErrorLog) > 0)
			ini_set('error_log' , $customPhpErrorLog);
	}

	private static function setupErrorReporting()
	{
		if (AppSettings::isDebuggingEnabled()) {
			error_reporting(E_ALL);
			ini_set('display_errors', 'on');
		}
		else {
			error_reporting(0);
			ini_set('display_errors', 'off');
		}
	}

	private static function setDateTimeSettings()
	{
		// set-up date settings
		date_default_timezone_set(AppSettings::getDefaultTimezone());
	}

	/**
	 *
	 * @return Logger
	 */
	public static function getLogger()
	{
		return self::$_logger;
	}

	public static function setLogger(Logger $logger = null)
	{
		self::$_logger = $logger;
	}

	/**
	 * Writes a log entry in the associated log file.
	 *
	 * @param int $level Any of the logging level supported constants.
	 * Valid values are LOGGER_LEVEL_OFF, LOGGER_LEVEL_ERROR, LOGGER_LEVEL_WARN, LOGGER_LEVEL_INFO, LOGGER_LEVEL_DEBUG and
	 * they are defined in Constants.php
	 * @param string $message The message to be written to log.
	 * @param array $args Any arguments to be used with sprintf (if message has format specifiers).
	 * @param mixed $data Any extra data associated with the log entry.
	 * @return int The number of bytes written by the logger, false, in case of an error.
	 */
	public static function logEvent($level, $message, array $args = null, $data = null)
	{
		try {
			if (self::$_logger != null) {
				return self::$_logger->logEvent($level, $message, $args, $data);
			}
		}
		catch (Exception $e) {
			// bail; we don't want to be interrupted if the logging system fails for some reason.
		}
		return 0;
	}


	public static function autoloader($className)
	{
		foreach (self::$_includePaths as $incPath)
		{
			if (file_exists($fullPath = $incPath . DIRECTORY_SEPARATOR . $className . '.php'))
			{
				require_once $fullPath;
				return true;
			}
		}
		return false;
	}
}


//--------------------------------------------------------------------------------------------
// Include Constants in order to have our components working
//
// Note: We don't use require_once just so you can receive an error if you include App.php
// more than once.
//
require 'base/Constants.php';
//--------------------------------------------------------------------------------------------


//--------------------------------------------------------------------------------------------
// This call initializes the application (include_path, error reporting, date time settings)
//--------------------------------------------------------------------------------------------
App::init();

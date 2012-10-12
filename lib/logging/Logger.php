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
abstract class Logger
{
	private $_level;
	private $_source;

	/**
	 * @param int $level
	 * @param string $source
	 */
	public function __construct($level = LOGGER_LEVEL_DEBUG, $source = '')
	{
		$this->setLoggingLevel($level);
		$this->setLoggingSource($source);
	}


	public function debug($message, $data = null)
	{
		return $this->logEvent(LOGGER_LEVEL_DEBUG, $message, null, $data);
	}

	public function debugFormat($format, array $args = null, $data = null)
	{
		return $this->logEvent(LOGGER_LEVEL_DEBUG, $format, $args, $data);
	}

	public function info($message, $data = null)
	{
		return $this->logEvent(LOGGER_LEVEL_INFO, $message, null, $data);
	}

	public function infoFormat($format, array $args = null, $data = null)
	{
		return $this->logEvent(LOGGER_LEVEL_INFO, $format, $args, $data);
	}

	public function warn($message, $data = null)
	{
		return $this->logEvent(LOGGER_LEVEL_WARN, $message, null, $data);
	}

	public function warnFormat($format, array $args = null, $data = null)
	{
		return $this->logEvent(LOGGER_LEVEL_WARN, $format, $args, $data);
	}

	public function error($message, $data = null)
	{
		return $this->logEvent(LOGGER_LEVEL_ERROR, $message, null, $data);
	}

	public function errorFormat($format, array $args = null, $data = null)
	{
		return $this->logEvent(LOGGER_LEVEL_ERROR, $format, $args, $data);
	}

	public abstract function logEvent($level, $message, array $args = null, $data = null);


	public final function isDebugEnabled()
	{
		return $this->_level >= LOGGER_LEVEL_DEBUG;
	}

	public final function isInfoEnabled()
	{
		return $this->_level >= LOGGER_LEVEL_INFO;
	}

	public final function isWarnEnabled()
	{
		return $this->_level >= LOGGER_LEVEL_WARN;
	}

	public final function isErrorEnabled()
	{
		return $this->_level >= LOGGER_LEVEL_ERROR;
	}


	public final function getLoggingLevel()
	{
		return $this->_level;
	}

	public final function setLoggingLevel($level)
	{
		SecUtil::checkIntegerArgument($level, 'level', false, LOGGER_LEVEL_OFF, LOGGER_LEVEL_DEBUG);
		$this->_level = $level;
	}

	public final function getLoggingSource()
	{
		return $this->_source;
	}

	public final function setLoggingSource($source)
	{
		SecUtil::checkStringArgument($source, 'source', 60, false, true, true);
		$this->_source = $source;
	}
}
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
class LoggerAggregate extends Logger
{
	private $_loggers = array();
	private $_logLevel = LOGGER_LEVEL_OFF;


	public function __construct()
	{
		parent::__construct(LOGGER_LEVEL_OFF, '');
	}

	public function addLogger(Logger $logger)
	{
		if ($logger->getLoggingLevel() > $this->_logLevel) {
			$this->_logLevel = $logger->getLoggingLevel();
		}
		$this->_loggers[] = $logger;
	}

	public function logEvent($level, $message, array $args = null, $data = null)
	{
		foreach ($this->_loggers as $logger) {
			$logger->logEvent($level, $message, $args, $data);
		}
	}
}
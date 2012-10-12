<?php

/**
 * Base class for all objects that support RPC calls.<br/>
 * <b>Note:</b> This class is not an absolute requirement for having RPC calls. The only benefits are integration with logging and authorization.
 */
abstract class RemoteObject implements ISupportLogging
{
	/**
	 * @var Logger An object used for logging.
	 */
	private $_logger;

	protected function __construct()
	{
		// Protected methods are not valid RPC calls but we add them to the disallow list just in case they might be made public in future versions
		Rpc::disallowRpcCall(get_class($this), array('getCurrentPrincipal', 'requireAuthenticatedUser', 'requireRole', 'requireAdministrator', 'logEvent'));

		// Disable getLogger/setLogger methods as well
		Rpc::disallowRpcCall(get_class($this), array('getLogger', 'setLogger'));
	}

	/**
	 *
	 * @return Principal
	 */
	protected function getCurrentPrincipal()
	{
		return Principal::getCurrent();
	}

	protected function requireAuthenticatedUser()
	{
		Authorization::requireAuthenticatedUser();
	}

	protected function requireRole($roleName, $builtinSuffice = false)
	{
		Authorization::requireRole($roleName, $builtinSuffice);
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
	protected function logEvent($level, $message, array $args = null, $data = null)
	{
		if ($this->_logger == null)
			return 0;
		return $this->_logger->logEvent($level, $message, $args, $data);
	}

	// ISupportErrorLogging implementation

	/**
	 *
	 * @return Logger
	 */
	public final function getLogger()
	{
		return $this->_logger;
	}

	public final function setLogger(Logger $logger = null)
	{
		$this->_logger = $logger;
	}
}
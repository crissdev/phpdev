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
abstract class Database implements ISupportLogging
{
	private $_logger;


	protected function __construct()
	{
	}


	public abstract function getInnerConnection();

	public abstract function connect();

	public abstract function disconnect();


	public abstract function beginTransaction();

	public abstract function commitTransaction();

	public abstract function rollbackTransaction();

	public abstract function inTransaction();

	public abstract function getTransactionStatus();


	public abstract function executeQuery($query, array $params = null);

	public abstract function executeScalar($query, array $params = null);

	public abstract function executeNonQuery($query, array $params = null);

	public abstract function loadRow($query, array $params = null, $assoc = true, &$rowCount = null);

	public abstract function loadArray($query, array $params = null, $assoc = true, &$rowCount = null);

	public abstract function loadScalarList($query, array $params = null, &$rowCount = null);

	public abstract function updateArray($query, array $data, $useTransaction);


	public abstract function parseDbValue($value, $dbType = DB_TYPE_STRING, $allowNull = true);

	public abstract function freeResult($result);


	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->_logger;
	}

	/**
	 * @param Logger $logger
	 */
	public function setLogger(Logger $logger = null)
	{
		$this->_logger = $logger;
	}

	protected function logEvent($level, $message, array $args = null, $data = null)
	{
		if ($this->_logger !== null) {
			try {
				$this->_logger->logEvent($level, $message, $args, $data);
			}
			catch (Exception $ex) {
				// Write a warning in PHP error log.
				trigger_error("Cannot write to log: ".$ex->getMessage(), E_USER_WARNING);
			}
		}
	}
}
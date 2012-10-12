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
class PgDatabase extends Database
{
	private $_connection;
	private $_connectionString;


	public function __construct($connectionString)
	{
		parent::__construct();

		SecUtil::checkStringArgument($connectionString, 'connectionString', -1, false, false, false);
		$this->_connectionString = $connectionString;
	}

	public function __destruct()
	{
		$this->logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Closing database connection.');

		$this->disconnect();
		unset($this->_connectionString);
	}

	public function getInnerConnection()
	{
		return $this->_connection;
	}

	public function connect()
	{
		// the maximum number of attempts to connect
		$attempts = 5;

		while ($attempts > 0) {
			if ($this->_connection === null) {
				$this->logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Opening a new connection to database server.');
				$this->_connection = pg_connect($this->_connectionString);
			}
			else {
				$status = pg_connection_status($this->_connection);
				if ($status === PGSQL_CONNECTION_BAD) {
					$this->_connection = null;
					continue;
				}
			}
			if ($this->_connection === false) {
				$this->logEvent(LOGGER_LEVEL_WARN, __METHOD__ . ' - Failed to open a new connection to database server. Attempts remained: %d', array($attempts));
				$this->_connection = null;
				$attempts--;
				usleep(3 * 100000);	// 300 milliseconds
				continue;
			}
			break;
		}
		if ($this->_connection === null) {
			$this->logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . ' - Failed to open a new connection to database server.');
			throw new DbConnectionFailedException();
		}
	}

	public function disconnect()
	{
		if ($this->_connection !== null && is_resource($this->_connection)) {
			$status = pg_connection_status($this->_connection);
			if ($status === PGSQL_CONNECTION_OK) {
				$this->logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Closing connection to database server. Database name: %s', array(pg_dbname($this->_connection)));
				pg_close($this->_connection);
			}
			$this->_connection = null;
		}
	}

	/**
	 * Check whether or not there's an active transaction for the current connection.
	 *
	 * @return bool true, a transaction is active, false, otherwise.
	 */
	public function inTransaction()
	{
		$status = $this->getTransactionStatus();
		return ($status != TRANSACTION_STATUS_UNKNOWN && $status != TRANSACTION_STATUS_IDLE);
	}

	/**
	 * Returns the current in-transaction status of the server.
	 *
	 * @return int Any of the following constants: <ul>
	 * <li>TRANSACTION_STATUS_IDLE - Connection is currently idle, not in a transaction.<li>
	 * <li>TRANSACTION_STATUS_ACTIVE - A command is in progress on the connection. A query has been sent via the connection and not yet completed.<li>
	 * <li>TRANSACTION_STATUS_INTRANS - The connection is idle, in a transaction block.<li>
	 * <li>TRANSACTION_STATUS_INERROR - The connection is idle, in a failed transaction block.</li>
	 * <li>TRANSACTION_STATUS_UNKNOWN - The connection is bad.</li></ul>
	 */
	public function getTransactionStatus()
	{
		if ($this->_connection === null) {
			return TRANSACTION_STATUS_IDLE;
		}
		$status = pg_transaction_status($this->_connection);

		switch ($status)
		{
			case PGSQL_TRANSACTION_IDLE:
				return TRANSACTION_STATUS_IDLE;
			case PGSQL_TRANSACTION_ACTIVE:
				return TRANSACTION_STATUS_ACTIVE;
			case PGSQL_TRANSACTION_INTRANS:
				return TRANSACTION_STATUS_INTRANS;
			case PGSQL_TRANSACTION_INERROR:
				return TRANSACTION_STATUS_INERROR;
			case PGSQL_TRANSACTION_UNKNOWN:
				return TRANSACTION_STATUS_UNKNOWN;
		}
		throw new NotSupportedException("The transaction status '$status' is not mapped.");
	}

	public function beginTransaction()
	{
		$status = $this->getTransactionStatus();

		if ($status == TRANSACTION_STATUS_UNKNOWN)
			throw new DbConnectionFailedException();

		if ($status == TRANSACTION_STATUS_INERROR)
			throw new DbQueryFailedException('The connection is currently in a failed transaction block. You must first rollback the current transaction before starting a new one.');

		if ($status == TRANSACTION_STATUS_INTRANS || $status == TRANSACTION_STATUS_ACTIVE)
			throw new DbQueryFailedException('Nested transactions are not supported. You must first commit the current transaction before starting a new one.');

		if ($status == TRANSACTION_STATUS_IDLE) {
			$this->executeNonQuery('start transaction');
			return;
		}
		throw new DbQueryFailedException();
	}

	public function commitTransaction()
	{
		$status = $this->getTransactionStatus();

		if ($status == TRANSACTION_STATUS_UNKNOWN)
			throw new DbConnectionFailedException();

		if ($status == TRANSACTION_STATUS_INERROR)
			throw new DbQueryFailedException('The connection is currently in a failed transaction block. The transaction cannot be committed.');

		if ($status == TRANSACTION_STATUS_IDLE)
			throw new DbQueryFailedException('There is no active transaction to be committed.');

		if ($status == TRANSACTION_STATUS_ACTIVE)
			throw new DbQueryFailedException('The transaction cannot be committed because there are still pending queries.');

		if ($status == TRANSACTION_STATUS_INTRANS) {
			$this->executeNonQuery('commit transaction');
			return;
		}
		throw new DbQueryFailedException();
	}

	public function rollbackTransaction()
	{
		$status = $this->getTransactionStatus();

		if ($status == TRANSACTION_STATUS_UNKNOWN)
			throw new DbConnectionFailedException();

		if ($status == TRANSACTION_STATUS_IDLE)
			throw new DbQueryFailedException('There is no active transaction to be rolled back.');

		if ($status == TRANSACTION_STATUS_ACTIVE)
			throw new DbQueryFailedException('The transaction cannot be committed because there are still pending queries.');

		if ($status == TRANSACTION_STATUS_INERROR || $status == TRANSACTION_STATUS_INTRANS) {
			$this->executeNonQuery('rollback transaction');
			return;
		}
		throw new DbQueryFailedException();
	}

	/**
	 * Submits a command to the server and waits for the result, with the ability to pass parameters separately from the SQL command text.
	 *
	 * @param string $query The parameterized SQL statement. Must contain only a single statement. (multiple statements separated by semi-colons are not allowed.)<br/>
	 * If any parameters are used, they are referred to as $1, $2, etc.
	 * @param array $params [optional] An array of parameter values to substitute for the $1, $2, etc. placeholders in the original prepared query string.<br/>
	 * The number of elements in the array must match the number of placeholders. Default is null.
	 * @return resource A query result resource on success. On failure, an exception is thrown.
	 */
	public function executeQuery($query, array $params = null)
	{
		SecUtil::checkStringArgument($query, 'query', -1, false, false, false);
		SecUtil::checkArrayArgument($params, 'params');

		$this->logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Executing query.', null, array('query' => $query, 'params' => $params));

		$this->connect();
		$connection = $this->getInnerConnection();

		$queryResult = empty($params) ? pg_query($connection, $query) : pg_query_params($connection, $query, $params);

		if ($queryResult === false) {
			$this->throwQueryFailed($query, $params);
		}
		return $queryResult;
	}

	/**
	 * Submits a command to the server and waits for the result, with the ability to pass parameters separately from the SQL command text.
	 *
	 * @param string $query The parameterized SQL statement. Must contain only a single statement. (multiple statements separated by semi-colons are not allowed.)<br/>
	 * If any parameters are used, they are referred to as $1, $2, etc.
	 * @param array $params [optional] An array of parameter values to substitute for the $1, $2, etc. placeholders in the original prepared query string.<br/>
	 * The number of elements in the array must match the number of placeholders. Default is null.
	 * @return mixed The string value from the first column of the first row returned from the server. If no rows were fetched from the server, null is returned.
	 */
	public function executeScalar($query, array $params = null)
	{
		$queryResult = $this->executeQuery($query, $params);
		$count = pg_num_rows($queryResult);

		if ($count == -1) {
			pg_free_result($queryResult);
			$this->throwQueryFailed($query, $params);
		}
		if ($count > 0) {
			$row = pg_fetch_array($queryResult, 0, PGSQL_NUM);
			if ($row === false) {
				pg_free_result($queryResult);
				$this->throwQueryFailed($query, $params);
			}
			pg_free_result($queryResult);

			$result = count($row) > 0 ? $row[0] : null;

			return $result;
		}
		return null;
	}

	/**
	 * Submits a command to the server and waits for the result, with the ability to pass parameters separately from the SQL command text.
	 *
	 * @param string $query The parameterized SQL statement. Must contain only a single statement. (multiple statements separated by semi-colons are not allowed.)<br/>
	 * If any parameters are used, they are referred to as $1, $2, etc.
	 * @param array $params [optional] An array of parameter values to substitute for the $1, $2, etc. placeholders in the original prepared query string.<br/>
	 * The number of elements in the array must match the number of placeholders. Default is null.
	 * @return int The number of rows affected by the query. If no tuple is affected, it will return 0.
	 */
	public function executeNonQuery($query, array $params = null)
	{
		$queryResult = $this->executeQuery($query, $params);
		$rowsAffected = pg_affected_rows($queryResult);
		pg_free_result($queryResult);
		return $rowsAffected;
	}

	public function loadRow($query, array $params = null, $assoc = true, &$rowCount = null)
	{
		$list = $this->loadArray($query, $params, $assoc, $rowCount);

		if ($rowCount > 0) {
			return $list[0];
		}
		return null;
	}

	/**
	 * Submits a command to the server and waits for the result, with the ability to pass parameters separately from the SQL command text.
	 *
	 * @param string $query The parameterized SQL statement. Must contain only a single statement. (multiple statements separated by semi-colons are not allowed.)<br/>
	 * If any parameters are used, they are referred to as $1, $2, etc. Default is null.
	 * @param array $params [optional] An array of parameter values to substitute for the $1, $2, etc. placeholders in the original prepared query string.<br/>
	 * The number of elements in the array must match the number of placeholders. Default is null, no parameters.
	 * @param boolean $assoc [optional] true, to return the results as array of associative arrays, false, to return the results as array of numerically indexed arrays.
	 * @param int $rowCount [optional] The number of rows returned by running the query.
	 * @return array An array with the results from the database. The format of the result depends of the parameter $assoc.
	 */
	public function loadArray($query, array $params = null, $assoc = true, &$rowCount = null)
	{
		SecUtil::checkBooleanArgument($assoc, 'assoc', false);

		$queryResult = $this->executeQuery($query, $params);
		$rowCount = pg_num_rows($queryResult);
		$list = array();

		if ($rowCount == -1) {
			pg_free_result($queryResult);
			$this->throwQueryFailed($query, $params);
		}
		for ($i = 0; $i < $rowCount; $i++) {
			$row = pg_fetch_array($queryResult, $i, $assoc ? PGSQL_ASSOC : PGSQL_NUM);
			if ($row === false) {
				pg_free_result($queryResult);
				$this->throwQueryFailed($query, $params);
			}
			$list[] = $row;
		}
		pg_free_result($queryResult);

		return $list;
	}

	/**
	 * Submits a command to the server and waits for the result, with the ability to pass parameters separately from the SQL command text.
	 *
	 * @param string $query The parameterized SQL statement. Must contain only a single statement. (multiple statements separated by semi-colons are not allowed.)<br/>
	 * If any parameters are used, they are referred to as $1, $2, etc. Default is null.
	 * @param array $params [optional] An array of parameter values to substitute for the $1, $2, etc. placeholders in the original prepared query string.<br/>
	 * The number of elements in the array must match the number of placeholders. Default is null, no parameters.
	 * @param int $rowCount [optional] The number of rows returned by running the query.
	 * @return array An array with values from the first column in the result set.
	 */
	public function loadScalarList($query, array $params = null, &$rowCount = null)
	{
		$queryResult = $this->executeQuery($query, $params);
		$rowCount = pg_num_rows($queryResult);
		$list = array();

		if ($rowCount == -1) {
			pg_free_result($queryResult);
			$this->throwQueryFailed($query, $params);
		}
		for ($i = 0; $i < $rowCount; $i++) {
			$row = pg_fetch_array($queryResult, $i, PGSQL_NUM);
			if ($row === false) {
				pg_free_result($queryResult);
				$this->throwQueryFailed($query, $params);
			}
			$list[] = $row[0];
		}
		pg_free_result($queryResult);

		return $list;
	}

	public function updateArray($query, array $data, $useTransaction)
	{
		// Check transaction status
		$tranStarted = false;

		if (!$this->inTransaction() && $useTransaction) {
			$this->beginTransaction();
			$tranStarted = true;
		}

		try {
			foreach ($data as $item) {
				$this->executeQuery($query, $item);
			}
			if ($tranStarted) {
				$tranStarted = false;
				$this->commitTransaction();
			}
		}
		catch (Exception $e) {
			// rollback transaction
			if ($tranStarted) {
				$this->rollbackTransaction();
			}
			throw $e;
		}
	}

	public function parseDbValue($value, $dbType = DB_TYPE_STRING, $allowNull = true)
	{
		SecUtil::checkIntegerArgument($dbType, 'dbType', false, DB_TYPE_STRING, DB_TYPE_DOUBLE);
		SecUtil::checkStringArgument($value, 'value', -1, $allowNull, true, false);

		if ($value === null && $allowNull)
			return null;

		$dbTypeName = 'DB_TYPE_STRING';

		if ($dbType === DB_TYPE_STRING)
		{
			return $value;
		}
		else if ($dbType === DB_TYPE_BOOL)
		{
			if ($value === 't' || (is_numeric($value) && floatval($value) != 0))
				return true;
			if ($value === 'f' || $value === '0')
				return false;

			$dbTypeName = '';
		}
		else if ($dbType === DB_TYPE_INT)
		{
			$value = trim($value);
			if (strlen($value) > 0 && is_numeric($value)) {
				$floatValue = floatval($value);
				$minValue = -2147483648.0;
				$maxValue = 2147483647.0;
				if ($floatValue >= $minValue && $floatValue <= $maxValue)
					return (int)$floatValue;
			}
			$dbTypeName = 'DB_TYPE_INT';
		}
		else if ($dbType === DB_TYPE_DATE)
		{
			$dateValue = date_create($value, new DateTimeZone(date_default_timezone_get()));
			if ($dateValue !== false)
				return $dateValue;
			$dbTypeName = 'DB_TYPE_DATE';
		}
		else if ($dbType === DB_TYPE_LONG)
		{
			$value = trim($value);
			if (strlen($value) > 0 && is_numeric($value)) {
				$floatValue = floatval($value);
				$minValue = -9223372036854775808.0;
				$maxValue = 9223372036854775807.0;
				if ($floatValue >= $minValue && $floatValue <= $maxValue)
					return (int)$floatValue;
			}
			$dbTypeName = 'DB_TYPE_LONG';
		}
		else if ($dbType === DB_TYPE_DOUBLE)
		{
			$value = trim($value);
			if (strlen($value) > 0 && is_numeric($value)) {
				$floatValue = floatval($value);
				return $floatValue;
			}
			$dbTypeName = 'DB_TYPE_DOUBLE';
		}
		throw new InvalidCastException("Value '{$value}' cannot be converted to '$dbTypeName'.");
	}

	public function freeResult($result)
	{
		if (!is_resource($result))
			throw new ArgumentException('Value must be a resource.', 'result');

		pg_free_result($result);
	}


	/**
	 * Throws a query failed exception. This method should be used to include more details in the exception being thrown.
	 *
	 * @param string $query The query that was executed with any errors.
	 * @param array $params [optional] Any parameters that were passed to the query.
	 */
	protected function throwQueryFailed($query, array $params = null)
	{
		if (!is_string($query)) $query = '';
		if (!is_array($params)) $params = null;

		$data = array(
			'providerError' => @pg_last_error($this->_connection),
			'database' => @pg_dbname($this->_connection),
			'query' => $query);

		if (!empty($params))
			$data['parameters'] = $params;

		$this->logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . ' - Query execution failed.', null, $data);

		throw new DbQueryFailedException();
	}
}
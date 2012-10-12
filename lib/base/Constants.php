<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */


//------------------------------------------------------------------------
// Debugging

/**
 * Specifies if Debugging is enabled.
 */
define('DEBUG', true);

//
//------------------------------------------------------------------------


//------------------------------------------------------------------------
// Logging

/**
 * No error logging.
 */
define('LOGGER_LEVEL_OFF', 0);

/**
 * Output error-handling messages.
 */
define('LOGGER_LEVEL_ERROR', 1);

/**
 * Output warnings and error-handling messages.
 */
define('LOGGER_LEVEL_WARN',  2);

/**
 * Output informational messages, warnings, and error-handling messages.
 */
define('LOGGER_LEVEL_INFO',  3);

/**
 * Output all debugging and tracing messages.
 */
define('LOGGER_LEVEL_DEBUG', 4);

//
//------------------------------------------------------------------------


//------------------------------------------------------------------------
// Transaction Statuses

/**
 * Connection is currently idle, not in a transaction.
 */
define('TRANSACTION_STATUS_IDLE', 0);

/**
 * A command is in progress on the connection. A query has been sent via the connection and not yet completed.
 */
define('TRANSACTION_STATUS_ACTIVE', 1);

/**
 * The connection is idle, in a transaction block.
 */
define('TRANSACTION_STATUS_INTRANS', 2);

/**
 * The connection is idle, in a failed transaction block.
 */
define('TRANSACTION_STATUS_INERROR', 3);

/**
 * The connection is bad.
 */
define('TRANSACTION_STATUS_UNKNOWN', 4);

//
//------------------------------------------------------------------------


//------------------------------------------------------------------------
// Database Data Types

/**
 * Type String
 */
define('DB_TYPE_STRING', 0);

/**
 * Type Boolean
 */
define('DB_TYPE_BOOL', 1);

/**
 * Type Integer
 */
define('DB_TYPE_INT', 2);

/**
 * Type Long
 */
define('DB_TYPE_LONG', 3);

/**
 * Type Date
 */
define('DB_TYPE_DATE', 4);

/**
 *Type Double
 */
define('DB_TYPE_DOUBLE', 5);

//
//------------------------------------------------------------------------



//------------------------------------------------------------------------
// RpcSettings

/**
 * The token should be generated every request.
 */
define('RPC_REGENERATE_TOKEN_AUTO', 0);

/**
 * The token should be generated manually.
 */
define('RPC_REGENERATE_TOKEN_MANUAL', 1);

//
//------------------------------------------------------------------------


//------------------------------------------------------------------------
// File System

/**
 * The maximum length of a path. Currently set to 4096 characters
 */
define('MAX_PATH_LENGTH', 4096);


//
//------------------------------------------------------------------------

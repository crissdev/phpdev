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
class ErrorCodes
{
	private function __construct() { }


	// General error codes
	const E_GENERAL_FIRST = 101;
	const E_GENERAL_INVALID_ARGUMENT = 101;
	const E_GENERAL_NULL_ARGUMENT = 102;
	const E_GENERAL_NOT_SUPPORTED = 103;
	const E_GENERAL_NOT_IMPLEMENTED = 104;
	const E_GENERAL_METHOD_ACCESS_ERROR = 105;
	const E_GENERAL_METHOD_INVOCATION_ERROR = 106;
	const E_GENERAL_INVALID_OPERATION = 107;
	const E_GENERAL_INTERNAL_ERROR = 108;
	const E_GENERAL_INVALID_CAST = 109;
	const E_GENERAL_LAST = 109;



	// Session error codes
	const E_SESSION_FIRST = 301;
	const E_SESSION_ERROR = 301;
	const E_SESSION_INVALID_STATE = 302;
	const E_SESSION_INIT_FAILED = 303;
	const E_SESSION_EXPIRED = 304;
	const E_SESSION_LAST = 304;



	// Database error codes
	const E_DB_FIRST = 601;
	const E_DB_CONNECTION_FAILED = 601;
	const E_DB_QUERY_FAILED = 602;
	const E_DB_FIELD_MISSING = 603;
	const E_DB_LAST = 603;


	// Network error codes
	const E_NET_FIRST = 501;
	const E_NET_BAD_REQUEST = 501;
	const E_NET_INVALID_CONTENT_TYPE = 502;
	const E_NET_INVALID_REQUEST_DATA = 503;
	const E_NET_INVALID_JSON = 504;
	const E_NET_LAST = 504;


	// Security error codes
	const E_SEC_FIRST = 401;
	const E_SEC_ACCESS_DENIED = 401;
	const E_SEC_AUTH_FAILED = 402;
	const E_SEC_LAST = 402;


	// Membership error codes
	const E_MEMBERSHIP_FIRST = 701;
	const E_MEMBERSHIP_USER_NOT_FOUND = 701;
	const E_MEMBERSHIP_USER_ALREADY_REGISTERED = 702;
	const E_MEMBERSHIP_EMAIL_ALREADY_REGISTERED = 703;
	const E_MEMBERSHIP_LAST = 703;

}
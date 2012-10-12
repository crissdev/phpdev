<?php

final class RpcSettings
{
	private static $_rpcTokenSessionKey = '__rpcToken';
	private static $_rpcTokenCookieKey = '__rpcCookie';
	private static $_rpcIpAddressSessionKey = '__rpcIPaddress';
	private static $_rpcRegenerateTokenFrequency = RPC_REGENERATE_TOKEN_AUTO;
	private static $_rpcRequireAuthentication = false;

	// this can even be stricter by also specifying the charset (e.g. application/json; charset=UTF-8)
	private static $_rpcContentType = 'text/json';


	private function __construct() { }

	/**
	 * Obtains the the key to access the rpc token in the $_SESSION variable.
	 *
	 * @return string Returns the key used to access the rpc token in the $_SESSION.
	 */
	public static function getRpcTokenSessionKey()
	{
		return self::$_rpcTokenSessionKey;
	}

	/**
	 *
	 * @param string $value The new key used to access the rpc token in the $_SESSION.
	 */
	public static function setRpcTokenSessionKey($value)
	{
		SecUtil::checkStringArgument($value, 'value', 30, false, false, true);
		self::$_rpcTokenSessionKey = $value;
	}

	/**
	 * Obtains the the key to access the rpc token in the $_COOKIE variable.
	 *
	 * @return string Returns the key used to access the rpc token in the $_COOKIE.
	 */
	public static function getRpcTokenCookieKey()
	{
		return self::$_rpcTokenCookieKey;
	}

	/**
	 *
	 * @param string $value The new name of the cookie to store the rpc token.
	 */
	public static function setRpcTokenCookieKey($value)
	{
		SecUtil::checkStringArgument($value, 'value', 30, false, false, true);
		self::$_rpcTokenCookieKey = $value;
	}

	/**
	 *
	 * @return int Whether or not to regenerate the rpc token on every request or manually.
	 */
	public static function getRegenerateTokenFrequency()
	{
		return self::$_rpcRegenerateTokenFrequency;
	}

	/**
	 * Sets how the token will be generated (auto or manual)
	 *
	 * @param int $freq How the token will be generated. Valid values are RPC_REGENERATE_TOKEN_AUTO and RPC_REGENERATE_TOKEN_MANUAL.
	 */
	public static function setRegenerateTokenFrequency($freq)
	{
		SecUtil::checkIntegerArgument($freq, 'freq', false, RPC_REGENERATE_TOKEN_AUTO, RPC_REGENERATE_TOKEN_MANUAL);
		self::$_rpcRegenerateTokenFrequency = $freq;
	}

	/**
	 * Obtains the the key to access the remote address of the client in the $_SESSION variable.
	 *
	 * @return string Returns the key used to store the remote address in the $_SESSION.
	 */
	public static function getRpcIpAddressSessionKey()
	{
		return self::$_rpcIpAddressSessionKey;
	}

	public static function etRpcIpAddressSessionKey($value)
	{
		SecUtil::checkStringArgument($value, 'value', 30, false, false, true);
		self::$_rpcIpAddressSessionKey = $value;
	}

	public static function getRpcContentType()
	{
		return self::$_rpcContentType;
	}

	public static function setRpcContentType($value)
	{
		SecUtil::checkStringArgument($value, 'value', 60, false, false, true);
		self::$_rpcContentType = $value;
	}

	public static function getRequireAuthentication()
	{
		return self::$_rpcRequireAuthentication;
	}

	public static function setRequireAuthentication($value)
	{
		SecUtil::checkBooleanArgument($value, 'value', false);
		self::$_rpcRequireAuthentication = $value;
	}
}
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
class SessionSettings
{
	private static $_sessionTokenName = '__sessionToken';
	private static $_sessionName = 'PHPSESSID';
	private static $_httpOnly = true;

	private function __construct() { }

	public static function getSessionTokenName()
	{
		return self::$_sessionTokenName;
	}

	public static function setSessionTokenName($value)
	{
		SecUtil::checkStringArgument($value, 'value', 60, false, false, true, null, false, true, '/^[a-z0-9_]+$/i');
		self::$_sessionTokenName = $value;
	}

	public static function getSessionName()
	{
		return self::$_sessionName;
	}

	public static function setSessionName($value)
	{
		SecUtil::checkStringArgument($value, 'value', 60, false, false, true, null, false, true, '/^[a-z0-9_]+$/i');
		self::$_sessionName = $value;
	}

	public static function getHttpOnlySessionCookie()
	{
		return self::$_httpOnly;
	}

	public static function setHttpOnlySessionCookie($value)
	{
		SecUtil::checkBooleanArgument($value, 'value', false);
		self::$_httpOnly = $value;
	}
}
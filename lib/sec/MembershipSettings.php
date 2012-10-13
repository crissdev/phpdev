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
class MembershipSettings
{
	private static $_dbSettingName = 'membership';
	private static $_dbSchema = 'master';
	private static $_enablePasswordRetrieval = false;
	private static $_requireUniqueEmail = true;
	private static $_userOnlineTimeWindow = 15;	// 15 minutes
	private static $_checkEmailDomain = true;


	public static function getDbSettingName()
	{
		return self::$_dbSettingName;
	}

	public static function setDbSettingName($value)
	{
		SecUtil::checkDbObjectName($value, 'value', false, false);
		self::$_dbSettingName = $value;
	}

	public static function getSchemaName()
	{
		return self::$_dbSchema;
	}

	public static function setSchemaName($value)
	{
		SecUtil::checkDbObjectName($value, 'value', false, false);
		self::$_dbSchema = $value;
	}

	public static function getEnablePasswordRetrieval()
	{
		return self::$_enablePasswordRetrieval;
	}

	public static function setEnablePasswordRetrieval($value)
	{
		SecUtil::checkBooleanArgument($value, 'value', false);
		self::$_enablePasswordRetrieval = $value;
	}

	public static function getRequireUniqueEmail()
	{
		return self::$_requireUniqueEmail;
	}

	public static function setRequireUniqueEmail($value)
	{
		SecUtil::checkBooleanArgument($value, 'value', false);
		self::$_requireUniqueEmail = $value;
	}

	public static function getUserOnlineTimeWindow()
	{
		return self::$_userOnlineTimeWindow;
	}

	public static function setUserOnlineTimeWindow($value)
	{
		SecUtil::checkIntegerArgument($value, 'value', false, 1, 60);
		self::$_userOnlineTimeWindow = $value;
	}

	public static function getCheckEmailDomain()
	{
		return self::$_checkEmailDomain;
	}

	public static function setCheckEmailDomain($value)
	{
		SecUtil::checkBooleanArgument($value, 'value', false);
		self::$_checkEmailDomain = $value;
	}
}
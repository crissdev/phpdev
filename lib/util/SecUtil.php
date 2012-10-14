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
class SecUtil
{
	private function __construct() { }


	// Basic checks


	/**
	 * Checks the input argument against a set of rules. <b>Note that the argument is passed by reference.</b><br/>
	 *
	 * @param string $argument The argument to check.
	 * @param string $argumentName The name of the argument being checked.
	 * @param int $maxLength [optional] Maximum length allowed. Default is <b>-1</b> (no constraints on length).
	 * @param bool $allowNull [optional] True, to allow null value, false, otherwise. Default is <b>true</b>.
	 * @param bool $allowEmpty [optional] True, to allow empty string, false, otherwise. Default is <b>true</b>.
	 * @param bool $trim [optional] True, to trim the value, false, otherwise. Default is <b>true</b>.
	 * @param string $invalidChars [optional] A string with characters that are not permitted in the $argument.
	 * @param bool $removeInvalidChars [optional] True, to remove any invalid character specified. Default is <b>false</b>.
	 * @param bool $throwOnInvalidChars [optional] True to throw an exception when invalid characters are detected in $argument.
	 * @param string $pattern
	 *
	 */
	public static function checkStringArgument(&$argument, $argumentName, $maxLength = -1, $allowNull = true, $allowEmpty = true, $trim = true, $invalidChars = null, $removeInvalidChars = false, $throwOnInvalidChars = true, $validPattern = null)
	{
		// Check for null
		if (!$allowNull && $argument === null)
			throw new ArgumentNullException(null, $argumentName);

		// Check for null but skip trim as it turns null into an empty string!!!
		if ($allowNull && $argument === null)
			return;

		// Check type
		if (!is_string($argument))
			throw new ArgumentException('The value passed must be of type string.', $argumentName);

		// Trim
		if ($trim)
			$argument = trim($argument);

		// Check empty
		if (!$allowEmpty && strlen($argument) < 1)
			throw new ArgumentException('The value passed is not allowed to be null or empty string.', $argumentName);

		if (is_string($invalidChars) && strlen($invalidChars) > 0) {
			// Check for invalid chars
			$pattern = '/[' . preg_quote($invalidChars, '/') . ']/';
			$result = preg_match($pattern, $argument);

			if ($result === false) {
				// Runtime error; probably will never occur.
				throw new InternalErrorException('Failed to remove invalid characters.');
			}
			if ($result > 0) {
				if ($throwOnInvalidChars)
					throw new ArgumentException('The value passed contains invalid characters.', $argumentName);
				if ($removeInvalidChars)
					$argument = preg_replace($pattern, '', $argument);
			}
		}

		// Check length
		if ($maxLength > 0 && strlen($argument) > $maxLength)
			throw new ArgumentException("The value passed cannot exceed $maxLength characters.", $argumentName);

		// Check empty
		if (!$allowEmpty && strlen($argument) < 1)
			throw new ArgumentException('The value passed cannot be null or empty string.', $argumentName);

		// Check if a regular expression was defined
		if (is_string($validPattern) && strlen($validPattern) > 0)
		{
			$result = preg_match($validPattern, $argument);
			if ($result === false) {
				// Runtime error; probably will never occur.
				throw new InternalErrorException('Failed to validate argument against regular expression.');
			}
			if ($result === 0)
				throw new ArgumentException('The value passed does not match the required pattern.', $argumentName);
		}
	}

	/**
	 * Checks the input argument against a set of rules.
	 *
	 * @param bool $argument The argument to check.
	 * @param string $argumentName The name of the argument being checked.
	 * @param bool $allowNull [optional] True, to allow null value, false, otherwise. Default is <b>true</b>.
	 */
	public static function checkBooleanArgument($argument, $argumentName, $allowNull = true)
	{
		if (!$allowNull && $argument === null)
			throw new ArgumentNullException(null, $argumentName);

		if ($argument !== null && !is_bool($argument))
			throw new ArgumentException('The value passed must be of type boolean.', $argumentName);
	}

	/**
	 * Checks the input argument against a set of rules.
	 *
	 * @param int $argument The argument to check.
	 * @param string $argumentName The name of the argument being checked.
	 * @param bool $allowNull [optional] True, to allow null value, false, otherwise. Default is <b>true</b>.
	 * @param int $minValue [optional] The minimum value allowed for this argument. Default is <b>-2147483648</b>.
	 * @param int $maxValue [optional] The maximum value allowed for this argument. Default is <b>2147483647</b>.
	 */
	public static function checkIntegerArgument($argument, $argumentName, $allowNull = true, $minValue = -2147483648, $maxValue = 2147483647)
	{
		if (!$allowNull && $argument === null)
			throw new ArgumentNullException(null, $argumentName);

		if ($argument !== null && !is_integer($argument))
			throw new ArgumentException('The value passed must be of type integer.');

		if ($argument !== null && $minValue !== null && $argument < $minValue)
			throw new ArgumentException("The value passed must be greater than $minValue.", $argumentName);

		if ($argument !== null && $maxValue !== null && $argument > $maxValue)
			throw new ArgumentException("The value passed must be less than $maxValue.", $argumentName);
	}

	/**
	 * Checks the input argument against a set of rules.
	 *
	 * @param DateTime $argument The argument to check.
	 * @param string $argumentName The name of the argument being checked.
	 * @param bool $allowNull [optional] True, to allow null value, false, otherwise. Default is <b>true</b>.
	 * @param DateTime $minValue [optional] <i>Currently not used.</i>
	 * @param DateTime $maxValue [optional] <i>Currently not used.</i>
	 */
	public static function checkDateTimeArgument($argument, $argumentName, $allowNull = true, DateTime $minValue = null, DateTime $maxValue = null)
	{
		if (!$allowNull && $argument === null)
			throw new ArgumentNullException(null, $argumentName);

		if ($argument !== null && get_class($argument) !== 'DateTime')
			throw new ArgumentException('The value passed must be of type DateTime.', $argumentName);

		if ($argument !== null && $minValue !== null && $argument < $minValue)
			throw new ArgumentException('The value passed must be greater than ' . $minValue->format('Y-m-d H:i:s') . '.', $argumentName);

		if ($argument !== null && $maxValue !== null && $argument > $maxValue)
			throw new ArgumentException('The value passed must be greater than ' . $maxValue->format('Y-m-d H:i:s') . '.', $argumentName);
	}

	/**
	 * Checks the input argument against a set of rules.
	 *
	 * @param array $argument The argument to check.
	 * @param string $argumentName The name of the argument being checked.
	 * @param bool $allowNull [optional] True, to allow null value, false, otherwise. Default is <b>true</b>.
	 * @param bool $allowEmpty [optional] True, to allow empty array, false, otherwise. Default is <b>true</b>.
	 */
	public static function checkArrayArgument($argument, $argumentName, $allowNull = true, $allowEmpty = true)
	{
		if (!$allowNull && $argument === null)
			throw new ArgumentNullException(null, $argument);

		if ($argument !== null && !is_array($argument))
			throw new ArgumentException('The value passed must be of type Array.', $argumentName);

		if ($argument !== null && !$allowEmpty && count($argument) == 0)
			throw new ArgumentException('The value passed cannot be an empty array.', $argumentName);
	}



	// Specialized checks

	public static function checkIpv4Address($argument, $argumentName, $allowNull, $onlyPublicIp)
	{
		SecUtil::checkStringArgument($argument, $argumentName, 15, $allowNull, false, true);

		$ipv4Pattern = '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/';
		$ret = preg_match($ipv4Pattern, $argument);
		if ($ret !== false && $ret > 0) {
			if ($onlyPublicIp) {
				$long = ip2long($argument);
				if ($long === false) {
					throw new ArgumentException('The value passed is not a valid IP address.', $argumentName);
				}
				if (($long >= 167772160 && $long <= 184549375) || ($long >= -1408237568 && $long <= -1407188993) ||
					($long >= -1062731776 && $long <= -1062666241) || ($long >= 2130706432 && $long <= 2147483647) || $long == -1) {
					throw new ArgumentException('The value passed is not a public IP address.', $argumentName);
				}
			}
			return;
		}
		throw new ArgumentException('Invalid IP address.', $argumentName);
	}

	public static function checkEmailAddress(&$argument, $argumentName, $maxLength = 128, $allowNull = false, $allowEmpty = false, $verifyDomain = false)
	{
		self::checkStringArgument($argument, $argumentName, $maxLength, $allowNull, $allowEmpty, true, null, false, true, "/^([a-z0-9])+([a-z0-9\._-])*@([a-z0-9_-])+([a-z0-9\._-]+)+$/i");

		if (($allowNull && $argument === null) || ($allowEmpty && $argument === ''))
			return;

		if ($verifyDomain) {
			list($name, $domain) = explode('@', $argument);
			if (!checkdnsrr($domain, 'MX'))
				throw new ArgumentException('The value passed is not a valid email address. Invalid DNS.', $argumentName);
		}
	}

	//-----------------------------------------------------------------------------
	// Membership & Roles specialized security checks
	//
	public static function checkUserName(&$argument, $argumentName)
	{
		self::checkStringArgument($argument, $argumentName, 20, false, false, true, null, false, true, '/^[a-zA-Z]+[\.\-\'_ ]?[0-9a-zA-Z ]*$/');
	}

	public static function checkRoleName(&$argument, $argumentName)
	{
		SecUtil::checkStringArgument($argument, $argumentName, 60, false, false, true, null, false, true, '/^[a-zA-Z]+[ \-]?[a-zA-Z ]*$/');
	}

	public static function checkDbObjectName(&$argument, $argumentName, $allowNull = false, $allowEmpty = false)
	{
		SecUtil::checkStringArgument($argument, $argumentName, 128, $allowNull, $allowEmpty, true, null, false, false, '/^[a-z_]+[a-z0-9_]*$/i');
	}
}
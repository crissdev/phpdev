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
class RequestUtil
{
	private function __construct() { }

	public static function requireGET()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'GET')
			throw new BadRequestException('Only GET requests are allowed.');
	}

	public static function requirePOST()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
			throw new BadRequestException('Only POST requests are allowed.');
	}


	public static function extractParameter(array $from, $name, $defaultValue = null, $trim = false, $nullOnEmpty = false)
	{
		SecUtil::checkArrayArgument($from, 'from', false, true);
		SecUtil::checkStringArgument($name, 'name', -1, false, false, false);
		SecUtil::checkBooleanArgument($trim, 'trim', false);
		SecUtil::checkBooleanArgument($nullOnEmpty, 'nullOnEmpty', false);

		if (isset($from[$name]))
		{
			if ($from[$name] === '' && $nullOnEmpty)
				return null;

			return $from[$name];
		}
		return $defaultValue;
	}

	public static function redirect($location, $endResponse = true)
	{
		SecUtil::checkStringArgument($location, 'location', 2048, false, false);
		SecUtil::checkBooleanArgument($endResponse, 'endResponse', false);

		if (headers_sent())
			throw new InvalidOperationException('Headers already sent.');

		header('Status: 302');
		header('Location: ' . $location);

		if ($endResponse)
			exit;
	}

	public static function redirectPermanent($location, $endResponse = true)
	{
		SecUtil::checkStringArgument($location, 'location', 2048, false, false);
		SecUtil::checkBooleanArgument($endResponse, 'endResponse', false);

		if (headers_sent())
			throw new InvalidOperationException('Headers already sent.');

		header('Status: 301');
		header('Location: ' . $location);

		if ($endResponse)
			exit;
	}
}
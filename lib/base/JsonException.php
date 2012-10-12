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
class JsonException extends ApplicationException
{
	public function __construct($message = null, $jsonErrorCode = null, Exception $previous = null)
	{
		SecUtil::checkStringArgument($message, 'message', -1);
		SecUtil::checkIntegerArgument($jsonErrorCode, 'jsonErrorCode', true);

		if (strlen($message) == 0)
		{
			$message = "JSON encode or decode error.";
		}
		if ($jsonErrorCode !== null)
		{
			$message .= PHP_EOL . "Last json error: $jsonErrorCode.";
		}
		parent::__construct($message, ErrorCodes::E_NET_INVALID_JSON, $previous);
	}
}

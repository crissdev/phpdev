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
class ArgumentException extends ApplicationException
{
	public function __construct($message = null, $argumentName = null, Exception $previous = null)
	{
		SecUtil::checkStringArgument($message, 'message', -1);
		SecUtil::checkStringArgument($argumentName, 'argumentName', -1);

		if (strlen($argumentName) > 0)
		{
			if (strlen($message) == 0)
			{
				$message = "Exception of type " . get_class($this) . " was thrown.";
			}
			$message .= PHP_EOL . "Parameter name: $argumentName";
		}
		else
		{
			$message = 'Value does not fall within the expected range.';
		}
		parent::__construct($message, ErrorCodes::E_GENERAL_INVALID_ARGUMENT, $previous);
	}
}

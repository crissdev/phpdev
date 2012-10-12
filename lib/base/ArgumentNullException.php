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
class ArgumentNullException extends ArgumentException
{
	public function __construct($message = null, $argumentName = null, Exception $previous = null)
	{
		SecUtil::checkStringArgument($message, 'message', -1);
		SecUtil::checkStringArgument($argumentName, 'argumentName', -1);

		if (strlen($message) == 0)
		{
			$message = "Value cannot be null.";
		}
		parent::__construct($message, $argumentName, $previous);
	}
}

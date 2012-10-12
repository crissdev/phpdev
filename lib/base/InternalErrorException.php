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
class InternalErrorException extends ApplicationException
{
	public function __construct($message = null, Exception $previous = null)
	{
		SecUtil::checkStringArgument($message, 'message', -1);

		if (strlen($message) == 0)
		{
			$message = 'The operation failed due to an internal error.';
		}
		parent::__construct($message, ErrorCodes::E_GENERAL_INTERNAL_ERROR, $previous);
	}
}

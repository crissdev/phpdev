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
class SessionException extends ApplicationException
{
	public function __construct($message = null, $errorCode = ErrorCodes::E_SESSION_ERROR, Exception $previous = null)
	{
		SecUtil::checkStringArgument($message, 'message', -1);

		if (strlen($message) == 0)
		{
			$message = 'An error occurred while accessing the session.';
		}
		parent::__construct($message, $errorCode, $previous);
	}
}
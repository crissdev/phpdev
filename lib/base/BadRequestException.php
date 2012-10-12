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
class BadRequestException extends ApplicationException
{
	public function __construct($message = null, Exception $previous = null)
	{
		if (strlen($message) == 0)
		{
			$message = "Bad request.";
		}
		parent::__construct($message, ErrorCodes::E_NET_BAD_REQUEST, $previous);
	}

}
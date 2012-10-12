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
class ApplicationException extends Exception
{
	public function __construct($message = null, $code = E_USER_ERROR, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
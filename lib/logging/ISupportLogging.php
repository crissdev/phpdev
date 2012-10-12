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
interface ISupportLogging
{
	/**
	 * @return Logger
	 */
	function getLogger();

	/**
	 * @param Logger $logger
	 */
	function setLogger(Logger $logger = null);
}
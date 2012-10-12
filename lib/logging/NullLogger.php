<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */

/**
 * Discards al log entries.
 */
final class NullLogger extends Logger
{
	public function __construct()
	{
		parent::__construct(LOGGER_LEVEL_OFF, '');
	}

	public final function logEvent($level, $message, array $args = null, $data = null)
	{
		return 0;
	}
}
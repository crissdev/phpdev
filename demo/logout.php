<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */

require 'wsinit.php';

try
{
	Authentication::signOut();
}
catch (Exception $e)
{
	App::logEvent(LOGGER_LEVEL_WARN, __FILE__ . ' - An error occurred durring the sign out process.', null, $e);
}
RequestUtil::redirect('index.php');

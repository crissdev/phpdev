<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */

$rootPath = realpath(dirname(__FILE__) . '/..');

require "$rootPath/lib/App.php";


function App_onInit()
{
	App::addIncludePath(array('inc', 'rpc'), dirname(__FILE__));
}

function App_onInitComplete()
{
	$logger = new FileLogger(AppSettings::getLogFileName(), LOGGER_LEVEL_DEBUG, 'Application');

	App::setLogger($logger);
	Rpc::setLogger($logger);

	$fileName = PathUtil::combine(AppSettings::getTempPath(), 'master_db.log');
	LoggerManager::registerLazyInstance('master_db_logger', 'FileLogger', array($fileName, LOGGER_LEVEL_DEBUG, 'master_db'));

	// Install the membership database
	//	$installer = MembershipInstaller::getInstaller();
	//	$installer->uninstall();
	//	$installer->install();
}
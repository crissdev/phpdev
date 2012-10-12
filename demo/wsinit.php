<?php

$rootPath = realpath(dirname(__FILE__) . '/..');

require "$rootPath/lib/App.php";


function App_onInit()
{
	App::addIncludePath(array('inc'), dirname(__FILE__));
}

function App_onInitComplete()
{
	App::setLogger(new FileLogger(AppSettings::getLogFileName(), LOGGER_LEVEL_DEBUG, 'Application'));

	$fileName = PathUtil::combine(AppSettings::getTempPath(), 'master_db.log');
	LoggerManager::registerLazyInstance('master_db_logger', 'FileLogger', array($fileName, LOGGER_LEVEL_DEBUG, 'master_db'));
}
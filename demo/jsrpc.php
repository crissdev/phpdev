<?php
/**
* @package      phpdev
* @author       Cristian Trifan
* @copyright    2012 Cristian Trifan
* @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
*/
//
// The RPC currently works ONLY with POST requests!
//
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		//
		// Usually, sites that use this framework will likely want to define an entry point (e.g. wsinit.php) instead of loading the App.php file.
		//
		require 'wsinit.php';

		//--------------------------------------------------------------------------------------------
		// Configure RPC callable/non-callable methods here
		//--------------------------------------------------------------------------------------------
		//
		// Allow RPC method calls for classes class1 and class2
		//
		//		Rpc::allowRpcCall(array('class1', 'class2'));
		//
		//--------------------------------------------------------------------------------------------
		// Disallow RPC method calls for method1 and method2 defined in class class1
		//
		//		Rpc::disallowRpcCall('class1', array('method1', 'method2'));
		//
		//--------------------------------------------------------------------------------------------
		// Disallow all RPC method calls
		//		Rpc::disallowRpcCall('class1', array('*'));

		Rpc::allowRpcCall(array('UserRpc'));


		Rpc::processRequest();
		exit();
	}
	catch (Exception $e) {
		// This a FATAL error and should rarely/never be seen
		$logger = App::getLogger();
		if ($logger !== null) {
			$logger->errorFormat('There was an error processing JSON RPC request: %s', array($e->getMessage()), array('error' => $e));
		}
		exit('There was an error processing your request. Please try again later.');
	}
}
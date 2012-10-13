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
final class Rpc
{
	private static $_logger = null;
	private static $_rpcClasses = array();
	private static $_disallowedMethods = array();
	private static $_currentMethodCall = null;

	private function __construct() { }

	public static function allowRpcCall(array $classes)
	{
		SecUtil::checkArrayArgument($classes, 'classes', false, true);

		self::logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Processing class list.', null, $classes);

		for ($i = 0, $n = count($classes); $i < $n; $i++) {
			$item = $classes[$i];
			SecUtil::checkStringArgument($item, "class.item[{$i}]", -1, false, false, false, '?*&^%$#@:>-\/`,.()+=<', false, true);
			if (!in_array($item, self::$_rpcClasses))
				self::$_rpcClasses[] = $item;
		}
	}

	public static function disallowRpcCall($class, array $methods = null)
	{
		SecUtil::checkStringArgument($class, 'class', -1, false, false, false, '?*&^%$#@:>-\/`,.()+=<', false, true);
		SecUtil::checkArrayArgument($methods, 'methods', true, true);

		self::logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Processing class methods list', null, array('class' => $class, 'methods' => $methods));

		if (!array_key_exists($class, self::$_disallowedMethods))
			self::$_disallowedMethods[$class] = array();

		if ($methods !== null && count($methods) > 0) {

			if (count($methods) == 1) {
				SecUtil::checkStringArgument($methods[0], "methods.item[0]", -1, false, false, false, '?*&^%$#@:>-\/`,.()+=<');
				if ($methods[0] == '*') {
					self::$_disallowedMethods[$class] = array('*');
					return;
				}
			}
			else {
				for ($i = 0, $n = count($methods), $item = $methods[$i]; $i < $n; $i++) {
					SecUtil::checkStringArgument($item, "methods.item[{$i}]", -1, false, false, false, '?*&^%$#@:>-\/`,.()+=<');
					if ($item == '*') {
						$details = "You cannot specify any other methods when specifying the '*' (disallow rpc calls to all method of the class).";
						self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
						throw new ArgumentException($details, 'methods');
					}
				}
			}

			for ($i = 0, $n = count($methods), $item = $methods[$i]; $i < $n; $i++) {
				SecUtil::checkStringArgument($item, "methods.item[{$i}]", -1, false, false, false, '?*&^%$#@:>-\/`,.()+=<');
				if (!in_array($item, self::$_disallowedMethods))
					array_push(self::$_disallowedMethods[$class], $item);
			}
		}
	}

	public static function processRequest()
	{
		self::logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Started');

		$response = self::processRequestCore();
		self::sendResponse($response);

		self::logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Ended');
	}

	/**
	 * @return array Information regarding what method the RPC will call (class => method).
	 */
	public static function getRpcCallInfo()
	{
		if (self::$_currentMethodCall !== null) {
			return array(self::$_currentMethodCall[0], self::$_currentMethodCall[1]);
		}
		return null;
	}

	private static function prepareResponse($result)
	{
		$result = JsonUtil::encode($result);
		return $result;
	}

	private static function sendResponse($response)
	{
		$contentType = RpcSettings::getRpcContentType();
		header('Content-Type: ' . $contentType);

		// The content is not compressed during development as it causes even more problems when warnings are generated before ob_start is called.
		// The side effect is that on the client side the response will not be visible because the decompression will fail (due to a mix of clear and compressed output).
		if (strlen($response) > 1000 && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && !AppSettings::isDebuggingEnabled()) {
			self::logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Sending compressed response.', null, array('Content type' => $contentType, 'Response data' => $response));
			ob_start("ob_gzhandler");
		}
		else {
			self::logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Sending response.', null, array('Content type' => $contentType, 'Response data' => $response));
			ob_start();
		}

		echo $response;

		ob_end_flush();
	}

	private static function processRequestCore()
	{
		$response = array('jsonrpc' => 2.0, 'id' => -1, 'token' => null);
		try {
			self::validateRemoteIp();
			self::validateRequest();

			$rpcTokenSessionKey = RpcSettings::getRpcTokenSessionKey();
			$rpcTokenCookieKey = RpcSettings::getRpcTokenCookieKey();

			// retrieve the request data
			$requestData = self::parseRequestData();

			// set id property
			$response['id'] = $requestData->id;

			// validate RPC version
			if (!is_numeric($requestData->jsonrpc) || floatval($requestData->jsonrpc) != 2.0)
			{
				$details = sprintf('Invalid rpc version. Expected %.1f but received %s.', 2.0, is_scalar($requestData->jsonrpc) ? $requestData->jsonrpc : '<object>');
				self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");

				throw new BadRequestException('Invalid JSON-RPC version.');
			}

			$token = null;

			// validate token
			if (Session::get($rpcTokenSessionKey))
			{
				$token = property_exists($requestData, 'token') ? $requestData->token : null;
				$cookie = isset($_COOKIE[$rpcTokenCookieKey]) ? $_COOKIE[$rpcTokenCookieKey] : null;

				if ($token !== Session::get($rpcTokenSessionKey) || $token !== $cookie)
				{
					$details = sprintf('The supplied RPC token is different. Current token: \'%s\', Provided token: \'%s\'.', Session::get($rpcTokenSessionKey), $token);
					self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
					throw new SessionExpiredException();
				}
				unset($cookie);
			}
			else if (property_exists($requestData, 'token') && !empty($requestData->token)) {
				// the exception was changed to a session expiration exception as this is most probably the cause
				$details = 'The supplied RPC token is no longer valid because it was not found in session. Request data: ' . var_export($requestData, true);
				self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
				throw new SessionExpiredException();
			}

			// Generate a new token; the token must be generated on each new request
			if (RpcSettings::getRegenerateTokenFrequency() == RPC_REGENERATE_TOKEN_AUTO) {
				$token = self::generateRpcToken();
				Session::set($rpcTokenSessionKey, $token);
			}
			$response['token'] = $token;

			// Obtain class and method to call
			$methodInfo = explode('.', $requestData->method);
			if (!is_array($methodInfo) || count($methodInfo) != 2) {
				self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . ' - The method was not specified.');
				throw new BadRequestException('The method was not specified.');
			}

			list($className, $methodName) = $methodInfo;
			unset($methodInfo);

			self::$_currentMethodCall = array($className, $methodName);
			self::validateMethodCall($className, $methodName);

			$result = null;
			try {
				// Invoke method
				$args = is_array($requestData->params) ? $requestData->params : array($requestData->params);
				$result = self::invokeMethod($className, $methodName, $args);

				// If current method call is destroying the session (Session::destroy) then we can verify using session_id and avoid starting a new session
				// We also need to grab the new token if token generation is set to manual (the invoked method might call Rpc::regenerateRpcToken)
				if (RpcSettings::getRegenerateTokenFrequency() == RPC_REGENERATE_TOKEN_MANUAL) {
					if (session_id() != '') {
						// the token might change after method invocation, for example on authentication
						$response['token'] = Session::get($rpcTokenSessionKey);
					}
					else {
						// the session has been destroyed; we don't send any token to the client
						$response['token'] = null;
					}
				}
			}
			catch (Exception $e) {
				if (RpcSettings::getRegenerateTokenFrequency() == RPC_REGENERATE_TOKEN_MANUAL) {
					if (session_id() != '') {
						// the token might change after method invocation, for example on authentication
						$response['token'] = Session::get($rpcTokenSessionKey);
					}
					else {
						// the session has been destroyed; we don't send any token to the client
						$response['token'] = null;
					}
				}
				$details = sprintf('Method %s.%s threw the following error: %s', $className, $methodName, $e->getMessage());
				self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " {$details}");

				throw $e;
			}
			$response['result'] = $result;
		}
		catch (Exception $e) {
			// we should never reach here...
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . ' - Error processing RPC request: Error code: %d Error message: %s', array($e->getCode(), $e->getMessage()));

			//TODO: Verify the error message sent to client side
			$response['error'] = array('code' => $e->getCode(), 'message' => $e->getMessage());
		}
		return self::prepareResponse($response);
	}

	public static function regenerateRpcToken()
	{
		if (RpcSettings::getRegenerateTokenFrequency() == RPC_REGENERATE_TOKEN_MANUAL) {
			Session::set(RpcSettings::getRpcTokenSessionKey(), self::generateRpcToken());
			return;
		}
		$details = 'The current setting for RPC does not allow manual changes to the RPC token.';
		self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
		throw new InvalidOperationException($details);
	}

	private static function generateRpcToken()
	{
		return md5(mt_rand());
	}

	private static function invokeMethod($className, $methodName, array $args = null)
	{
		$class = new ReflectionClass($className);
		$method = $class->getMethod($methodName);

		if (!$method || $method->isAbstract() || !$method->isUserDefined() || !$method->isPublic())
		{
			$details = "Method '{$className}.{$methodName}' is not accessible.";
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
			throw new MethodAccessException();
		}

		if ($method->isStatic()) {
			return $method->invokeArgs(null, $args);
		}
		// If an instance of this class cannot be created then a ReflectionException will be thrown
		$instance = $class->newInstance();
		return $method->invokeArgs($instance, $args);
	}

	private static function validateRemoteIp()
	{
		$rpcIpAddressSessionKey = RpcSettings::getRpcIpAddressSessionKey();
		$sessionIpAddress = Session::get($rpcIpAddressSessionKey);
		$remoteIpAddress = $_SERVER['REMOTE_ADDR'];

		self::logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Validating remote address \'%s\'.', array($remoteIpAddress));

		if (!empty($sessionIpAddress) && ($sessionIpAddress !== $remoteIpAddress)) {
			$details = sprintf('The remote IP address is different. IP stored in session: \'%s\'. Remote IP address: \'%s\'', $sessionIpAddress, $remoteIpAddress);
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
			throw new SessionExpiredException();
		}
		Session::set($rpcIpAddressSessionKey, $remoteIpAddress);
	}

	private static function validateRequest()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . ' - Only POST requests are allowed.');
			throw new BadRequestException('Only POST requests are allowed.');
		}

		//if (empty($_SERVER['CONTENT_TYPE']) || preg_match('/^application\/json/i', $_SERVER['CONTENT_TYPE']) != 1)
		if (empty($_SERVER['CONTENT_TYPE']) || preg_match('/^'.preg_quote(RpcSettings::getRpcContentType(), '/').'/i', $_SERVER['CONTENT_TYPE']) != 1)
		{
			$details = sprintf('Expected \'%s\' content type but received \'%s\'.', RpcSettings::getRpcContentType(), $_SERVER['CONTENT_TYPE']);
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
			throw new BadRequestException('Invalid content type.');
		}
	}

	private static function validateMethodCall($className, $methodName)
	{
		// Method must not begin with underscore (even if it's a public method)
		if (substr_compare($methodName, '_', 0, 1) == 0)
		{
			$details = 'Method names that start with underscore are not RPC-callable';
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
			throw new MethodAccessException();
		}

		// Check in rpc $_rpcClasses
		if (!in_array($className, self::$_rpcClasses))
		{
			$details = "Class '{$className}' was not configured to be RPC-callable.";
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
			throw new MethodAccessException();
		}

		// Check in $_disallowedMethods
		if (array_key_exists($className, self::$_disallowedMethods) && in_array($methodName, self::$_disallowedMethods[$className]))
		{
			$details = "Method '{$className}.{$methodName}' has been disallowed to be RPC-callable.";
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
			throw new MethodAccessException();
		}

		// Check to see if all methods are blocked
		if (array_key_exists($className, self::$_disallowedMethods) && count(self::$_disallowedMethods[$className]) == 1 && self::$_disallowedMethods[$className][0] == '*')
		{
			$details = "All methods of class '{$className}' have been disallowed to be RPC-callable (error occured while checking method '{$className}.{$methodName}').";
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
			throw new MethodAccessException();
		}
	}

	private static function parseRequestData()
	{
		$request = file_get_contents('php://input');

		if (!is_string($request) || empty($request))
		{
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . ' - The request data is empty.');
			throw new BadRequestException('The request data is empty.');
		}

		self::logEvent(LOGGER_LEVEL_DEBUG, __METHOD__ . ' - Parsing request data.');

		$requestData = null;

		try {
			// decode request data, not that depth and maxJsonLength are not specified here and use the defaults
			$requestData = JsonUtil::decode($request);
		}
		catch (Exception $e) {
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . ' - The request data could not be decoded.', null, array('exception' => $e));
			throw new BadRequestException('The request data cannot be decoded.', $e);
		}
		unset($request);

		if (!is_object($requestData))
		{
			$details = 'The request data cannot be decoded. It is either malformed either depth greater than the allowed limit.';
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . " - {$details}");
			throw new BadRequestException('The request data cannot be decoded.');
		}

		// validate request data
		if (!property_exists($requestData, 'jsonrpc') || !property_exists($requestData, 'id') || !property_exists($requestData, 'method') || !property_exists($requestData, 'params'))
		{
			self::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . ' - The request data is missing one of the required properties.');
			throw new BadRequestException('The request data is missing one of the required properties.');
		}

		// If params argument is null then we change it to an empty array otherwise we'll end up calling a method with no arguments with a NULL argument
		if ($requestData->params === null)
			$requestData->params = array();

		return $requestData;
	}



	// logging methods
	private static function logEvent($level, $message, array $args = null, $data = null)
	{
		if (self::$_logger == null)
			return 0;
		return self::$_logger->logEvent($level, $message, $args, $data);
	}

	// ISupportErrorLogging implementation

	public static function getLogger()
	{
		return self::$_logger;
	}

	public static function setLogger(Logger $logger = null)
	{
		self::$_logger = $logger;
	}
}
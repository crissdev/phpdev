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
class Session
{
	private static $_started = false;
	private static $_sessionToken = null;
	private static $_sessionId = null;


	private function __construct() { }

	/**
	 * Determines whether the session was started.
	 *
	 * @return bool Whether or not the session was started.
	 */
	public static function isStarted()
	{
		return self::$_started;
	}

	/**
	 * Destroys the current session.
	 */
	public static function destroy()
	{
		self::init();

		$params = session_get_cookie_params();

		// we do not check any return value for setcookie as it's not bad if this function fails - the session will be destroyed and the session cookie will no longer be valid.
		setcookie(SessionSettings::getSessionName(), '', time() - 42000, $params['path'], $params['domain'], !empty($_SERVER['HTTPS']) ? true : false, SessionSettings::getHttpOnlySessionCookie());

		// un-sets the $_SESSION super global (must be called before session_destroy()
		session_unset();

		// clears the session identifier
		session_destroy();

		self::$_sessionToken = null;
		self::$_started = false;
		self::$_sessionId = null;
	}

	/**
	 * Gets a session value by key.
	 *
	 * @param string $key The key of the value to get.
	 * @return mixed The session-state value with the specified key, or null if the item does not exist.
	 */
	public static function get($key)
	{
		SecUtil::checkStringArgument($key, 'key', 255, false, false, false);

		self::init();
		return (isset($_SESSION[$key])) ? $_SESSION[$key] : null;
	}

	/**
	 * Sets a session value by key.
	 *
	 * @param string $key The key of the value to set.
	 * @param mixed $value The value of the element to set. <b>The value cannot indicate a resource.</b>
	 */
	public static function set($key, $value)
	{
		SecUtil::checkStringArgument($key, 'key', 255, false, false, false);

		if (is_resource($value))
			throw new ArgumentException('Resources cannot be persisted in the session.', $value);

		self::init();
		$_SESSION[$key] = $value;
	}

	/**
	 * Determines whether the $_SESSION contains the specified key.
	 *
	 * @param string $key The key to locate in the $_SESSION.
	 * @return bool if the _SESSION contains a value with the specified key; otherwise, false.
	 */
	public static function containsKey($key)
	{
		SecUtil::checkStringArgument($key, 'key', 255, false, false, false);

		self::init();
		return isset($_SESSION[$key]);
	}

	/**
	 * Removes the value with the specified key from the $_SESSION.
	 *
	 * @param string $key The key of the element to remove.
	 * @return bool true if the element is successfully found and removed; otherwise, false.<br/>
	 * This method also returns false if key is not found in the $_SESSION.
	 */
	public static function remove($key)
	{
		if (self::containsKey($key)) {
			unset($_SESSION[$key]);
			return true;
		}
		return false;
	}

	public static function regenerateId()
	{
		self::validateSession();

		$result = session_regenerate_id(true);

		if ($result === false) {
			// This is an unrecoverable error; unset the $_SESSION variable and destroy the current session
			session_unset();
			session_destroy();

			// Throw an internal error
			throw new InternalErrorException('Failed to regenerate session identifier.');
		}

		$sessionId = session_id();
		self::$_sessionToken = sha1($sessionId);
		$_SESSION[SessionSettings::getSessionTokenName()] = self::$_sessionToken;

		self::$_sessionId = $sessionId;
	}

	/**
	 * Returns the number of seconds after which data will be seen as 'garbage' and potentially cleaned up.
	 * The value is taken from current ini configuration (session.gc_maxlifetime)
	 *
	 * @return int The number of seconds after which data will be seen as 'garbage' and potentially cleaned up
	 */
	public static function getSessionLifetime()
	{
		self::init();
		return intval(ini_get('session.gc_maxlifetime'));
	}


	private static function validateSession()
	{
		// validateSession is called after init
		//	if $_started is set to true the session_id must return a valid identifier and _sessionToken must be in the $_SESSION super-global

		$sessionTokenName = SessionSettings::getSessionTokenName();

		// simple test
		if (!self::$_started)
			throw new InvalidSessionStateException();

		$sessionId = session_id();
		$sessionToken = sha1($sessionId);

		// Validate session identifier
		if ($sessionId != self::$_sessionId)
		{
			// The session identifier is different. This is possible if session was destroyed using session_destroy outside the scope of the Session class.
			throw new InvalidSessionStateException();
		}

		// Validate session token
		if (!isset($_SESSION[$sessionTokenName]) || $sessionToken != $_SESSION[$sessionTokenName]
				|| $_SESSION[$sessionTokenName] != self::$_sessionToken || $sessionToken != self::$_sessionToken)
		{
			throw new InvalidSessionStateException();
		}
	}

	private static function init()
	{
		// If the session was already initialized we need only to validate it
		if (self::$_started)
		{
			self::validateSession();
			return;
		}

		// ensure the user did not call session_start (as we have $_started set to false)
		$sessionId = session_id();
		if (!empty($sessionId))
		{
			//session_start cannot be called outside the scope of the Session class.
			throw new InvalidSessionStateException();
		}

		// unset the variable to prevent accidental use
		unset($sessionId);

		// change session name; useful if we have multiple web-sites under the same domain
		session_name(SessionSettings::getSessionName());


		// so far, the session is uninitialized -> try to restore the session

		// session_set_cookie_params must be called before session_start
		$params = session_get_cookie_params();
		session_set_cookie_params(0, $params['path'], $params['domain'], !empty($_SERVER['HTTPS']) ? true : false, SessionSettings::getHttpOnlySessionCookie());

		$result = session_start();
		if (!$result) throw new SessionInitializeFailedException();

		// grab the session id
		$sessionId = session_id();
		$sessionTokenName = SessionSettings::getSessionTokenName();

		// check to see if we did restore the session
		// to make this assumption, the $_sessionToken must be present in the session
		$sessionToken = isset($_SESSION[$sessionTokenName]) ? $_SESSION[$sessionTokenName] : null;

		if (empty($sessionToken))
		{
			// a new session was created
			// we need to generate a new session token and initialize class variables
			self::$_sessionToken = sha1($sessionId);
			$_SESSION[$sessionTokenName] = self::$_sessionToken;
		}
		else
		{
			// the session was restored
			// we need to initialize the class variables
			// extra-check: to make sure the session is ours we must also check the sessionToken to match the sha1 of session_id

			if ($sessionToken != sha1($sessionId))
			{
				// The session token is no longer valid.
				throw new InvalidSessionStateException();
			}
			self::$_sessionToken = $sessionToken;
		}
		self::$_sessionId = $sessionId;
		self::$_started = true;
	}
}
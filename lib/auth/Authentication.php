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
final class Authentication
{
	private static $_loginPage = 'login.php';

	/**
	 * @var string The key used to store the authentication token in Session
	 */
	private static $_authTokenName = '__authToken';

	/**
	 * @var string The key used to store the name of the authenticated principal
	 */
	private static $_authUserName = '__authUserName';

	/**
	 * @var MembershipUser The current authenticated user
	 */
	private static $_currentUser = null;

	/**
	 * @var array An array of strings describing the current authenticated user's role
	 */
	private static $_currentUserRoles = null;


	private function __construct() { }


	public static function setLoginPage($pageUrl)
	{
		self::$_loginPage = $pageUrl;
	}

	public static function redirectToLoginPage()
	{
		if (self::$_loginPage === null || self::$_loginPage === '')
			throw new InvalidOperationException('The login page was not set.');

		RequestUtil::redirect(self::$_loginPage, true);
	}

	/**
	 *
	 * @return string Returns the email address of the currently authenticated user. If no user is authenticated then an exception is thrown.
	 */
	public static function getCurrentUserEmail()
	{
		$user = self::getCurrentUser();
		if ($user === null)
			self::throwAuthenticationRequired();
		return $user->getEmail();
	}

	/**
	 * Obtains the user name of the currently authenticated user.
	 *
	 * @return string Returns the user name of the currently authenticated user. If no user is authenticated then an exception is thrown.
	 */
	public static function getCurrentUserName()
	{
		$user = self::getCurrentUser();
		if ($user === null)
			self::throwAuthenticationRequired();
		return $user->getUserName();
	}

	/**
	 *
	 * @return array Returns the roles for the currently authenticated user. If no user is authenticated an exception is thrown.
	 */
	public static function getRolesForCurrentUser()
	{
		// Just verify the user is authenticated
		$user = self::getCurrentUser();
		if ($user === null)
			self::throwAuthenticationRequired();

		if (self::$_currentUserRoles === null) {
			$userName = $user->getUserName();
			$roles = RoleProvider::getRolesForUser($userName);

			self::$_currentUserRoles = array();

			foreach ($roles as $role) {
				self::$_currentUserRoles[] = $role->getName();
			}
		}
		return self::$_currentUserRoles;
	}

	public static function signIn($userName, $password)
	{
		SecUtil::checkStringArgument($userName, 'userName', 20, false, false);
		SecUtil::checkStringArgument($password, 'password', -1, false, false, false);

		if (self::getCurrentUser())
			throw new InvalidOperationException('There is already a user authenticated.');

		$valid = MembershipProvider::validateUser($userName, $password);

		if ($valid === false)
			throw new AuthenticationFailedException();

		try {
			// Obtain user information from data source
			$user = MembershipProvider::getUser($userName, true);

			if ($user == null)
				throw new AuthenticationFailedException();

			$token = self::generateAuthToken();

			$user->setAuthToken($token);
			$user->setLastLoginIp($_SERVER['REMOTE_ADDR']);
			$user->setLastLoginDate(new DateTime());
			MembershipProvider::updateUser($user);

			// Update session information
			Session::set(self::$_authTokenName, $token);
			Session::set(self::$_authUserName, $userName);

			// Update current authentication state
			self::$_currentUser = $user;

			// Regenerate the session ID
			Session::regenerateId();
		}
		catch (Exception $e) {
			throw new AuthenticationFailedException(null, $e);
		}
	}

	public static function signOut()
	{
		$user = self::getCurrentUser();

		if ($user !== null)
		{
			try {
				$user->setAuthToken(null);
				MembershipProvider::updateUser($user);
			}
			catch (Exception $e) {
				// just log the exception
				App::logEvent(LOGGER_LEVEL_ERROR, __METHOD__ . ' - An non-fatal error occurred during user logout.', null, array('exception' => $e));
			}
		}
		self::clearAuthenticationState();

		// Abandon session
		Session::destroy();
	}

	/**
	 * Returns the current authenticated user. If the current principal accessing this method is not
	 * authenticated then an unauthenticated MembershipUser will be returned.
	 *
	 * @return MembershipUser The current principal
	 */
	public static function getCurrentUser()
	{
		$authToken = Session::get(self::$_authTokenName);
		$userName = Session::get(self::$_authUserName);

		if (self::$_currentUser) {
			// Just validate the user name and make sure we have auth token
			if (self::$_currentUser->getUserName() === $userName && $authToken == self::$_currentUser->getAuthToken()) {
				return self::$_currentUser;
			}
			// As this is not likely to happen we clear the authentication state and throw an exception
			self::clearAuthenticationState();

			// The current user appears to be not synchronized with the current session information.
			throw new SessionExpiredException();
		}

		//
		// If we don't have token or user name in session then the current principal is not authenticated
		//
		if (empty($authToken) || empty($userName)) {
			// the user is not authenticated
			self::clearAuthenticationState();
			return null;
		}

		// validate token -> this might fail if the user was signed out from somewhere else / the session expired or simply the DB query fails.
		if ($authToken != MembershipProvider::getAuthToken($userName)) {
			// the token is no longer valid
			self::clearAuthenticationState();
			return null;
		}

		// We have an authenticated user >> update current authenticated user

		self::$_currentUser = MembershipProvider::getUser($userName, true);
		self::$_currentUserRoles = null;

		return self::$_currentUser;
	}

	/**
	 * Removes the authentication information stored in session.
	 */
	private static function clearAuthenticationState()
	{
		Session::remove(self::$_authTokenName);
		Session::remove(self::$_authUserName);

		self::$_currentUser = null;
		self::$_currentUserRoles = null;
	}

	private static function generateAuthToken()
	{
		$salt = md5('!Ansh&kFO.Lk?;03fx' . mt_rand());
		return md5($salt . uniqid(mt_rand(), true));
	}

	private static function throwAuthenticationRequired()
	{
		throw new AccessDeniedException('You must be authenticated to perform this operation.');
	}
}
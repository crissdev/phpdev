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
final class Principal
{
	private $_user;
	private $_roles;


	private function __construct(MembershipUser $user = null, array $roles = null)
	{
		$this->_user = $user;
		$this->_roles = $roles;
	}

	/**
	 *
	 * @return bool Returns whether or not the current user is authenticated.
	 */
	public function isAuthenticated()
	{
		return $this->_user != null;
	}

	/**
	 *
	 * @return string Returns the name of the currently authenticated user. If not user is authenticated then empty string is returned.
	 */
	public function getName()
	{
		return $this->_user ? $this->_user->getUserName() : '';
	}

	/**
	 *
	 * @param string $roleName
	 * @return bool Returns whether or not the currently authenticated user is in the specified role. If no user is authenticated then false is returned.
	 */
	public function isInRole($roleName)
	{
		return $this->_roles ? in_array($roleName, $this->_roles) : false;
	}

	/**
	 * Obtains the current authenticated user that was passed to the constructor of this class.
	 *
	 * @return MembershipUser Returns the currently authenticated user. If no user if authenticated then null is returned.
	 */
	public function getUser()
	{
		return $this->_user;
	}

	/**
	 *
	 * @return Principal Returns a principal object that represents the currently authenticated user.
	 */
	public static function getCurrent()
	{
		$user = Authentication::getCurrentUser();
		$roles = null;

		if ($user !== null) {
			$roles = Authentication::getRolesForCurrentUser();
		}
		return new Principal($user, $roles);
	}
}
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
class UserRpc extends RemoteObject
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getCurrentUser()
	{
		$principal = $this->getCurrentPrincipal();

		return array(
			'name' => $principal->getName(),
			'auth' => $principal->isAuthenticated()
		);
	}

	public static function signIn($userName, $password)
	{
		Authentication::signIn($userName, $password);
	}

	public static function register($userName, $email, $firstName, $lastName, $password)
	{
		SecUtil::checkStringArgument($password, 'password', 32, false, false, false);
		MembershipProvider::createUser($userName, $email, $firstName, $lastName, $password, false);
	}

	public function getUserProfile()
	{
		$this->requireAuthenticatedUser();

		$currentUser = $this->getCurrentPrincipal()->getUser();

		return array(
			'userName' => $currentUser->getUserName(),
			'email' => $currentUser->getEmail(),
			'firstName' => $currentUser->getFirstName(),
			'lastName' => $currentUser->getLastName()
		);
	}

	public function updateUserProfile($email, $firstName, $lastName)
	{
		$this->requireAuthenticatedUser();

		$currentUser = $this->getCurrentPrincipal()->getUser();

		$currentUser->setEmail($email);
		$currentUser->setFirstName($firstName);
		$currentUser->setLastName($lastName);

		MembershipProvider::updateUser($currentUser);
	}
}

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
class MembershipUser
{
	private $_id;
	private $_userName;
	private $_email;
	private $_firstName;
	private $_lastName;
	private $_joinDate;
	private $_lastLoginDate;
	private $_lastLoginIp;
	private $_lastActivityDate;
	private $_lockedOut;
	private $_lockedOutMessage;
	private $_lockedOutDate;
	private $_authToken;
	private $_builtin;


	public function __construct($id, $userName, $email, $firstName, $lastName, DateTime $joinDate, $lastLoginDate, $lastLoginIp, $lastActivityDate, $lockedOut, $lockedOutDate, $lockedOutMessage, $authToken, $builtIn)
	{
		SecUtil::checkIntegerArgument($id, 'id', false, 1);
		SecUtil::checkUserName($userName, 'userName');
		SecUtil::checkEmailAddress($email, 'email');
		SecUtil::checkStringArgument($firstName, 'firstName', 60, true, true, true);
		SecUtil::checkStringArgument($lastName, 'lastName', 30, true, true, true);
		SecUtil::checkDateTimeArgument($joinDate, 'joinDate', false);
		SecUtil::checkDateTimeArgument($lastLoginDate, 'lastLoginDate', true);
		SecUtil::checkStringArgument($lastLoginIp, 'lastLoginIp', 15, true, true, true);
		SecUtil::checkDateTimeArgument($lastActivityDate, 'lastActivityDate', true);
		SecUtil::checkBooleanArgument($lockedOut, 'lockedOut', false);
		SecUtil::checkDateTimeArgument($lockedOutDate, 'lockedOutDate', true);
		SecUtil::checkStringArgument($lockedOutMessage, 'lockedOutMessage', 255, true, true, true);
		SecUtil::checkStringArgument($authToken, 'authToken', 32, true, false, false);
		SecUtil::checkBooleanArgument($builtIn, 'builtIn', false);

		$this->_id = $id;
		$this->_userName = $userName;
		$this->_email = $email;
		$this->_firstName = $firstName;
		$this->_lastName = $lastName;
		$this->_joinDate = clone $joinDate;
		$this->_lastLoginDate = ($lastLoginDate !== null) ? clone $lastLoginDate : null;
		$this->_lastLoginIp = $lastLoginIp;
		$this->_lastActivityDate = ($lastActivityDate !== null) ? clone $lastActivityDate : null;
		$this->_lockedOut = $lockedOut;
		$this->_lockedOutDate = ($lockedOutDate !== null) ? clone $lockedOutDate : null;
		$this->_lockedOutMessage = $lockedOutMessage;
		$this->_authToken = $authToken;
		$this->_builtin = $builtIn;
	}


	public function getId()
	{
		return $this->_id;
	}

	public function getUserName()
	{
		return $this->_userName;
	}

	public function getEmail()
	{
		return $this->_email;
	}

	public function getFirstName()
	{
		return $this->_firstName;
	}

	public function setFirstName($value)
	{
		SecUtil::checkStringArgument($value, 'value', 60, true, true, true);
		$this->_firstName = $value;
	}

	public function getLastName()
	{
		return $this->_lastName;
	}

	public function setLastName($value)
	{
		SecUtil::checkStringArgument($value, 'value', 30, true, true, true);
		$this->_lastName = $value;
	}

	public function getJoinDate()
	{
		return clone $this->_joinDate;
	}

	public function getLastLoginDate()
	{
		if ($this->_lastLoginDate !== null)
			return clone $this->_lastLoginDate;
		return null;
	}

	public function setLastLoginDate(DateTime $value)
	{
		$this->_lastLoginDate = ($value !== null) ? clone $value : null;
	}

	public function getLastLoginIp()
	{
		return $this->_lastLoginIp;
	}

	public function setLastLoginIp($value)
	{
		SecUtil::checkIpv4Address($value, 'value', true, false);
		$this->_lastLoginIp = $value;
	}

	public function getLastActivityDate()
	{
		if ($this->_lastActivityDate !== null)
			return $this->_lastActivityDate;
		return null;
	}

	public function setLastActivityDate(DateTime $value)
	{
		$this->_lastActivityDate = ($value !== null) ? clone $value : null;
	}

	public function isLockedOut()
	{
		return $this->_lockedOut;
	}

	public function getLockedOutMessage()
	{
		return $this->_lockedOutMessage;
	}

	public function getLockedOutDate()
	{
		if ($this->_lockedOutDate !== null)
			return clone $this->_lockedOutDate;
		return null;
	}

	public function getAuthToken()
	{
		return $this->_authToken;
	}

	public function setAuthToken($value)
	{
		SecUtil::checkStringArgument($value, 'value', 32, true, false, false);
		$this->_authToken = $value;
	}

	public function isBuiltin()
	{
		return $this->_builtin;
	}

	public function isOnline()
	{
		if ($this->_lastActivityDate === null)
			return false;

		$localCheck = false;

		if ($localCheck) {

			// TODO: If the WEB server time is different than the DB server time then the following approach might not be the best choice.
			$lifetime = MembershipSettings::getUserOnlineTimeWindow();

			if (date_add(clone $this->_lastActivityDate, new DateInterval("PT{$lifetime}m")) >= date_create()) {
				return true;
			}
			return false;
		}
		return MembershipProvider::isOnline($this->getUserName());
	}


	// Specific methods

	public function changePassword($oldPassword, $newPassword)
	{
		if (MembershipProvider::changePassword($this->getUserName(), $oldPassword, $newPassword)) {
			$this->updateSelf();
			return true;
		}
		return false;
	}

	public function resetPassword()
	{
		$newPassword = MembershipProvider::resetPassword($this->getUserName());
		$this->updateSelf();
		return $newPassword;
	}

	public function getPassword()
	{
		return MembershipProvider::getPassword($this->getUserName());
	}

	public function lockUser($reason)
	{
		if(MembershipProvider::lockUser($this->getUserName(), $reason)) {
			$this->updateSelf();
			return $this->isLockedOut();
		}
		return false;
	}

	public function unlockUser()
	{
		if (MembershipProvider::unlockUser($this->getUserName())) {
			$this->updateSelf();
			return !$this->isLockedOut();
		}
		return false;
	}

	private function updateSelf()
	{
		$user = MembershipProvider::getUser($this->_userName, false);

		if ($user !== null)
		{
			$this->_id = $user->getId();
			$this->_userName = $user->getUserName();
			$this->_email = $user->getEmail();
			$this->_firstName = $user->getFirstName();
			$this->_lastName = $user->getLastName();
			$this->_joinDate = $user->getJoinDate();
			$this->_lastLoginDate = $user->getLastLoginDate();
			$this->_lastLoginIp = $user->getLastLoginIp();
			$this->_lastActivityDate = $user->getLastActivityDate();
			$this->_lockedOut = $user->isLockedOut();
			$this->_lockedOutDate = $user->getLockedOutDate();
			$this->_lockedOutMessage = $user->getLockedOutMessage();
			$this->_builtin = $user->isBuiltin();
		}
	}
}
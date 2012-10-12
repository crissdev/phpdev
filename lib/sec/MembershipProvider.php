<?php

class MembershipProvider
{
	private function __construct()
	{
	}

	/**
	 * Adds a new membership user to the data source.
	 *
	 * @param string $userName The user name for the new user.
	 * @param string $email The e-mail address for the new user.
	 * @param string $firstName
	 * @param string $lastName
	 * @param string $password The password for the new user.
	 * @param bool $builtin
	 * @throws MembershipUserAlreadyRegistered
	 * @throws MembershipEmailAlreadyRegistered
	 * @throws DbQueryFailedException
	 * @return MembershipUser A MembershipUser object populated with the information for the newly created user.
	 */
	public static function createUser($userName, $email, $firstName, $lastName, $password, $builtin)
	{
		SecUtil::checkUserName($userName, 'userName');
		SecUtil::checkEmailAddress($email, 'email', 128, false, false, MembershipSettings::getCheckEmailDomain());
		SecUtil::checkStringArgument($firstName, 'firstName', 60, true, true, true);
		SecUtil::checkStringArgument($lastName, 'lastName', 30, true, true, true);
		SecUtil::checkStringArgument($password, 'password', 32, false, false, false);
		SecUtil::checkBooleanArgument($builtin, 'builtIn', false);

		if (self::userExists($userName))
			throw new MembershipUserAlreadyRegistered();

		// Check for email address
		if (MembershipSettings::getRequireUniqueEmail())
		{
			$db = self::getMembershipDatabase();
			$query = sprintf('select count(0) from %s where email = $1::character varying(128)', self::getQualifiedTableName('users'));
			$result = $db->executeScalar($query, array($email));
			$totalRecords = $db->parseDbValue($result, DB_TYPE_INT, false);

			if ($totalRecords > 0)
				throw new MembershipEmailAlreadyRegistered();

			unset($query, $totalRecords, $result);
		}


		$query = sprintf('
insert into %s(
	user_name, email, first_name, last_name, join_date, "password", password_salt, locked_out, builtin)
values(
	$1::character varying(20),		-- user_name
	$2::character varying(128),		-- email
	$3::character varying(60),		-- first_name
	$4::character varying(30),		-- last_name
	$5::timestamp without time zone, -- join_date
	$6::character varying(32),		-- password
	$7::character varying(32),		-- password salt
	false,							-- locked_out
	$8::boolean						-- builtin
)'
, self::getQualifiedTableName('users'));

		$salt = null;
		$hash = self::generatePasswordHash($password, $salt);
		$params = array($userName, $email, $firstName, $lastName, date('Y-m-d H:i:s'), $hash, $salt, $builtin ? 'true' : 'false');

		$rowsAffected = self::getMembershipDatabase()->executeNonQuery($query, $params);

		if ($rowsAffected <= 0)
			throw new DbQueryFailedException("The user '$userName' could not be added to the data source.");

		$user = self::getUser($userName);
		return $user;
	}

	public static function deleteUser($userName)
	{
		SecUtil::checkUserName($userName, 'userName');

		$query = sprintf('delete from %s where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));
		$rowsAffected = self::getMembershipDatabase()->executeNonQuery($query, array($userName));

		return $rowsAffected > 0;
	}


	public static function findUsersByEmail($email, $pageIndex, $pageSize, &$totalRecords)
	{
		SecUtil::checkIntegerArgument($pageIndex, 'pageIndex', false);
		SecUtil::checkIntegerArgument($pageSize, 'pageSize', false);

		$db = self::getMembershipDatabase();

		$query = sprintf('select count(0) from %s where email = $1::character varying(128)', self::getQualifiedTableName('users'));
		$params = array($email);

		$result = $db->executeScalar($query, $params);
		$totalRecords = $db->parseDbValue($result, DB_TYPE_INT, false);

		$query = sprintf('select "user_id", "user_name", "email", "first_name", "last_name", "join_date", "last_login_date", "last_login_ip", "last_activity_date", ' .
							'"locked_out", "locked_out_date", "locked_out_message", "auth_token", "builtin" from %s where email = $1::character varying(128) order by user_name offset %d limit %s', self::getQualifiedTableName('users'), $pageIndex, $pageSize);
		$list = self::getMembershipDatabase()->loadArray($query, $params, true);
		$users = array();

		foreach ($list as &$dataRow) {
			$users[] = self::fromDataRow($dataRow);
		}
		return $users;
	}

	public static function findUsersByName($userName, $pageIndex, $pageSize, &$totalRecords)
	{
		SecUtil::checkIntegerArgument($pageIndex, 'pageIndex', false);
		SecUtil::checkIntegerArgument($pageSize, 'pageSize', false);

		$db = self::getMembershipDatabase();

		$query = sprintf('select count(0) from %s where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));
		$params = array($userName);

		$result = $db->executeScalar($query, $params);
		$totalRecords = $db->parseDbValue($result, DB_TYPE_INT, false);

		$query = sprintf('select "user_id", "user_name", "email", "first_name", "last_name", "join_date", "last_login_date", "last_login_ip", "last_activity_date", ' .
							'"locked_out", "locked_out_date", "locked_out_message", "auth_token", "builtin" from %s where user_name = $1::character varying(20) order by user_name offset %d limit %s', self::getQualifiedTableName('users'), $pageIndex, $pageSize);
		$list = self::getMembershipDatabase()->loadArray($query, $params, true);
		$users = array();

		foreach ($list as &$dataRow) {
			$users[] = self::fromDataRow($dataRow);
		}
		return $users;
	}


	public static function getAllUsers($pageIndex, $pageSize, &$totalRecords)
	{
		SecUtil::checkIntegerArgument($pageIndex, 'pageIndex', false);
		SecUtil::checkIntegerArgument($pageSize, 'pageSize', false);

		$db = self::getMembershipDatabase();

		$query = sprintf('select count(0) from %s', self::getQualifiedTableName('users'));
		$result = $db->executeScalar($query);
		$totalRecords = $db->parseDbValue($result, DB_TYPE_INT, false);

		$query = sprintf('select "user_id", "user_name", "email", "first_name", "last_name", "join_date", "last_login_date", "last_login_ip", "last_activity_date", ' .
							'"locked_out", "locked_out_date", "locked_out_message", "auth_token", "builtin" from %s order by user_name offset %d limit %s', self::getQualifiedTableName('users'), $pageIndex, $pageSize);
		$list = self::getMembershipDatabase()->loadArray($query, null, true);
		$users = array();

		foreach ($list as &$dataRow) {
			$users[] = self::fromDataRow($dataRow);
		}
		return $users;
	}

	public static function getNumberOfUsersOnline()
	{
		$lifetime = MembershipSettings::getUserOnlineTimeWindow();
		$query = sprintf('select count(0) from %s where locked_out = false and auth_token is not null and (last_activity_date + interval \'%d minute\') >= $1::timestamp without time zone',
							self::getQualifiedTableName('users'), $lifetime);

		$db = self::getMembershipDatabase();
		$result = $db->executeScalar($query, array(date('Y-m-d H:i:s')));
		$value = $db->parseDbValue($result, DB_TYPE_INT, false);

		return $value;
	}


	/**
	 * Gets information from the data source for a user. <br/>
	 * Provides an option to update the last-activity date/time stamp for the user.
	 *
	 * @param string $userName The name of the user to get information for.
	 * @param bool $userIsOnline true to update the last-activity date/time stamp for the user; <br/>false to return user information without updating the last-activity date/time stamp for the user.
	 * @return MembershipUser A MembershipUser object populated with the specified user's information from the data source.
	 */
	public static function getUser($userName, $userIsOnline = false)
	{
		SecUtil::checkUserName($userName, 'userName');
		SecUtil::checkBooleanArgument($userIsOnline, 'userIsOnline', false);

		$db = self::getMembershipDatabase();

		if ($userIsOnline)
		{
			if (!MembershipProvider::userExists($userName))
				throw new MembershipUserNotFoundException();

			$query = sprintf('update %s set last_activity_date = $2::timestamp without time zone where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));
			$rowsAffected = $db->executeNonQuery($query, array($userName, date('Y-m-d H:i:s')));

			if ($rowsAffected <= 0)
				throw new DbQueryFailedException();
		}

		$query = sprintf('select user_id, user_name, email, first_name, last_name, join_date, last_login_date, last_login_ip, last_activity_date, locked_out, locked_out_date, locked_out_message, auth_token, builtin from %s where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));
		$list = $db->loadArray($query, array($userName));

		$user = null;

		if (count($list) > 0)
			$user = self::fromDataRow($list[0]);

		return $user;
	}

	/**
	 * Gets user information from the data source based on the unique identifier for the membership user.<br/>
	 * Provides an option to update the last-activity date/time stamp for the user.
	 *
	 * @param int $userId The unique identifier for the membership user to get information for.
	 * @param bool $userIsOnline true to update the last-activity date/time stamp for the user; <br/>false to return user information without updating the last-activity date/time stamp for the user.
	 * @throws MembershipUserNotFoundException
	 * @return MembershipUser A MembershipUser object populated with the specified user's information from the data source.
	 */
	public static function getUserById($userId, $userIsOnline)
	{
		SecUtil::checkIntegerArgument($userId, 'id', false, 1);
		SecUtil::checkBooleanArgument($userIsOnline, 'userIsOnline', false);

		$db = self::getMembershipDatabase();
		$params = array($userId);

		$query = sprintf('select user_id, user_name, email, first_name, last_name, join_date, last_login_date, last_login_ip, last_activity_date, locked_out, locked_out_date, locked_out_message, auth_token, builtin from %s where user_id = $1::int', self::getQualifiedTableName('users'));
		$dataRow = $db->loadRow($query, $params);

		if ($dataRow === null)
			throw new MembershipUserNotFoundException();

		$user = self::fromDataRow($dataRow);

		if ($userIsOnline)
		{
			$user->setLastActivityDate(new DateTime('now'));
			self::updateUser($user);
		}
		return $user;
	}

	/**
	 * Gets the user name associated with the specified e-mail address.
	 *
	 * @param string $email The e-mail address to search for.
	 * @throws NotSupportedException
	 * @return string The user name associated with the specified e-mail address. If no match is found, return null.
	 */
	public static function getUserNameByEmail($email)
	{
		SecUtil::checkEmailAddress($email, 'email');

		// This operation is valid only if uniqueness of email address is ensured
		if (!MembershipSettings::getRequireUniqueEmail()) {
			throw new NotSupportedException('Email address uniqueness is required for this operation.');
		}

		$query = sprintf('select user_name from %s where email = $1::character varying(128)', self::getQualifiedTableName('users'));
		$db = self::getMembershipDatabase();
		$result = $db->executeScalar($query, array($email));

		$userName = $db->parseDbValue($result, DB_TYPE_STRING, true);
		return $userName;

	}


	public static function getPassword($userName)
	{
		throw new NotSupportedException('Password retrieval is not supported.');
	}

	/**
	 * Resets a user's password to a new, automatically generated/specified password.
	 *
	 * @param string $userName The user to reset the password for.
	 * @param string $newPassword [optional] The new password for the specified user.
	 * @throws MembershipUserNotFoundException
	 * @return bool The new password for the specified user.
	 */
	public static function resetPassword($userName, &$newPassword = null)
	{
		SecUtil::checkUserName($userName, 'userName');

		if ($newPassword !== null)
			SecUtil::checkStringArgument($newPassword, 'newPassword', -1, false, false, false);

		if ($newPassword === null) {
			$newPassword = substr(self::generatePasswordSalt(), 0, 12);
		}

		$newSalt = null;
		$newHash = self::generatePasswordHash($newPassword, $newSalt);

		$query = sprintf('update %s set "password" = $2::character varying(32), password_salt = $3::character varying(32) where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));
		$params = array($userName, $newHash, $newSalt);

		$db = self::getMembershipDatabase();
		$rowsAffected = $db->executeNonQuery($query, $params);

		if ($rowsAffected <= 0)
			throw new MembershipUserNotFoundException();

		return $newPassword;
	}

	/**
	 * Changes the current password with a new password for the specified user.<br/>
	 * <b>Note: </b>The password salt is also updated.
	 *
	 * @param string $userName The user name for which to change the password.
	 * @param string $oldPassword The current password.
	 * @param string $newPassword The new password.
	 * @return bool true, if the password was changed, false, otherwise.
	 */
	public static function changePassword($userName, $oldPassword, $newPassword)
	{
		SecUtil::checkUserName($userName, 'userName');
		SecUtil::checkStringArgument($oldPassword, 'oldPassword', -1, false, false, false);
		SecUtil::checkStringArgument($newPassword, 'newPassword', -1, false, false, false);

		$db = self::getMembershipDatabase();
		$query = sprintf('select password_salt from %s where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));
		$result = $db->executeScalar($query, array($userName));
		$oldSalt = $db->parseDbValue($result, DB_TYPE_STRING, false);

		// Redundant but worth the effort
		if ($oldSalt === null)
			return false;

		$newSalt = null;
		$newHash = self::generatePasswordHash($newPassword, $newSalt);
		$oldHash = self::generatePasswordHash($oldPassword, $oldSalt);

		$params = array($userName, $newHash, $newSalt, $oldHash, $oldSalt);
		$query = sprintf('
update %s
set
	"password"			= $2::character varying(32),
	password_salt		= $3::character varying(32)
where
	user_name			= $1::character varying(20)
	and "password"		= $4::character varying(32)
	and password_salt	= $5::character varying(32)
', self::getQualifiedTableName('users'));

		$rowsAffected = $db->executeNonQuery($query, $params);
		return $rowsAffected > 0;
	}

	/**
	 * Sets a lock so that the membership user cannot be validated.
	 *
	 * @param string $userName The membership user whose lock status you want to set.
	 * @param string $reason A message describing the reason of locking the user.
	 * @return bool true if the membership user was successfully locked; otherwise, false.
	 */
	public static function lockUser($userName, $reason)
	{
		SecUtil::checkUserName($userName, 'userName');
		SecUtil::checkStringArgument($reason, 'reason', 255);

		$query = sprintf('update %s set locked_out = true, locked_out_message = $2::character varying(255), locked_out_date = $3::timestamp without time zone where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));
		$params = array($userName, $reason, date('Y-m-d H:i:s'));

		$rowsAffected = self::getMembershipDatabase()->executeNonQuery($query, $params);
		return $rowsAffected > 0;
	}

	/**
	 * Clears a lock so that the membership user can be validated.
	 *
	 * @param string $userName The membership user whose lock status you want to clear.
	 * @return bool true if the membership user was successfully unlocked; otherwise, false.
	 */
	public static function unlockUser($userName)
	{
		SecUtil::checkUserName($userName, 'userName');

		$query = sprintf('update %s set locked_out = false, locked_out_message = null where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));
		$rowsAffected = self::getMembershipDatabase()->executeNonQuery($query, array($userName));
		return $rowsAffected > 0;
	}

	/**
	 * Checks whether or not the membership user is locked or not.
	 *
	 * @param string $userName The membership user whose lock status should be retrieved.
	 * @param string $reason The locked out message set if the user is locked.
	 * @return bool true, the user is locked - $reason will contain a description of why the user is locked, false, otherwise.
	 */
	public static function isLocked($userName, &$reason = null)
	{
		SecUtil::checkUserName($userName, 'userName');

		$reason = null;
		$query = sprintf('select locked_out, locked_out_message from %s where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));

		$db = self::getMembershipDatabase();
		$list = $db->loadArray($query, array($userName));

		if (count($list) > 0) {
			$dataRow = $list[0];
			$lockedOut = $db->parseDbValue($dataRow['locked_out'], DB_TYPE_BOOL, false);

			if ($lockedOut) {
				$reason = $db->parseDbValue($dataRow['locked_out_message'], DB_TYPE_STRING, true);
				return true;
			}
		}
		return false;
	}


	public static function updateUser(MembershipUser $user)
	{
		$query = sprintf('update %s set first_name = $2::character varying(60), last_name = $3::character varying(30), last_activity_date = $4::timestamp without time zone, last_login_ip = $5::character varying(15), auth_token = $6::character varying(32), last_login_date = $7::timestamp without time zone where user_id = $1::int', self::getQualifiedTableName('users'));
		$params = array($user->getId(), $user->getFirstName(), $user->getLastName(), $user->getLastActivityDate() === null ? null : $user->getLastActivityDate()->format('Y-m-d H:i:s'), $user->getLastLoginIp(), $user->getAuthToken(), $user->getLastLoginDate() ? $user->getLastLoginDate()->format('Y-m-d H:i:s') : null);

		$db = self::getMembershipDatabase();
		$rowsAffected = $db->executeNonQuery($query, $params);

		if ($rowsAffected <= 0)
			throw new MembershipUserNotFoundException();
	}

	/**
	 * Verifies that the specified user name and password exist in the data source.
	 * This method also updates the last-activity date/time stamp for the user.
	 *
	 * @param string $userName The name of the user to validate.
	 * @param string $password The password for the specified user.
	 * @return bool true if the specified username and password are valid; otherwise, false.
	 */
	public static function validateUser($userName, $password)
	{
		SecUtil::checkUserName($userName, 'userName');
		SecUtil::checkStringArgument($password, 'password', -1, false, false, false);

		$db = self::getMembershipDatabase();
		$query = sprintf('select "password_salt" from %s where "user_name" = $1::character varying(20)', self::getQualifiedTableName('users'));
		$result = $db->executeScalar($query, array($userName));
		$salt = $db->parseDbValue($result, DB_TYPE_STRING, false);

		// Redundant but worths the effort
		if ($salt === null)
			return false;

		$query = sprintf('update %s set last_activity_date = $4::timestamp without time zone where user_name = $1::character varying(20) and "password" = $2::character varying(32) and password_salt = $3::character varying(32) and locked_out = false', self::getQualifiedTableName('users'));

		$hash = self::generatePasswordHash($password, $salt);
		$params = array($userName, $hash, $salt, date('Y-m-d H:i:s'));

		$rowsAffected = $db->executeNonQuery($query, $params);
		return $rowsAffected > 0;
	}


	public static function isOnline($userName)
	{
		SecUtil::checkUserName($userName, 'userName');

		$db = self::getMembershipDatabase();
		$query = sprintf('select 1 from %s where user_name = $1::character varying(20) and locked_out = false and auth_token is not null and (last_activity_date + interval \'%d minute\') >= $2::timestamp without time zone', self::getQualifiedTableName('users'), MembershipSettings::getUserOnlineTimeWindow());
		$params = array($userName, date('Y-m-d H:i:s'));

		$result = $db->executeScalar($query, $params);
		$value = $db->parseDbValue($result, DB_TYPE_INT, true);

		return $value === 1;
	}

	/**
	 * Checks if a user exists in the data source.
	 *
	 * @param string $userName The name of the user to check.
	 * @return bool true, if the user exists, false, otherwise.
	 */
	public static function userExists($userName)
	{
		SecUtil::checkUserName($userName, 'userName');

		$db = self::getMembershipDatabase();
		$query = sprintf('select 1 from %s where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));

		$result = $db->executeScalar($query, array($userName));
		$value = $db->parseDbValue($result, DB_TYPE_INT, true);

		return $value === 1;
	}

	public static function getAuthToken($userName)
	{
		SecUtil::checkUserName($userName, 'userName');

		if (!self::userExists($userName))
			throw new MembershipUserNotFoundException();

		$db = self::getMembershipDatabase();
		$query = sprintf('select auth_token from %s where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));

		$result = $db->executeScalar($query, array($userName));
		$value = $db->parseDbValue($result, DB_TYPE_STRING, true);

		return $value;
	}

	public static function setAuthToken($userName, $token)
	{
		SecUtil::checkUserName($userName, 'userName');
		SecUtil::checkStringArgument($token, 'token', 50, true, false, false);

		if (!self::userExists($userName))
			throw new MembershipUserNotFoundException();

		$db = self::getMembershipDatabase();
		$query = sprintf('update %s set auth_token = $2::character varying(50) where user_name = $1::character varying(20)', self::getQualifiedTableName('users'));
		$params = array($userName, $token);

		$rowsAffected = $db->executeNonQuery($query, $params);
		return $rowsAffected > 0;
	}

	// Helper methods

	private static function generatePasswordSalt()
	{
		// 20 - 23 characters long
		return uniqid(mt_rand(), false);
	}

	private static function generatePasswordHash($password, &$salt)
	{
		SecUtil::checkStringArgument($password, 'password', -1, false, false, false);

		if ($salt !== null)
			SecUtil::checkStringArgument($salt, 'salt', 32, false, false, false);

		if ($salt === null) {
			// a new salt must be generated
			$salt = self::generatePasswordSalt();
		}
		return md5($salt.md5($password));
	}

	/**
	 * @static
	 * @param $dataRow
	 * @return MembershipUser
	 */
	private static function fromDataRow($dataRow)
	{
		$db = self::getMembershipDatabase();
		$userId				= $db->parseDbValue($dataRow['user_id'], DB_TYPE_INT, false);
		$userName			= $db->parseDbValue($dataRow['user_name'], DB_TYPE_STRING, false);
		$email				= $db->parseDbValue($dataRow['email'], DB_TYPE_STRING, false);
		$firstName			= $db->parseDbValue($dataRow['first_name'], DB_TYPE_STRING, false);
		$lastName			= $db->parseDbValue($dataRow['last_name'], DB_TYPE_STRING, false);
		$joinDate			= $db->parseDbValue($dataRow['join_date'], DB_TYPE_DATE, false);
		$lastLoginDate		= $db->parseDbValue($dataRow['last_login_date'], DB_TYPE_DATE, true);
		$lastLoginIp		= $db->parseDbValue($dataRow['last_login_ip'], DB_TYPE_STRING, true);
		$lastActivityDate	= $db->parseDbValue($dataRow['last_activity_date'], DB_TYPE_DATE, true);
		$lockedOut			= $db->parseDbValue($dataRow['locked_out'], DB_TYPE_BOOL, false);
		$lockedOutDate		= $db->parseDbValue($dataRow['locked_out_date'], DB_TYPE_DATE, true);
		$lockedOutMessage	= $db->parseDbValue($dataRow['locked_out_message'], DB_TYPE_STRING, true);
		$authToken			= $db->parseDbValue($dataRow['auth_token'], DB_TYPE_STRING, true);
		$builtin			= $db->parseDbValue($dataRow['builtin'], DB_TYPE_BOOL, false);
		$user = new MembershipUser($userId, $userName, $email, $firstName, $lastName, $joinDate, $lastLoginDate, $lastLoginIp,
				$lastActivityDate, $lockedOut, $lockedOutDate, $lockedOutMessage, $authToken, $builtin);

		return $user;
	}

	/**
	 *
	 * @return Database
	 */
	private static function getMembershipDatabase()
	{
		$name = MembershipSettings::getDbSettingName();
		$database = DatabaseFactory::getDatabase($name);
		return $database;
	}

	private static function getQualifiedTableName($tableName)
	{
		$schemaName = MembershipSettings::getSchemaName();
		$qualifiedName = sprintf('"%s"."%s"', $schemaName, $tableName);
		return $qualifiedName;
	}
}
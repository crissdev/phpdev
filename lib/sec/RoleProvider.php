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
class RoleProvider
{
	public static function createRole($roleName, $description, $builtIn)
	{
		SecUtil::checkRoleName($roleName, 'roleName');
		SecUtil::checkStringArgument($description, 'description', 255, true, true, true);
		SecUtil::checkBooleanArgument($builtIn, 'builtIn', false);

		if (self::roleExists($roleName))
			throw new ArgumentException('There is already a role with this name.', 'roleName');

		$query = sprintf('insert into %s("role_name", "role_description", "builtin") values($1::character varying(60), $2::character varying(255), $3::boolean)', self::getQualifiedTableName('roles'));
		$params = array($roleName, $description, $builtIn ? 'true' : 'false');

		$rowsAffected = self::getMembershipDatabase()->executeNonQuery($query, $params);

		unset($query, $params);

		if ($rowsAffected <= 0)
			throw new DbQueryFailedException('The role could not be added to the data source.');

		$role = self::getRole($roleName);
		return $role;
	}

	public static function deleteRole($roleName)
	{
		SecUtil::checkRoleName($roleName, 'roleName');

		$query = sprintf('delete from %s where "role_name" = $1::character varying(60)', self::getQualifiedTableName('roles'));
		$params = array($roleName);
		$rowsAffected = self::getMembershipDatabase()->executeNonQuery($query, $params);

		unset($query, $params);

		return $rowsAffected > 0;
	}

	public static function roleExists($roleName)
	{
		SecUtil::checkRoleName($roleName, 'roleName');

		$query = sprintf('select 1 from %s where "role_name" = $1::character varying(60)', self::getQualifiedTableName('roles'));
		$params = array($roleName);
		$result = self::getMembershipDatabase()->executeScalar($query, $params);

		unset($query, $params);

		return $result !== null;
	}

	public static function getAllRoles()
	{
		$query = sprintf('select role_id, role_name, role_description, builtin from %s', self::getQualifiedTableName('roles'));
		$list = self::getMembershipDatabase()->loadArray($query, null, true);
		$roles = array();

		foreach ($list as &$dataRow) {
			$roles[] = self::fromDataRow($dataRow);
		}
		unset($query, $list);

		return $roles;
	}

	public static function getRole($roleName)
	{
		SecUtil::checkRoleName($roleName, 'roleName');

		$query = sprintf('select role_id, role_name, role_description, builtin from %s where role_name = $1::character varying(60)', self::getQualifiedTableName('roles'));
		$params = array($roleName);
		$list = self::getMembershipDatabase()->loadArray($query, $params, true);
		$dataRow = count($list) == 1 ? $list[0] : null;
		$role = null;

		if ($dataRow) {
			$role = self::fromDataRow($dataRow);
		}
		return $role;
	}

	public static function getRolesForUser($userName)
	{
		SecUtil::checkUserName($userName, 'userName');

		$query = sprintf('
select
	r."role_id",
	r."role_name",
	r."role_description",
	r."builtin"
from
	%s as r
join
	%s as ur on ur."role_id" = r."role_id"
join
	%s as u on u."user_id" = ur."user_id"
where
	u."user_name" = $1::character varying(20)', self::getQualifiedTableName('roles'), self::getQualifiedTableName('users_roles'), self::getQualifiedTableName('users'));
		$params = array($userName);
		$list = self::getMembershipDatabase()->loadArray($query, $params, true);
		$roles = array();

		foreach ($list as &$dataRow) {
			$roles[] = self::fromDataRow($dataRow);
		}
		return $roles;
	}

	public static function updateRole(Role $role)
	{
		if ($role === null)
			throw new ArgumentNullException(null, 'role');

		$query = sprintf('update %s set "role_name" = $2::character varying(60), "role_description" = $3::character varying(255), "builtin" = $4::boolean where "role_id" = $1::serial', self::getQualifiedTableName('roles'));
		$params = array($role->getId(), $role->getName(), $role->getDescription(), $role->isBuiltin() ? 'true' : 'false');

		$rowsAffected = self::getMembershipDatabase()->executeNonQuery($query, $params);

		if ($rowsAffected <= 0)
			throw new DbQueryFailedException('The role could not be updated.');
	}

	public static function addUserToRoles($userName, array $roles)
	{
		SecUtil::checkUserName($userName, 'userName');
		SecUtil::checkArrayArgument($roles, 'roles', false, false);

		if (!MembershipProvider::userExists($userName))
			throw new DbQueryFailedException("The user '$userName' does not exist in the data source.");

		$query = '
insert into %s("user_id", "role_id")
select
	u."user_id", r."role_id"
from
	%s as u,
	%s as r
where
	u."user_name" = $1::character varying(20) and (';

		$first = true;
		$index = 2;
		foreach ($roles as &$roleName) {
			SecUtil::checkRoleName($roleName, 'roleName');
			if (!$first) $query .= ' or ';

			$query .= ' r."role_name" = $' . $index . '::character varying(60)';

			$first = false;
			$index++;
		}
		$query .= ')';

		$query = sprintf($query, self::getQualifiedTableName('users_roles'), self::getQualifiedTableName('users'), self::getQualifiedTableName('roles'));
		$params = array_merge(array($userName), $roles);

		$db = self::getMembershipDatabase();
		$db->beginTransaction();
		try
		{
			$rowsAffected = $db->executeNonQuery($query, $params);

			if ($rowsAffected < count($roles))
			{
				if (!MembershipProvider::userExists($userName)) {
					throw new DbQueryFailedException("The user '$userName' does not exist in the data source.");
				}
				throw new DbQueryFailedException('Not all roles specified exist in the data source.');
			}
			if ($rowsAffected <= 0)
				throw new DbQueryFailedException('No rows were affected by the query.');

			$db->commitTransaction();

			return $rowsAffected;
		}
		catch (Exception $e) {
			$db->rollbackTransaction();
			throw $e;
		}
	}

	public static function removeUserFromRoles($userName, array $roles)
	{
		SecUtil::checkUserName($userName, 'userName');
		SecUtil::checkArrayArgument($roles, 'roles', false, false);

		$query = '
delete from %s
using
	%s as ur
join
	%s as u on ur."user_id" = u."user_id"
join
	%s as r on ur."role_id" = r."role_id"
where
	u."user_name" = $1::character varying(20)
	and (';

		$first = true;
		$index = 2;
		foreach ($roles as &$roleName) {
			SecUtil::checkRoleName($roleName, 'roleName');
			if (!$first) $query .= ' or ';

			$query .= ' r."role_name" = $' . $index . '::character varying(60)';

			$first = false;
			$index++;
		}
		$query .= ')';

		$query = sprintf($query, self::getQualifiedTableName('users_roles'), self::getQualifiedTableName('users_roles'), self::getQualifiedTableName('users'), self::getQualifiedTableName('roles'));
		$params = array_merge(array($userName), $roles);

		$db = self::getMembershipDatabase();
		$db->beginTransaction();
		try {
			$rowsAffected = $db->executeNonQuery($query, $params);

			if ($rowsAffected < count($roles))
			{
				if (!MembershipProvider::userExists($userName)) {
					throw new DbQueryFailedException("The user '$userName' does not exist in the data source.");
				}
				throw new DbQueryFailedException('Not all roles specified exist in the data source.');
			}

			if ($rowsAffected <= 0)
				throw new DbQueryFailedException('No rows were affected by the query.');

			$db->commitTransaction();

			return $rowsAffected;
		}
		catch (Exception $e) {
			$db->rollbackTransaction();
			throw $e;
		}
	}

	private static function fromDataRow($dataRow)
	{
		$db	= self::getMembershipDatabase();
		$roleId		= $db->parseDbValue($dataRow['role_id'], DB_TYPE_INT, false);
		$roleName	= $db->parseDbValue($dataRow['role_name'], DB_TYPE_STRING, false);
		$roleDesc	= $db->parseDbValue($dataRow['role_description'], DB_TYPE_STRING, true);
		$builtin	= $db->parseDbValue($dataRow['builtin'], DB_TYPE_BOOL, false);
		$role = new Role($roleId, $roleName, $roleDesc, $builtin);

		return $role;
	}

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
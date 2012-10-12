<?php

class PgMembershipInstaller extends MembershipInstaller
{
	private $_db;

	public function __construct()
	{
		$this->_db = DatabaseFactory::getDatabase(MembershipSettings::getDbSettingName());
	}

	public function install()
	{
		$this->_db->beginTransaction();
		try {
			parent::install();
			$this->_db->commitTransaction();
		}
		catch (Exception $e) {
			$this->_db->rollbackTransaction();
			throw $e;
		}
	}

	public function uninstall()
	{
		$this->_db->beginTransaction();
		try {
			parent::uninstall();
			$this->_db->commitTransaction();
		}
		catch (Exception $e) {
			$this->_db->rollbackTransaction();
			throw $e;
		}
	}


	protected function createOwnerSchema()
	{
        $value = $this->_db->executeScalar("SELECT true FROM information_schema.schemata WHERE schema_name = 'master'");
        $value = $this->_db->parseDbValue($value, DB_TYPE_BOOL, true);

        if ($value !== true)
        {
            $query = sprintf('CREATE SCHEMA %s AUTHORIZATION postgres;', MembershipSettings::getSchemaName());
            $this->_db->executeNonQuery($query);
        }
	}

	protected function createUsersTable()
	{
		$schema = MembershipSettings::getSchemaName();
		$query = <<<QUERY
CREATE TABLE "$schema".users
(
  user_id serial NOT NULL,
  user_name character varying(20) NOT NULL,
  email character varying(128) NOT NULL,
  first_name character varying(60) NOT NULL,
  last_name character varying(30) NOT NULL,
  join_date timestamp without time zone NOT NULL,
  "password" character varying(32) NOT NULL,
  password_salt character varying(32) NOT NULL,
  last_login_date timestamp without time zone,
  last_login_ip character varying(15),
  last_activity_date timestamp without time zone,
  locked_out boolean NOT NULL,
  locked_out_date timestamp without time zone,
  locked_out_message character varying(255),
  auth_token character varying(32),
  builtin boolean NOT NULL,
  CONSTRAINT pk_users PRIMARY KEY (user_id),
  CONSTRAINT uq_users_user_name UNIQUE (user_name),
  CONSTRAINT ck_users_email_non_empty CHECK (char_length(email::text) > 0),
  CONSTRAINT ck_users_password_non_empty CHECK (char_length(password::text) > 0),
  CONSTRAINT ck_users_user_name_non_empty CHECK (char_length(user_name::text) > 0)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "$schema".users OWNER TO postgres;
GRANT ALL ON TABLE "$schema".users TO postgres;
QUERY;

		if (MembershipSettings::getRequireUniqueEmail())
		{
			$query .= <<<QUERY

ALTER TABLE "$schema".users
  ADD CONSTRAINT uq_users_email UNIQUE(email);
QUERY;

		}
		$this->_db->executeNonQuery($query);
	}

	protected function createRolesTable()
	{
		$schema = MembershipSettings::getSchemaName();
		$query = <<<QUERY
CREATE TABLE "$schema".roles
(
  role_id serial NOT NULL,
  role_name character varying(60) NOT NULL,
  role_description character varying(255),
  builtin boolean NOT NULL,
  CONSTRAINT pk_roles PRIMARY KEY (role_id),
  CONSTRAINT uq_roles_role_name UNIQUE (role_name),
  CONSTRAINT ck_roles_role_name_non_empty CHECK (char_length(role_name::text) > 0)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "$schema".roles OWNER TO postgres;
GRANT ALL ON TABLE "$schema".roles TO postgres;
QUERY;
		$this->_db->executeNonQuery($query);
	}

	protected function createUsersRolesTable()
	{
		$schema = MembershipSettings::getSchemaName();
		$query = <<<QUERY
CREATE TABLE "$schema".users_roles
(
  user_id integer NOT NULL,
  role_id integer NOT NULL,
  builtin boolean NOT NULL,
  CONSTRAINT pk_users_roles PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_users_roles_roles_role_id FOREIGN KEY (role_id)
      REFERENCES "$schema".roles (role_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_users_roles_users_user_id FOREIGN KEY (user_id)
      REFERENCES "$schema".users (user_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "$schema".users_roles OWNER TO postgres;
GRANT ALL ON TABLE "$schema".users_roles TO postgres;

CREATE INDEX fki_users_roles_roles_role_id
  ON "$schema".users_roles
  USING btree
  (role_id);
QUERY;
		$this->_db->executeNonQuery($query);
	}

	protected function dropOwnerSchema()
	{
		$query = sprintf('DROP SCHEMA %s;', MembershipSettings::getSchemaName());
		$this->_db->executeNonQuery($query);
	}

	protected function dropUsersTable()
	{
		$query = sprintf('DROP TABLE %s.users;', MembershipSettings::getSchemaName());
		$this->_db->executeNonQuery($query);
	}

	protected function dropRolesTable()
	{
		$query = sprintf('DROP TABLE %s.roles;', MembershipSettings::getSchemaName());
		$this->_db->executeNonQuery($query);
	}

	protected function dropUsersRolesTable()
	{
		$query = sprintf('DROP TABLE %s.users_roles;', MembershipSettings::getSchemaName());
		$this->_db->executeNonQuery($query);
	}
}
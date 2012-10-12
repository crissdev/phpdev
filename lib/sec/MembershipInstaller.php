<?php
abstract class MembershipInstaller implements ISupportLogging
{
	private $_logger;


	protected function __construct()
	{
	}


	protected abstract function createOwnerSchema();

	protected abstract function createUsersTable();

	protected abstract function createRolesTable();

	protected abstract function createUsersRolesTable();


	protected abstract function dropOwnerSchema();

	protected abstract function dropUsersTable();

	protected abstract function dropRolesTable();

	protected abstract function dropUsersRolesTable();


	public function install()
	{
		$this->createOwnerSchema();
		$this->createUsersTable();
		$this->createRolesTable();
		$this->createUsersRolesTable();
	}

	public function uninstall()
	{
		$this->dropUsersRolesTable();
		$this->dropRolesTable();
		$this->dropUsersTable();
		$this->dropOwnerSchema();
	}



	public static final function getInstaller()
	{
		$dbSettings = AppSettings::getDbConnectionSettings(MembershipSettings::getDbSettingName());

		switch (strtolower($dbSettings['provider'])) {
			case 'postgres':
				return new PgMembershipInstaller();
				break;
		}
		throw new NotSupportedException();
	}


	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->_logger;
	}

	/**
	 * @param Logger $logger
	 */
	public function setLogger(Logger $logger = null)
	{
		$this->_logger = $logger;
	}

	protected function logEvent($level, $message, array $args = null, $data = null)
	{
		if ($this->_logger !== null) {
			try {
				$this->_logger->logEvent($level, $message, $args, $data);
			}
			catch (Exception $ex) {
				// Write a warning in PHP error log.
				trigger_error("Cannot write to log: ".$ex->getMessage(), E_USER_WARNING);
			}
		}
	}

}
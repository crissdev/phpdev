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
class Role
{
	private $_id;
	private $_roleName;
	private $_description;
	private $_builtin;


	public function __construct($id, $roleName, $description, $builtIn)
	{
		SecUtil::checkIntegerArgument($id, 'id', false, 1);
		SecUtil::checkRoleName($roleName, 'roleName');
		SecUtil::checkStringArgument($description, 'description', 255, true, true, true);
		SecUtil::checkBooleanArgument($builtIn, 'builtIn', false);

		$this->_id = $id;
		$this->_roleName = $roleName;
		$this->_description = $description;
		$this->_builtin = $builtIn;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function getName()
	{
		return $this->_roleName;
	}

	public function setName($roleName)
	{
		SecUtil::checkRoleName($roleName, 'roleName');
		$this->_roleName = $roleName;
	}

	public function getDescription()
	{
		return $this->_description;
	}

	public function setDescription($description)
	{
		SecUtil::checkStringArgument($description, 'description', 255, true, true, true);
		$this->_description = $description;
	}

	public function isBuiltin()
	{
		return $this->_builtin;
	}

	public function setIsBuiltin($builtIn)
	{
		SecUtil::checkBooleanArgument($builtIn, 'builtIn', false);
		$this->_builtin = $builtIn;
	}
}
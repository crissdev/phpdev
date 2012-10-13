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
final class Authorization
{
	private function __construct() { }


	public static function requireAuthenticatedUser($onlineCheck = false)
	{
		$principal = Principal::getCurrent();

		if (!$principal->isAuthenticated())
			throw new AccessDeniedException('You must be authenticated to perform this operation.');

		if ($onlineCheck && !$principal->getUser()->isOnline())
			throw new SessionExpiredException();
	}

	public static function requireRole($roleName, $builtinSuffice = false, $onlineCheck = false)
	{
		self::requireAuthenticatedUser($onlineCheck);

		$principal = Principal::getCurrent();

		if (!$builtinSuffice && !$principal->isInRole($roleName))
			throw new AccessDeniedException("You must be a member of '{$roleName}' to perform this operation.");
	}
}
<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */

/**
 * Path utility functions
 */
class PathUtil
{
	private function __construct() { }

	/**
	 * Takes one or more paths and combines them, using the UNIX path separator
	 * @static
	 * @return string
	 * @link http://www.bin-co.com/php/scripts/filesystem/join_path/
	 */
	public static function combine()
	{
		$path = '';
		$arguments = func_get_args();
		$args = array();

		foreach ($arguments as $a)
		{
			//Removes the empty elements and change directory separator to /
			if ($a !== '') {
				$args[] = str_replace('\\', '/', $a);
			}
		}

		$arg_count = count($args);
		for($i = 0; $i < $arg_count; $i++) {
			$folder = $args[$i];

			//Remove the first char if it is a '/' - and its not in the first argument
			if ($i != 0 and $folder[0] == '/') $folder = substr($folder, 1);

			//Remove the last char - if its not in the last argument
			if ($i != $arg_count - 1 and substr($folder, -1) == '/') $folder = substr($folder, 0, -1);

			$path .= $folder;
			if ($i != $arg_count - 1) $path .= '/'; //Add the '/' if its not the last element.
		}
		return $path;
	}

	/**
	 * Replaces consecutive backslashes or slashes with one slash.
	 * @static
	 * @param $path string The path to normalize
	 * @return mixed The normalized path
	 */
	public static function normalize($path)
	{
		SecUtil::checkStringArgument($path, 'path', -1, false, false, false);

		// Replace backslashes with slashes
		$path = preg_replace('/\\+/', '/', $path);

		// Replace consecutive slashes with one slash
		$path = preg_replace('/\/{2,}/', '/', $path);

		return $path;
	}
}
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
class Formatting
{
	private function __construct() { }

	// Formatting helpers
	public static function byteFormat($bytes, $unit = null, $decimals = 2)
	{
		if ($bytes === null) throw new ArgumentNullException(null, 'bytes');
		if (!is_numeric($bytes)) throw new ArgumentException('Parameter must be numeric.', 'bytes');

		SecUtil::checkStringArgument($unit, 'unit', 2);
		SecUtil::checkIntegerArgument($decimals, 'decimals', false, 0);

		$units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
		$value = 0;

		if ($bytes > 0)
		{
			// Generate automatic prefix by bytes
			// If wrong prefix given
			if ($unit === null || !array_key_exists($unit, $units))
			{
				$pow = floor(log($bytes) / log(1024));
				$unit = array_search($pow, $units);
			}
			// Calculate byte value by prefix
			$value = ($bytes / pow(1024, floor($units[$unit])));
		}
		// Format output
		return sprintf('%.' . $decimals . 'f ' . $unit, $value);
	}

	public static function formatDate(DateTime $value = null, $format = 'Y-m-d H:i:s')
	{
		SecUtil::checkStringArgument($format, 'format', -1, false, false, false);

		$result = null;

		if ($value !== null)
		{
			$result = $value->format($format);

			if ($result === false)
				throw new ArgumentException('Invalid date time format.', 'value');
		}
		return $result;
	}
}
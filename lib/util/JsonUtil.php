<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */

/**
 * JSON utility functions
 */
class JsonUtil
{
	private function __construct() { }

	/**
	 * Returns the JSON representation of a value
	 *
	 * @param mixed $value The value being encoded. Can be any type except a resource.<br/>This function only works with UTF-8 encoded data.
	 * @param int $options [optional] Bitmask consisting of JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, JSON_FORCE_OBJECT.
	 * @throws ArgumentException
	 * @return string On success, a JSON encoded string.<br/>On failure, an ArgumentException is thrown
	 */
	public static function encode($value, $options = null)
	{
		SecUtil::checkIntegerArgument($options, 'options', true);

		if (($options != null) &&
			(($options & JSON_HEX_QUOT) != JSON_HEX_QUOT) &&
			(($options & JSON_HEX_TAG) != JSON_HEX_TAG) &&
			(($options & JSON_HEX_AMP) != JSON_HEX_AMP) &&
			(($options & JSON_HEX_APOS) != JSON_HEX_APOS) &&
			(($options & JSON_FORCE_OBJECT) != JSON_FORCE_OBJECT))
		{
			throw new ArgumentException(null, 'options');
		}

		$result = json_encode($value, $options);
		self::checkLastError();
		return $result;
	}

	/**
	 * Decodes a JSON string
	 *
	 * @param string $json The json string being decoded.
	 * @param bool $assoc [optional] When true, returned objects will be converted into associative arrays. Default is false.
	 * @param int $depth [optional] User specified recursion depth. Default value is 128.
	 * @param int $maxJsonLength The maximum length of the JSON string passed. The default is -1, no limit.
	 * @return mixed The value encoded in json in appropriate PHP type.<br/>
	 * Values true, false and null (case-insensitive) are returned as true, false and &null respectively.
	 * &null is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
	 */
	public static function decode($json, $assoc = false, $depth = 128, $maxJsonLength = -1)
	{
		SecUtil::checkBooleanArgument($assoc, 'assoc', false);
		SecUtil::checkIntegerArgument($depth, 'depth', false, 1);
		SecUtil::checkIntegerArgument($maxJsonLength, 'maxJsonLength', false, -1);
		SecUtil::checkStringArgument($json, 'json', $maxJsonLength);

		$result = json_decode($json, $assoc, $depth);
		self::checkLastError();
		return $result;
	}

	/**
	 * Formats a JSON string
	 *
	 * @param string $json The json string being formatted.
	 * @param array|int $options An associative array with options to be passed to this method. Currently 'tab' and 'newline' options are supported.
	 * @return string The json string in a more human readable format.
	 */
	public static function format($json, array $options = null)
	{
		SecUtil::checkStringArgument($json, 'json', -1, false, false);

		$tab          = '  ';
		$newLine      = PHP_EOL;
		$new_json     = '';
		$indent_level = 0;
		$in_string    = false;
		$len          = strlen($json);

		if (is_array($options)) {
			if (array_key_exists('tab', $options)) {
				$value = $options['tab'];
				SecUtil::checkStringArgument($value, 'options.tab', 100, false, true, false);
				$tab = $value;
			}
			if (array_key_exists('newline', $options)) {
				$value = $options['newline'];
				SecUtil::checkStringArgument($value, 'options.newline', 100, false, true, false);
				$newLine = $value;
			}
		}

		for ($c = 0; $c < $len; $c++) {
			$char = $json[$c];
			switch ($char) {
				case '{':
				case '[':
					if (!$in_string) {
						$indent_level++;
						$new_json .= $char . $newLine . str_repeat($tab, $indent_level);
					}
					else
						$new_json .= $char;
					break;
				case '}':
				case ']':
					if (!$in_string) {
						$indent_level--;
						$new_json .= $newLine . str_repeat($tab, $indent_level) . $char;
					}
					else
						$new_json .= $char;
					break;
				case ',':
					if (!$in_string)
						$new_json .= ",{$newLine}" . str_repeat($tab, $indent_level);
					else
						$new_json .= $char;
					break;
				case ':':
					if (!$in_string)
						$new_json .= ': ';
					else
						$new_json .= $char;
					break;
				case '"':
					if ($in_string) {
						if (($c > 0) && ($json[$c - 1] != '\\'))
							$in_string = false;
					}
					else
					{
						$in_string = true;
					}
					$new_json .= $char;
					break;
				default:
					$new_json .= $char;
					break;
			}
		}
		return $new_json;
	}

	private static function checkLastError()
	{
		$error = json_last_error();

		switch($error)
		{
			case JSON_ERROR_NONE:
				// No error
				break;
			case JSON_ERROR_DEPTH:
				// The maximum stack depth has been exceeded
				throw new JsonException('The maximum stack depth has been exceeded.', $error);
			case JSON_ERROR_CTRL_CHAR:
				// Control character error, possibly incorrectly encoded
				throw new JsonException('Control character error, possibly incorrectly encoded.', $error);
			case JSON_ERROR_SYNTAX:
				// Syntax error
				throw new JsonException('Syntax error.', $error);
			case JSON_ERROR_STATE_MISMATCH:
				// Invalid or malformed JSON
				throw new JsonException('Invalid or malformed JSON.', $error);
		}
		if (defined('JSON_ERROR_UTF8') && $error == JSON_ERROR_UTF8) {
			// Malformed UTF-8 characters, possibly incorrectly encoded. This constant is available as of PHP 5.3.1
			throw new JsonException('Malformed UTF-8 characters, possibly incorrectly encoded.', $error);
		}
	}
}
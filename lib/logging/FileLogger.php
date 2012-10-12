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
class FileLogger extends Logger
{
	private $_fileName;

	public function __construct($fileName, $level = LOGGER_LEVEL_OFF, $source = '')
	{
		parent::__construct($level, $source);

		SecUtil::checkStringArgument($fileName, 'fileName', MAX_PATH_LENGTH, false, false, true);
		$this->_fileName = $fileName;
	}

	public function logEvent($level, $message, array $args = null, $data = null)
	{
		SecUtil::checkIntegerArgument($level, 'level', false, LOGGER_LEVEL_OFF, LOGGER_LEVEL_DEBUG);
		SecUtil::checkStringArgument($message, 'message', -1, false, true, false);
		SecUtil::checkArrayArgument($args, 'args', true, true);

		if ($this->getLoggingLevel() == LOGGER_LEVEL_OFF || $level == LOGGER_LEVEL_OFF)
			return 0;

		if (($level == LOGGER_LEVEL_ERROR && !$this->isErrorEnabled())
				|| ($level == LOGGER_LEVEL_WARN && !$this->isWarnEnabled())
				|| ($level == LOGGER_LEVEL_INFO && !$this->isInfoEnabled())
				|| ($level == LOGGER_LEVEL_DEBUG && !$this->isDebugEnabled()))
			return 0;

		$output = '';

		if (is_array($args))
			$output .= vsprintf($message, $args);
		else
			$output .= $message;

		if ($data !== null) {
			if (is_bool($data))
				$output .= ' Data: ' . ($data ? 'true' : 'false');
			else if (is_scalar($data))
				$output .= ' Data: ' . $data;
			else if (is_array($data)) {
				if (!empty($data))
					$output .= ' Data: ' . var_export($data, true);
			}
			else
				$output .= ' Data: ' . var_export($data, true);
		}

		$header = '';

		switch ($level) {
			case LOGGER_LEVEL_ERROR:
				$header = 'ERROR:';
				break;
			case LOGGER_LEVEL_WARN:
				$header = 'WARN:';
				break;
			case LOGGER_LEVEL_INFO:
				$header = 'INFO:';
				break;
			case LOGGER_LEVEL_DEBUG:
				$header = 'DEBUG:';
				break;
		}

		$source = $this->getLoggingSource();
		if ($source != '') $source = "{$source} ";

		$output = $header . ' [' . date('Y-m-d H:i:s') . '] ' . $source . $output . PHP_EOL;

		$result = file_put_contents($this->getFileName(), $output, FILE_APPEND | LOCK_EX);

		return $result;
	}

	public final function getFileName()
	{
		return $this->_fileName;
	}
}
<?php

namespace Core\Support;

class SessionStore
{
	public static function read($session_name)
	{
		$filepath = self::getFilePath($session_name);
		if (!is_file($filepath)) {
			return null;
		}

		$content = @file_get_contents($filepath);
		if ($content === false) {
			return null;
		}

		// Empty or corrupted files read as null, as before, so one bad file cannot break the JSON endpoints.
		$value = json_decode($content, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return null;
		}

		if (is_numeric($value)) {
			return floor($value) != $value ? (float) $value : (int) $value;
		}

		return $value;
	}

	public static function write($session_name, $session_content)
	{
		$filepath = self::getFilePath($session_name);
		$directory = dirname($filepath);
		if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
			error_log('SessionStore: unable to create the session data directory: ' . $directory);
			return;
		}

		$encoded_content = json_encode($session_content);
		if ($encoded_content === false) {
			throw new \UnexpectedValueException('Unable to encode session content: ' . json_last_error_msg());
		}

		if (is_file($filepath)) {
			$current_content = @file_get_contents($filepath);
			if ($current_content === $encoded_content) {
				return;
			}
		}

		// A file the web server cannot write must not break the JSON endpoints; the original only warned.
		$file = @fopen($filepath, 'c+');
		if ($file === false) {
			error_log('SessionStore: unable to open the session file for writing: ' . $filepath);
			return;
		}

		$locked = false;
		try {
			if (!flock($file, LOCK_EX)) {
				throw new \RuntimeException('Unable to lock the session file for writing.');
			}
			$locked = true;

			if (!ftruncate($file, 0) || !rewind($file)) {
				throw new \RuntimeException('Unable to prepare the session file for writing.');
			}

			$content_length = strlen($encoded_content);
			$bytes_written = 0;
			while ($bytes_written < $content_length) {
				$written = fwrite($file, substr($encoded_content, $bytes_written));
				if ($written === false || $written === 0) {
					throw new \RuntimeException('Unable to write the complete session content.');
				}

				$bytes_written += $written;
			}

			if (!fflush($file)) {
				throw new \RuntimeException('Unable to flush the session file.');
			}
		} finally {
			if ($locked) {
				flock($file, LOCK_UN);
			}
			fclose($file);
		}
	}

	public static function getDataDirectory()
	{
		return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR . 'data';
	}

	private static function getFilePath($session_name)
	{
		$filename_parts = explode('.txt_', $session_name, 2);
		if (count($filename_parts) !== 2) {
			throw new \InvalidArgumentException('Session names must contain ".txt_".');
		}

		return self::getDataDirectory()
			. DIRECTORY_SEPARATOR . $filename_parts[0] . '.txt'
			. DIRECTORY_SEPARATOR . $filename_parts[1] . '.txt';
	}
}

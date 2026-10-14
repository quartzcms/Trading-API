<?php

namespace Core\Support;

class Filesystem
{
	public static function directoryToArray($directory)
	{
		if (!is_dir($directory)) {
			return array();
		}

		$entries = scandir($directory);
		if ($entries === false) {
			throw new \RuntimeException('Unable to read directory: ' . $directory);
		}

		$result = array();
		foreach ($entries as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}

			$entry_path = $directory . DIRECTORY_SEPARATOR . $entry;
			if (is_dir($entry_path)) {
				$result[$entry] = self::directoryToArray($entry_path);
				continue;
			}

			$result[] = $entry;
		}

		return $result;
	}
}

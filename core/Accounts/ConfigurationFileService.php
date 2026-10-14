<?php

namespace Core\Accounts;

use Core\Support\Filesystem;

class ConfigurationFileService
{
	public static function hasAccountFiles($directory)
	{
		foreach (Filesystem::directoryToArray($directory) as $entry) {
			if (!is_array($entry) && $entry !== '.htaccess') {
				return true;
			}
		}

		return false;
	}
}

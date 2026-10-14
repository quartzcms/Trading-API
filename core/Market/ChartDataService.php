<?php

namespace Core\Market;

use Core\Support\Filesystem;

class ChartDataService
{
	public static function build($directory, $include_market_data = true)
	{
		$files = Filesystem::directoryToArray($directory);
		$build = array();

		foreach ($files as $file) {
			$file_path = $directory . DIRECTORY_SEPARATOR . $file;
			$file_content = file_get_contents($file_path);
			if ($file_content === false) {
				throw new \RuntimeException('Unable to read chart data file: ' . $file_path);
			}

			$lines = explode("\n", $file_content);
			sort($lines);
			$date_parts = explode('_', $file);
			if (!isset($date_parts[1])) {
				continue;
			}

			$time = null;
			foreach ($lines as $line) {
				$line = trim($line);
				if ($line === '') {
					continue;
				}

				$parts = explode(':', $line);
				if (!isset($parts[3], $parts[4])) {
					continue;
				}

				$hour = substr($parts[0], 0, 2);
				$minute = substr($parts[0], 2, 2);
				$second = substr($parts[0], 4, 6);
				$time = array(
					'open' => $parts[3],
					'close' => $parts[4],
					'date' => $date_parts[1] . ' ' . $hour . ':' . $minute . ':' . $second
				);

				if ($include_market_data) {
					$time['high'] = isset($parts[5]) ? $parts[5] : $parts[3];
					$time['low'] = isset($parts[6]) ? $parts[6] : $parts[4];
					$time['volume'] = isset($parts[7]) ? $parts[7] : 0;
				}
			}

			if ($time !== null) {
				$build[] = $time;
			}
		}

		if ($include_market_data) {
			usort($build, static function ($first, $second) {
				return $first['date'] <=> $second['date'];
			});
		}

		return $build;
	}
}

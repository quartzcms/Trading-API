<?php

namespace Core\Market;

use Core\Support\Filesystem;

class MarketSummaryService
{
	public static function summarizeChangeAtTime($directory, $time, $average)
	{
		$amount = 0;
		$count = 0;

		foreach (Filesystem::directoryToArray($directory) as $file) {
			$file_path = $directory . DIRECTORY_SEPARATOR . $file;
			$file_content = file_get_contents($file_path);
			if ($file_content === false) {
				throw new \RuntimeException('Unable to read market data file: ' . $file_path);
			}

			foreach (explode("\n", $file_content) as $line) {
				$line_parts = explode(':', $line);
				if (isset($line_parts[0], $line_parts[2]) && $line_parts[0] === $time) {
					$amount += (float) $line_parts[2];
					$count++;
					break;
				}
			}
		}

		if ($average && $count !== 0) {
			$amount /= $count;
		}

		if (!is_finite($amount)) {
			$amount = 0;
		}

		return array(
			'amount' => number_format($amount, 2, '.', ''),
			'files' => $count
		);
	}

	public static function summarizeFilesForDate($directory, $date)
	{
		$amount = 0;
		$count = 0;

		foreach (Filesystem::directoryToArray($directory) as $file) {
			$date_from_name = str_replace('-', '', substr($file, 1, 10));
			if ($date_from_name !== '' && $date_from_name === $date) {
				$amount += self::sumFileChanges($directory, $file);
				$count++;
			}
		}

		return array(
			'amount' => number_format($amount, 2, '.', ''),
			'files' => $count
		);
	}

	public static function summarizeFilesForMonth($directory, $month)
	{
		$amount = 0;
		$count = 0;

		foreach (Filesystem::directoryToArray($directory) as $file) {
			$file_month = (int) substr($file, 6, 2);
			if ($file_month !== 0 && (int) $month === $file_month) {
				$amount += self::sumFileChanges($directory, $file);
				$count++;
			}
		}

		if (!is_finite($amount)) {
			$amount = 0;
		}

		return array(
			'amount' => number_format($amount, 2, '.', ''),
			'files' => $count
		);
	}

	public static function summarizeGroupedFiles($directory, $today, $group_size)
	{
		$amounts = array();
		$counts = array();
		$starting_dates = array();
		$group = 1;
		$position = 1;
		$files = Filesystem::directoryToArray($directory);
		asort($files);

		foreach ($files as $file) {
			$date = str_replace('-', '', substr($file, 1, 10));
			if ($date === '' || (int) $date > (int) $today) {
				continue;
			}

			$amounts[$group] = (isset($amounts[$group]) ? $amounts[$group] : 0)
				+ self::sumFileChanges($directory, $file);
			$starting_dates[$group][] = $date;
			$counts[$group] = isset($counts[$group]) ? $counts[$group] + 1 : 1;

			if ($position === $group_size) {
				$position = 0;
				$group++;
			}
			$position++;
		}

		$last_group = count($amounts) > 0 ? array_key_last($amounts) : null;
		$amount = $last_group === null ? 0 : $amounts[$last_group];

		return array(
			'amount' => number_format(is_finite($amount) ? $amount : 0, 2, '.', ''),
			'date' => $last_group === null ? '' : $starting_dates[$last_group][0],
			'files' => $last_group === null ? 0 : $counts[$last_group]
		);
	}

	private static function sumFileChanges($directory, $file)
	{
		$file_path = $directory . DIRECTORY_SEPARATOR . $file;
		$file_content = file_get_contents($file_path);
		if ($file_content === false) {
			throw new \RuntimeException('Unable to read market data file: ' . $file_path);
		}

		$amount = 0;
		foreach (explode("\n", $file_content) as $line) {
			$line_parts = explode(':', $line);
			if (isset($line_parts[2])) {
				$amount += (float) $line_parts[2];
			}
		}

		return $amount;
	}
}

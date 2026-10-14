<?php

namespace Core\Reports;

use Core\Support\Filesystem;
use Core\Support\SessionStore;

class ExchangeStatsService
{
	private const CSV_HEADER = '"id","type","date","price","total","status","coin","side","difference","fees"';

	public static function persistCollection($username, $date_collect, $time, $withdraw_mode, $lines, $directory)
	{
		if ($withdraw_mode === 'off') {
			SessionStore::write($username . '.txt_date_collect', $date_collect);
		}

		if (!empty($lines) && $withdraw_mode === 'off') {
			self::writeCurrentStats($directory, $time, $lines);
		}
	}

	public static function processRequestedActions($username, $query, $directory, $time)
	{
		$year = date('Y', $time);
		if (isset($query['profit']) && $query['profit'] === 'on') {
			$cash_flows = self::buildYearCashFlows($directory, $year);
			SessionStore::write($username . '.txt_deposit_total_' . $year, $cash_flows['deposits']);
			SessionStore::write($username . '.txt_withdraw_total_' . $year, $cash_flows['withdrawals']);
		}

		if (isset($query['consolidate']) && $query['consolidate'] === 'on') {
			self::consolidateYear($directory, $year);
		}
	}

	public static function writeCurrentStats($directory, $time, $lines)
	{
		if (!is_array($lines)) {
			throw new \UnexpectedValueException('Exchange statistics lines must be an array.');
		}

		$contents = implode("\n", self::flattenLines($lines));
		self::writeLockedFile($directory . DIRECTORY_SEPARATOR . (int) $time . '.txt', $contents);
	}

	public static function getLastCollectedTimestamp(&$date_collect, $fallback)
	{
		if (!is_array($date_collect)) {
			throw new \UnexpectedValueException('Collected account data must be an array.');
		}

		$last_timestamp = $fallback;
		foreach ($date_collect as &$years) {
			if (!is_array($years)) {
				continue;
			}

			foreach ($years as &$entries) {
				if (!is_array($entries)) {
					continue;
				}

				ksort($entries);
				foreach ($entries as $timestamp => $entry) {
					$last_timestamp = $timestamp;
				}
			}
			unset($entries);
		}
		unset($years);

		return $last_timestamp;
	}

	public static function buildYearCashFlows($directory, $year)
	{
		$files = self::getYearFiles($directory, $year);
		$deposits = array();
		$withdrawals = array();

		foreach ($files as $file_path) {
			$contents = file_get_contents($file_path);
			if ($contents === false) {
				throw new \RuntimeException('Unable to read exchange statistics file: ' . $file_path);
			}

			foreach (explode("\n", $contents) as $line) {
				if ($line === '') {
					continue;
				}

				$fields = str_getcsv($line);
				if (!isset($fields[1], $fields[2], $fields[4], $fields[6])) {
					continue;
				}

				$type = $fields[1];
				if ($type !== 'deposit' && $type !== 'withdraw') {
					continue;
				}

				$timestamp = strtotime($fields[2]);
				if ($timestamp === false) {
					continue;
				}

				$coin = $fields[6];
				$amount = abs((float) $fields[4]);
				if ($type === 'deposit') {
					$deposits[$coin][$timestamp] = $amount;
				} else {
					$withdrawals[$coin][$timestamp] = $amount;
				}
			}
		}

		return array(
			'deposits' => $deposits,
			'withdrawals' => $withdrawals
		);
	}

	public static function consolidateYear($directory, $year)
	{
		$files = self::getYearFiles($directory, $year);
		if (!$files) {
			return false;
		}

		$contents = array(self::CSV_HEADER);
		foreach ($files as $file_path) {
			$file_contents = file_get_contents($file_path);
			if ($file_contents === false) {
				throw new \RuntimeException('Unable to read exchange statistics file: ' . $file_path);
			}
			$contents[] = $file_contents;
		}

		self::writeLockedFile($directory . DIRECTORY_SEPARATOR . (int) $year . '.csv', implode("\n", $contents));
		return true;
	}

	private static function getYearFiles($directory, $year)
	{
		$entries = Filesystem::directoryToArray($directory);
		$files = array();

		foreach ($entries as $entry) {
			if (!is_string($entry) || strpos($entry, '.csv') !== false) {
				continue;
			}

			$filename_timestamp = str_replace('.txt', '', $entry);
			$timestamp = is_numeric($filename_timestamp)
				? (int) $filename_timestamp
				: strtotime($filename_timestamp);
			if ($timestamp === false || date('Y', $timestamp) !== (string) $year) {
				continue;
			}

			$files[] = $directory . DIRECTORY_SEPARATOR . $entry;
		}

		return $files;
	}

	private static function flattenLines($lines)
	{
		$flattened = array();
		foreach ($lines as $coin_lines) {
			if (is_array($coin_lines)) {
				foreach ($coin_lines as $line) {
					$flattened[] = $line;
				}
			} else {
				$flattened[] = $coin_lines;
			}
		}

		return $flattened;
	}

	private static function writeLockedFile($file_path, $contents)
	{
		$file = fopen($file_path, 'c+');
		if ($file === false) {
			throw new \RuntimeException('Unable to open exchange statistics file for writing: ' . $file_path);
		}

		$locked = false;
		try {
			if (!flock($file, LOCK_EX)) {
				throw new \RuntimeException('Unable to lock exchange statistics file: ' . $file_path);
			}
			$locked = true;

			if (!ftruncate($file, 0) || !rewind($file)) {
				throw new \RuntimeException('Unable to prepare exchange statistics file: ' . $file_path);
			}

			$length = strlen($contents);
			$written = 0;
			while ($written < $length) {
				$bytes = fwrite($file, substr($contents, $written));
				if ($bytes === false || $bytes === 0) {
					throw new \RuntimeException('Unable to write exchange statistics file: ' . $file_path);
				}
				$written += $bytes;
			}

			if (!fflush($file)) {
				throw new \RuntimeException('Unable to flush exchange statistics file: ' . $file_path);
			}
		} finally {
			if ($locked) {
				flock($file, LOCK_UN);
			}
			fclose($file);
		}
	}
}

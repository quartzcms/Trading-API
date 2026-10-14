<?php

namespace Core\Reports;

class ReportDataService
{
	public static function getTotal($username, $file, $withdrawals, $deposits, $date)
	{
		$log_content = self::readLog($username, $file);
		if ($log_content === '') {
			return array(0, 0, 0);
		}

		$lines = explode("\n", $log_content);
		$first_line = null;
		$last_line = null;

		foreach ($lines as $line) {
			if (strpos($line, 'experimental feature') === false) {
				$first_line = $line;
				break;
			}
		}

		for ($index = count($lines) - 1; $index >= 0; $index--) {
			if (trim($lines[$index]) !== '' && strpos($lines[$index], 'experimental feature') === false) {
				$last_line = $lines[$index];
				break;
			}
		}

		$first_balance = self::extractBalance($first_line);
		$last_balance = self::extractBalance($last_line);
		if ($first_balance === null || $last_balance === null) {
			return array(0, 0, 0);
		}
		$withdrawals = is_array($withdrawals) ? $withdrawals : array();
		$deposits = is_array($deposits) ? $deposits : array();

		$target_date = self::normalizeDate($date);
		$daily_cash_flow = self::sumCashFlowsForDate($withdrawals, $target_date, -1)
			+ self::sumCashFlowsForDate($deposits, $target_date, 1);

		return array(
			number_format($last_balance - $first_balance, 3, '.', ''),
			$last_balance,
			$daily_cash_flow
		);
	}

	public static function getTransactions($username, $file, $transaction_id_field)
	{
		$log_content = self::readLog($username, $file);
		if ($log_content === '') {
			return array(0, 0, 0);
		}

		$transaction_count = 0;
		$total_gain = 0;
		$total_loss = 0;
		$seen_transactions = array();
		$transaction_id = '"' . $transaction_id_field . '"';

		foreach (explode("\n", $log_content) as $line) {
			if (strpos($line, $transaction_id) !== false) {
				$transaction_count++;
			}

			if (strpos($line, 'This transaction is related to the experimental feature.') === false) {
				continue;
			}

			$line_parts = explode('=', $line, 2);
			if (!isset($line_parts[1])) {
				continue;
			}

			$transaction_parts = explode('|', $line_parts[1], 2);
			$transaction_json = $transaction_parts[0];
			if (in_array($transaction_json, $seen_transactions, true)) {
				continue;
			}
			$seen_transactions[] = $transaction_json;

			$transaction_data = json_decode($transaction_json, true);
			if (!isset($transaction_data['result'])) {
				continue;
			}

			$result_parts = explode(':', $transaction_data['result'], 2);
			if (!isset($result_parts[1])) {
				continue;
			}

			$transaction_total = (float) str_replace(array(' ', '+'), '', $result_parts[1]);
			$transaction_count++;
			if ($transaction_total > 0) {
				$total_gain += $transaction_total;
			} else {
				$total_loss += $transaction_total;
			}
		}

		return array($transaction_count, $total_gain, $total_loss);
	}

	public static function getHighest($username, $file)
	{
		return self::getPriceExtreme($username, $file, true);
	}

	public static function getLowest($username, $file)
	{
		return self::getPriceExtreme($username, $file, false);
	}

	public static function flattenArray($data)
	{
		$key_values = array();
		foreach ($data as $key => $value) {
			if (empty($value)) {
				continue;
			}

			if (is_array($value)) {
				$key_values[] = self::flattenArray($value);
			} else {
				$key_values[] = $key . ':' . $value;
			}
		}

		return implode(', ', $key_values);
	}

	private static function readLog($username, $file)
	{
		$log_path = 'config/logs/' . $username . '/' . $file;
		if (!is_file($log_path)) {
			return '';
		}

		$log_content = file_get_contents($log_path);
		if ($log_content === false) {
			throw new \RuntimeException('Unable to read the user report log: ' . $log_path);
		}

		return $log_content;
	}

	private static function extractBalance($line)
	{
		if ($line === null) {
			return null;
		}

		$line_parts = explode('=', $line, 2);
		if (!isset($line_parts[1])) {
			return null;
		}

		$data_parts = explode('|', $line_parts[1]);
		if (!isset($data_parts[4])) {
			return null;
		}

		$balance_parts = explode('/', $data_parts[4], 2);
		if (!isset($balance_parts[1]) || !is_numeric($balance_parts[1])) {
			return null;
		}

		return $balance_parts[1];
	}

	private static function sumCashFlowsForDate($entries, $target_date, $direction)
	{
		$total = 0;
		foreach ($entries as $entry) {
			$parts = explode(':', $entry, 2);
			if (!isset($parts[1]) || !is_numeric($parts[1])) {
				continue;
			}

			if (self::normalizeDate($parts[0]) === $target_date) {
				$total += $direction * (float) $parts[1];
			}
		}

		return $total;
	}

	private static function normalizeDate($date)
	{
		$date_parts = explode('-', $date);
		if (isset($date_parts[2]) && strlen($date_parts[2]) === 4) {
			return $date_parts[2] . $date_parts[1] . $date_parts[0];
		}

		return str_replace('-', '', $date);
	}

	private static function getPriceExtreme($username, $file, $find_highest)
	{
		$log_content = self::readLog($username, $file);
		$extreme = null;
		foreach (explode("\n", $log_content) as $line) {
			$line_parts = explode('=', $line, 2);
			$date_parts = explode(' ', $line_parts[0]);
			if (!isset($line_parts[1], $date_parts[2])) {
				continue;
			}

			$price_parts = explode('*', $date_parts[2], 2);
			if (!isset($price_parts[1]) || !is_numeric($price_parts[1])) {
				continue;
			}

			$price = (float) $price_parts[1];
			if ($extreme === null) {
				$extreme = $price;
			} else {
				$extreme = $find_highest ? max($extreme, $price) : min($extreme, $price);
			}
		}

		return $extreme ?? 0;
	}
}

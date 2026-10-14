<?php

namespace Core\Accounts;

use Core\Support\Filesystem;
use Core\Support\SessionStore;

class AccountDataService
{
	public static function build($username, $config_directory)
	{
		$log_directory = $config_directory . '/logs/' . $username;
		$files = Filesystem::directoryToArray($log_directory);
		$withdrawals = self::parseCashFlows(SessionStore::read('config_' . $username . '.txt_total_money_withdrew'), -1);
		$deposits = self::parseCashFlows(SessionStore::read('config_' . $username . '.txt_total_money_deposited'), 1);
		$build = array();
		$total_from_start = 0;

		foreach ($files as $file) {
			if (!is_string($file)) {
				continue;
			}

			$filename_parts = explode('_', $file);
			$date = $filename_parts[0];
			$file_path = $log_directory . '/' . $file;
			$file_content = file_get_contents($file_path);
			if ($file_content === false) {
				throw new \RuntimeException('Unable to read account log: ' . $file_path);
			}
			if ($file_content === '') {
				continue;
			}

			$balances = self::getFirstAndLastBalance($file_content);
			if ($balances === null) {
				continue;
			}

			$daily_total = $balances[1] - $balances[0];
			$daily_cash_flow = self::getCashFlowForDate($withdrawals, $date)
				+ self::getCashFlowForDate($deposits, $date);
			$cumulative_cash_flow = self::getCashFlowThroughDate($withdrawals, $date)
				+ self::getCashFlowThroughDate($deposits, $date);

			$total_from_start += $daily_total;
			$build[] = array(
				'date' => $date,
				'today' => (float) number_format($daily_total - $daily_cash_flow, 3, '.', ''),
				'account' => (float) ($total_from_start - $cumulative_cash_flow)
			);
		}

		return $build;
	}

	private static function getFirstAndLastBalance($file_content)
	{
		$lines = explode("\n", $file_content);
		$first_line = null;
		$last_line = null;

		foreach ($lines as $line) {
			if (strpos($line, 'experimental feature') === false) {
				$first_line = $line;
				break;
			}
		}

		for ($index = count($lines) - 1; $index >= 0; $index--) {
			$line = $lines[$index];
			if ($line !== '' && strpos($line, 'experimental feature') === false) {
				$last_line = $line;
				break;
			}
		}

		$first_balance = self::extractBalance($first_line);
		$last_balance = self::extractBalance($last_line);
		if ($first_balance === null || $last_balance === null) {
			return null;
		}

		return array($first_balance, $last_balance);
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

		return (float) $balance_parts[1];
	}

	private static function parseCashFlows($cash_flows, $direction)
	{
		if ($cash_flows === null || $cash_flows === '') {
			return array();
		}

		if (is_string($cash_flows)) {
			$cash_flows = explode(',', $cash_flows);
		}
		if (!is_array($cash_flows)) {
			throw new \UnexpectedValueException('Session cash flow data must be an array or comma-separated string.');
		}

		$parsed_flows = array();
		foreach ($cash_flows as $cash_flow) {
			$parts = explode(':', $cash_flow, 2);
			if (!isset($parts[1]) || !is_numeric($parts[1])) {
				continue;
			}

			$parsed_flows[] = array(
				'date' => self::normalizeDate($parts[0]),
				'amount' => $direction * (float) $parts[1]
			);
		}

		return $parsed_flows;
	}

	private static function normalizeDate($date)
	{
		$date_parts = explode('-', $date);
		if (isset($date_parts[2]) && strlen($date_parts[2]) === 4) {
			return $date_parts[2] . $date_parts[1] . $date_parts[0];
		}

		return str_replace('-', '', $date);
	}

	private static function getCashFlowForDate($cash_flows, $date)
	{
		$amount = 0;
		$target_date = self::normalizeDate($date);
		foreach ($cash_flows as $cash_flow) {
			if ($cash_flow['date'] === $target_date) {
				$amount += $cash_flow['amount'];
			}
		}

		return $amount;
	}

	private static function getCashFlowThroughDate($cash_flows, $date)
	{
		$amount = 0;
		$target_date = self::normalizeDate($date);
		foreach ($cash_flows as $cash_flow) {
			if ($cash_flow['date'] <= $target_date) {
				$amount += $cash_flow['amount'];
			}
		}

		return $amount;
	}
}

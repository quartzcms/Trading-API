<?php

namespace Core\Reports;

use Core\Support\Filesystem;
use Core\Support\SessionStore;

class ReportPageService
{
	public static function buildDashboard($username, $date, $transaction_id_field)
	{
		$withdrawals = self::getCashFlows($username, 'total_money_withdrew');
		$deposits = self::getCashFlows($username, 'total_money_deposited');
		$log_directory = 'config/logs/' . $username;
		$files = Filesystem::directoryToArray($log_directory);
		$daily_filename = $date . '_' . $username . '.txt';
		$today_money = ReportDataService::getTotal($username, $daily_filename, $withdrawals, $deposits, $date);

		$total_money_logs = 0;
		$total_deposit_withdraw = 0;
		$target_date = (int) str_replace('-', '', $date);
		$days = array();

		foreach ($files as $file) {
			if (!is_string($file) || strpos($file, $username) === false) {
				continue;
			}

			$days[] = $file;
			$file_date_parts = explode('_', $file);
			$file_date = isset($file_date_parts[0]) ? (int) str_replace('-', '', $file_date_parts[0]) : 0;
			if ($file_date > $target_date) {
				continue;
			}

			$file_total = ReportDataService::getTotal($username, $file, $withdrawals, $deposits, $file_date_parts[0]);
			$total_money_logs += (float) $file_total[0];
			$total_deposit_withdraw += (float) $file_total[2];
		}

		$initial_money = (float) SessionStore::read('config_' . $username . '.txt_total_money');
		$difference = (-$initial_money - $total_deposit_withdraw) + $total_money_logs;
		$total_money_logs -= $total_deposit_withdraw;
		$today_money[0] -= $today_money[2];
		$total_logs = number_format($total_money_logs, 2, '.', '');

		return array(
			'today_money' => array(
				number_format($today_money[0], 2, '.', ''),
				$today_money[1],
				$today_money[2]
			),
			'transactions' => ReportDataService::getTransactions($username, $daily_filename, $transaction_id_field),
			'difference' => number_format($difference, 2, '.', ''),
			'total_money_logs' => $total_logs,
			'median_money' => number_format($files ? $total_money_logs / count($files) : 0, 2, '.', ''),
			'percent' => $today_money[1] > 0
				? number_format(((float) $today_money[0] / (float) $today_money[1]) * 100, 2, '.', '')
				: 0,
			'highest' => ReportDataService::getHighest($username, $daily_filename),
			'lowest' => ReportDataService::getLowest($username, $daily_filename),
			'files' => $files,
			'days' => $days
		);
	}

	private static function getCashFlows($username, $setting)
	{
		$cash_flows = SessionStore::read('config_' . $username . '.txt_' . $setting);
		return explode(',', $cash_flows === null ? '' : (string) $cash_flows);
	}
}

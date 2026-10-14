<?php

namespace Core\Reports;

use Core\Support\SessionStore;

class AccountStatementService
{
	public static function build($username, $date_collect, $time, $interval_minutes = 60)
	{
		$rows = array();
		$time_next = strtotime('+' . (int) $interval_minutes . ' minutes', (int) $time);
		$collect_deposit = 0;
		$collect_withdraw = 0;
		$collect_coin = 0;
		$collect_cur = 0;

		if (!is_array($date_collect)) {
			throw new \UnexpectedValueException('Collected account data must be an array.');
		}

		foreach ($date_collect as $coin => $year_data) {
			if (!is_array($year_data)) {
				continue;
			}

			foreach ($year_data as $year => $entries) {
				if (!is_array($entries)) {
					continue;
				}

				$year_coin_total = 0;
				$price = 0;
				foreach ($entries as $timestamp => $entry) {
					if ((int) $time_next <= (int) $timestamp) {
						continue;
					}

					$price = $entry['price'];
					$collect_coin += (float) $entry['coin'];
					$collect_cur += (float) $entry['cur'];
					$year_coin_total += (float) $entry['coin'];
				}

				$deposit_totals = SessionStore::read($username . '.txt_deposit_total_' . $year);
				$withdraw_totals = SessionStore::read($username . '.txt_withdraw_total_' . $year);
				$collect_deposit += self::sumCoinCashFlows($deposit_totals, $coin, $time_next);
				$collect_withdraw += self::sumCoinCashFlows($withdraw_totals, $coin, $time_next);

				$profit = 0;
				$profit_value = 0;
				$total = 0;
				if ($price != 0) {
					$profit = (($collect_cur / $price) + $collect_coin) - $collect_deposit + $collect_withdraw;
					$profit_value = $profit * $price;
					$total = ($collect_coin * $price) + $collect_cur;
				}

				if ($year_coin_total != 0) {
					$rows[] = array(
						'year' => $year,
						'coin' => $coin,
						'price' => $price,
						'balance' => $collect_cur,
						'coins' => $collect_coin,
						'total' => $total,
						'profit' => $profit,
						'profit_value' => $profit_value
					);
				}
			}
		}

		return $rows;
	}

	private static function sumCoinCashFlows($totals, $coin, $time_next)
	{
		if (!is_array($totals) || !isset($totals[$coin]) || !is_array($totals[$coin])) {
			return 0;
		}

		$total = 0;
		foreach ($totals[$coin] as $timestamp => $amount) {
			if ((int) $time_next > (int) $timestamp) {
				$total += abs((float) $amount);
			}
		}

		return $total;
	}
}

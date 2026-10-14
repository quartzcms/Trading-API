<?php

namespace Core\Reports;

class ExchangeTotalService
{
	public static function build($data, $date_collect, $time, $interval_minutes, $withdraw_mode, $completed_status)
	{
		if (!is_array($data) || !is_array($date_collect)) {
			throw new \UnexpectedValueException('Exchange totals require transaction and account data arrays.');
		}

		$time = (int) $time;
		$time_next = strtotime('+' . (int) $interval_minutes . ' minutes', $time);
		$withdraw_mode = $withdraw_mode === 'on';
		$completed_status = strtolower($completed_status);

		foreach ($data as &$transactions) {
			if (is_array($transactions)) {
				usort($transactions, static function ($first, $second) {
					return ($first['time'] ?? 0) <=> ($second['time'] ?? 0);
				});
			}
		}
		unset($transactions);

		$result = array(
			'deposit' => array(),
			'withdraw' => array(),
			'filled' => array(),
			'total' => array(),
			'account' => array(),
			'money_in' => array(),
			'last_price_ratio' => array(),
			'diff' => array(),
			'buys' => array(),
			'sells' => array(),
			'year' => 0,
			'price' => array(),
			'x' => array(),
			'y' => 0,
			'lines' => array(),
			'cur_sym' => array(),
			'coin' => array()
		);

		foreach ($data as $symbol => $transactions) {
			if (!is_array($transactions)) {
				continue;
			}

			foreach ($transactions as $transaction_index => $transaction) {
				if (!isset($transaction['time'], $transaction['type'])) {
					continue;
				}

				$transaction_time = (int) $transaction['time'];
				if (!(($time <= $transaction_time && $time_next >= $transaction_time)
					|| ($withdraw_mode && $time_next >= $transaction_time))) {
					continue;
				}

				$year = date('Y', $transaction_time);
				$date = date('Y-m-d H:i:s', $transaction_time);
				$result['year'] = $year;
				$type = $transaction['type'];
				$include_total = ($withdraw_mode && ($type === 'withdraw' || $type === 'deposit'))
					|| (!$withdraw_mode && ($type === 'transaction' || $type === 'withdraw' || $type === 'deposit'));

				if ($include_total) {
					$result['total'][$symbol][$transaction_index] = isset($result['total'][$symbol][$transaction_index])
						? $result['total'][$symbol][$transaction_index]
						: array();
					$result['cur_sym'][$symbol][$year] = $result['cur_sym'][$symbol][$year] ?? 0;
					$result['coin'][$symbol][$year] = $result['coin'][$symbol][$year] ?? 0;
				}

				foreach (array('lines', 'account', 'filled', 'withdraw', 'deposit', 'diff', 'last_price_ratio', 'money_in', 'buys', 'sells', 'price', 'x') as $key) {
					$result[$key][$symbol] = $result[$key][$symbol] ?? array();
				}
				foreach (array('account', 'filled', 'withdraw', 'deposit', 'diff', 'last_price_ratio', 'money_in', 'buys', 'sells', 'price', 'x') as $key) {
					$result[$key][$symbol][$year] = $result[$key][$symbol][$year] ?? 0;
				}

				if ($type === 'deposit') {
					$amount = (float) ($transaction['amount'] ?? 0);
					$result['account'][$symbol][$year] += $amount;
					$result['coin'][$symbol][$year] += $amount;
					$result['total'][$symbol][$transaction_index] = array(
						'total' => $result['account'][$symbol][$year],
						'price' => 0,
						'status' => 0,
						'diff' => 0,
						'coin' => $transaction['coin'] ?? '',
						'type' => $type,
						'time' => $date,
						'fees' => number_format((float) ($transaction['fees'] ?? 0), 6, '.', ''),
						'id' => $transaction['id'] ?? '',
						'side' => 0
					);
					$result['deposit'][$symbol][$year]++;
				} elseif ($type === 'withdraw') {
					$fees = (float) ($transaction['amount'] ?? 0) + (float) ($transaction['fees'] ?? 0);
					$result['account'][$symbol][$year] -= $fees;
					$result['coin'][$symbol][$year] -= $fees;
					$result['total'][$symbol][$transaction_index] = array(
						'price' => 0,
						'status' => 0,
						'total' => $result['account'][$symbol][$year],
						'diff' => 0,
						'coin' => $transaction['coin'] ?? '',
						'type' => $type,
						'time' => $date,
						'fees' => number_format((float) ($transaction['fees'] ?? 0), 6, '.', ''),
						'id' => $transaction['id'] ?? '',
						'side' => 0
					);
					$result['withdraw'][$symbol][$year]++;
				} elseif ($type === 'transaction' && !$withdraw_mode && $include_total) {
					$price = (float) ($transaction['price'] ?? 0);
					$amount = (float) ($transaction['amount'] ?? 0);
					$fees = (float) ($transaction['fees'] ?? 0);
					$side = (string) ($transaction['side'] ?? '');
					$status = (string) ($transaction['status'] ?? '');

					$result['price'][$symbol][$year] += $price;
					$result['x'][$symbol][$year]++;
					$result['y']++;
					$result['total'][$symbol][$transaction_index] = array(
						'side' => $side,
						'price' => $price,
						'total' => $price * $result['money_in'][$symbol][$year],
						'status' => $status,
						'fees' => number_format($fees, 6, '.', '')
					);

					if (strtolower($status) === $completed_status) {
						if (strtolower($side) === 'buy') {
							$previous_coin = $result['coin'][$symbol][$year];
							$result['coin'][$symbol][$year] = ($result['cur_sym'][$symbol][$year] / $price) + $amount;
							$result['cur_sym'][$symbol][$year] = ($previous_coin * $price) - ($amount * $price);
							$result['buys'][$symbol][$year] += $amount;
							$result['money_in'][$symbol][$year] += $amount;
							$result['last_price_ratio'][$symbol][$year] = $result['money_in'][$symbol][$year] / $price;
							$result['diff'][$symbol][$year] = $amount * $price;
						} else {
							$previous_coin = $result['coin'][$symbol][$year];
							$result['coin'][$symbol][$year] = ($result['cur_sym'][$symbol][$year] / $price) - $amount;
							$result['cur_sym'][$symbol][$year] = ($previous_coin * $price) + ($amount * $price);
							$sell_amount = $amount + $fees;
							$result['sells'][$symbol][$year] += $sell_amount;
							$result['money_in'][$symbol][$year] -= $sell_amount;
							$result['diff'][$symbol][$year] = (($result['last_price_ratio'][$symbol][$year] * $price) - ($amount * $price)) - ($fees * $price);
							$result['account'][$symbol][$year] += $result['diff'][$symbol][$year] / $price;
						}
						$result['filled'][$symbol][$year]++;
					}

					$result['total'][$symbol][$transaction_index]['diff'] = $result['diff'][$symbol][$year];
					$result['total'][$symbol][$transaction_index]['coin'] = $transaction['coin'] ?? '';
					$result['total'][$symbol][$transaction_index]['type'] = $type;
					$result['total'][$symbol][$transaction_index]['time'] = $date;
					$result['total'][$symbol][$transaction_index]['id'] = $transaction['id'] ?? '';
				}

				if (isset($result['total'][$symbol][$transaction_index])
					&& count(array_intersect(
						array('id', 'type', 'time', 'price', 'total', 'status', 'coin', 'side', 'diff', 'fees'),
						array_keys($result['total'][$symbol][$transaction_index])
					)) === 10) {
					$row = $result['total'][$symbol][$transaction_index];
					$result['lines'][$symbol][] = '"' . $row['id'] . '","' . $row['type'] . '","' . $row['time'] . '","'
						. $row['price'] . '","' . $row['total'] . '","' . $row['status'] . '","' . $row['coin'] . '","'
						. $row['side'] . '","' . $row['diff'] . '","' . $row['fees'] . '"';
				}
			}
		}

		if (!$withdraw_mode) {
			foreach ($result['coin'] as $symbol => $years) {
				foreach ($years as $year => $balance) {
					$price_count = $result['x'][$symbol][$year] ?? 0;
					$average_price = $price_count && ($result['price'][$symbol][$year] ?? 0) != 0
						? $result['price'][$symbol][$year] / $price_count
						: 0;
					$date_collect[$symbol][$year][$time] = array(
						'coin' => $balance,
						'cur' => $result['cur_sym'][$symbol][$year] ?? 0,
						'price' => $average_price
					);
				}
			}
		}

		$result['date_collect'] = $date_collect;
		return $result;
	}
}

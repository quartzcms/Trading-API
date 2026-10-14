<?php

namespace Core\Reports;

use Core\Support\SessionStore;

class TotalPageRequestService
{
	public static function clearRequestedCaches($username, $query, $cache_keys, $balance_keys)
	{
		if (isset($query['cache']) && $query['cache'] === 'clear') {
			self::clearSessionValues($username, array_merge(array('date_collect'), $cache_keys));
		}

		if (isset($query['balance']) && $query['balance'] === 'clear') {
			self::clearSessionValues($username, $balance_keys);
		}
	}

	public static function resolve($query, $post, &$date_collect, $default_currencies, $default_pairs, $interval_minutes)
	{
		if (!is_array($date_collect)) {
			$date_collect = array();
		}

		$currencies = self::getCurrencyList($query, $post, 'currencies', $default_currencies);
		$currency_pairs = self::getCurrencyList($query, $post, 'currencies_full', $default_pairs);
		$year_start = strtotime(((int) date('Y') - 1) . '-01-01');
		$requested_time = isset($query['time']) ? $query['time'] : null;

		if (isset($post['date'])) {
			$posted_date = (string) $post['date'];
			$posted_timestamp = strtotime(date('Y-m-d', strtotime($posted_date)));
			$requested_time = $posted_timestamp;
			$date_parts = explode('-', $posted_date);
			$posted_year = $date_parts[0];

			foreach ($date_collect as $currency => &$years) {
				if (!is_array($years) || empty($years[$posted_year]) || !is_array($years[$posted_year])) {
					continue;
				}

				ksort($years[$posted_year]);
				$minimum_time = strtotime('-' . (int) $interval_minutes . ' minutes', $posted_timestamp);
				foreach ($years[$posted_year] as $timestamp => $entry) {
					if ($timestamp >= $minimum_time) {
						$requested_time = $timestamp;
						break;
					}
				}
			}
			unset($years);
		}

		if ($requested_time === null) {
			$requested_time = $year_start;
		}

		$time = (int) $requested_time;
		$time_end = strtotime('now');
		$withdraw_mode = isset($query['withdraw']) ? $query['withdraw'] : 'off';

		return array(
			'currencies' => $currencies,
			'currency_pairs' => $currency_pairs,
			'withdraw' => $withdraw_mode,
			'time_first' => $year_start,
			'time_end' => $time_end,
			'time' => $time,
			'time_next' => strtotime('+' . (int) $interval_minutes . ' minutes', $time)
		);
	}

	private static function clearSessionValues($username, $keys)
	{
		foreach (array_unique($keys) as $key) {
			SessionStore::write($username . '.txt_' . $key, array());
		}
	}

	private static function getCurrencyList($query, $post, $key, $fallback)
	{
		if (isset($post[$key])) {
			$value = $post[$key];
		} elseif (isset($query[$key])) {
			$value = $query[$key];
		} else {
			$value = null;
		}

		if ($value === null || $value === '') {
			$value = $fallback;
		}

		if (!is_scalar($value) && $value !== null) {
			throw new \UnexpectedValueException('Currency selections must be scalar values.');
		}

		return explode(',', (string) $value);
	}
}

<?php

namespace Core\Market;

use Core\Support\SessionStore;

class PriceSummaryService
{
	public static function build($username, $request)
	{
		if (SessionStore::read('config_' . $username . '.txt_api_key') == '') {
			return false;
		}

		$buy_price = SessionStore::read($username . '.txt_current_price_value');
		$buy_balance = SessionStore::read($username . '.txt_buy_balance');
		$buy_balance = ($buy_balance != '') ? $buy_balance : 1;
		$sell_balance = SessionStore::read($username . '.txt_sell_balance');
		$get_total = (float) SessionStore::read($username . '.txt_price_today');
		$total_money_logs = (float) SessionStore::read($username . '.txt_price_total_from_today');
		$total_money_difference = (float) SessionStore::read($username . '.txt_total_money_difference');
		$total_money_start = SessionStore::read('config_' . $username . '.txt_total_money');
		$trigger_lowest_price = (float) SessionStore::read($username . '.txt_trigger_lowest_price');
		$price_total_median = (float) SessionStore::read($username . '.txt_price_total_median');
		$time_lowest_limit = SessionStore::read($username . '.txt_time_lowest_limit');
		$sold_at = SessionStore::read($username . '.txt_price_value');
		$sold_at = ($sold_at != '') ? $sold_at : 0;
		$highest = SessionStore::read($username . '.txt_highest_today');
		$highest = ($highest != '') ? $highest : 0;
		$lowest = SessionStore::read($username . '.txt_lowest_today');
		$lowest = ($lowest != '') ? $lowest : 0;
		$today_range = $highest - $lowest;
		$price_increase = SessionStore::read('config_' . $username . '.txt_price_increase');
		$rsi_top = SessionStore::read('config_' . $username . '.txt_rsi_top');
		$rsi_bottom = SessionStore::read('config_' . $username . '.txt_rsi_bottom');
		$rsi_bottom_long = SessionStore::read('config_' . $username . '.txt_rsi_bottom_long');
		$price_reached = SessionStore::read('config_' . $username . '.txt_price_reached');
		$price_reached_added = $price_reached + $price_increase;
		$top_level_trigger = SessionStore::read($username . '.txt_top_level_trigger');
		$operate = SessionStore::read($username . '.txt_operate');
		$enough_money = SessionStore::read($username . '.txt_enough_money');
		$sell_all_state = self::applyRequestToggles($username, $request, $buy_price, $price_increase);

		$step_one_extra_long = SessionStore::read($username . '.txt_rsi_extra_long');
		$step_one_extra_long = ($step_one_extra_long == '') ? 50 : $step_one_extra_long;
		$step_one_long = SessionStore::read($username . '.txt_rsi_long');
		$step_one_long = ($step_one_long == '') ? 50 : $step_one_long;
		$step_one = SessionStore::read($username . '.txt_rsi');
		$step_one = ($step_one == '') ? 50 : $step_one;
		$detect_count = 'no';
		if (strval($step_one) < strval($rsi_top) && strval($step_one) > strval($rsi_bottom)) {
			$detect_count = 'yes';
		}

		$range_stagnant_extra_long = number_format($step_one_extra_long, 2, '.', '');
		$range_stagnant_long = number_format($step_one_long, 2, '.', '');
		$range_stagnant = number_format($step_one, 2, '.', '');
		$current_range_ai = 0;
		$price_ai = SessionStore::read($username . '.txt_price_ai');
		if (strval($price_ai) > '0' && strval($buy_price) > '0') {
			$current_range_ai = $buy_price - $price_ai;
		}

		$total_money = (-((float) $total_money_start) - $total_money_difference) + $total_money_logs;
		$total_money_logs -= $total_money_difference;
		$total_balance = $buy_balance + $sell_balance;
		$percent = $total_balance != 0 ? ($get_total / $total_balance) * 100 : 0;
		$estimation = number_format(($buy_price != 0 ? ($sell_balance / $buy_price) * $price_reached_added : 0), 2, '.', '');

		$array_server_time = SessionStore::read($username . '.txt_server_time_average');
		$array_server_time = !empty($array_server_time) ? $array_server_time : array(1);
		$server_time_average = 0;
		foreach ($array_server_time as $value) {
			$server_time_average += $value;
		}
		$server_time_average /= count($array_server_time);
		$server_time = number_format($server_time_average, 2, '.', '');

		$price_array = SessionStore::read($username . '.txt_price_array');
		$stats = self::buildTransactionStats($username, $price_array, $buy_price);
		$possible = self::buildPossibleFlags(
			$step_one_long,
			$rsi_bottom_long,
			$price_reached,
			$buy_price,
			$top_level_trigger,
			$operate,
			$enough_money
		);

		return array(
			'price' => $buy_price,
			'price_sell' => number_format($sell_balance, 2, '.', ''),
			'price_buy' => number_format($buy_balance, 2, '.', ''),
			'total' => number_format($buy_balance + $sell_balance, 2, '.', ''),
			'difference' => number_format($total_money, 2, '.', ''),
			'total_today' => number_format($get_total, 2, '.', ''),
			'price_sell_all' => $sell_all_state['price_sell_all'],
			'sold_at' => number_format($sold_at, 2, '.', ''),
			'limit_high' => number_format($price_reached_added, 2, '.', ''),
			'limit_low' => number_format($price_reached, 2, '.', ''),
			'stagnant' => $detect_count,
			'highest' => number_format($highest, 2, '.', ''),
			'lowest' => number_format($lowest, 2, '.', ''),
			'low_detect' => number_format($trigger_lowest_price, 2, '.', ''),
			'in_logs' => number_format($total_money_logs, 2, '.', ''),
			'range' => number_format($current_range_ai, 2, '.', ''),
			'percent' => number_format($percent, 2, '.', ''),
			'median' => number_format($price_total_median, 2, '.', ''),
			'range_today' => number_format($today_range, 2, '.', ''),
			'estimation' => $estimation,
			'stats' => $stats,
			'range_stagnant' => $range_stagnant,
			'range_stagnant_long' => $range_stagnant_long,
			'range_stagnant_extra_long' => $range_stagnant_extra_long,
			'server_time' => $server_time,
			'possible' => $possible,
			'time_lowest_limit' => $time_lowest_limit
		);
	}

	private static function applyRequestToggles($username, $request, $buy_price, $price_increase)
	{
		if (isset($request['detect_lowest']) && $request['detect_lowest'] == 1) {
			if (isset($request['detect_lowest_switch']) && $request['detect_lowest_switch'] == 'off') {
				SessionStore::write($username . '.txt_price_reached_block_buy_start', 0);
				SessionStore::write($username . '.txt_highest_today_sell', $buy_price);
			} elseif (isset($request['detect_lowest_switch']) && $request['detect_lowest_switch'] == 'on') {
				SessionStore::write($username . '.txt_price_reached_block_buy_start', 1);
				SessionStore::write($username . '.txt_highest_today_sell', $buy_price);
			}
			SessionStore::write($username . '.txt_trigger_lowest_price', 0);
			SessionStore::write($username . '.txt_time', time());
		}

		$toggle_price_reached_sell_all = SessionStore::read($username . '.txt_toggle_price_reached_sell_all');
		if ($toggle_price_reached_sell_all == '') {
			$toggle_price_reached_sell_all = 0;
		}

		if (isset($request['sell_unlock']) && $request['sell_unlock'] == 1) {
			$remove_to_price = (isset($request['sell_unlock_minus_10']) && $request['sell_unlock_minus_10'] == 1)
				? 0.10 * $price_increase
				: 0;
			$remove_to_price = (isset($request['sell_unlock_minus_20']) && $request['sell_unlock_minus_20'] == 1)
				? 0.20 * $price_increase
				: $remove_to_price;
			SessionStore::write('config_' . $username . '.txt_price_reached', ($buy_price + $remove_to_price));
			$toggle_price_reached_sell_all = 0;
		}

		if (isset($request['sell_all']) && $request['sell_all'] == 1 && $toggle_price_reached_sell_all == 0) {
			$add_to_price = (isset($request['sell_all_plus_10']) && $request['sell_all_plus_10'] == 1)
				? 0.10 * $price_increase
				: 0;
			$add_to_price = (isset($request['sell_all_plus_20']) && $request['sell_all_plus_20'] == 1)
				? 0.20 * $price_increase
				: $add_to_price;
			SessionStore::write('config_' . $username . '.txt_price_reached', ($buy_price - $price_increase) - $add_to_price);
			$toggle_price_reached_sell_all = 1;
			SessionStore::write($username . '.txt_price_position', 'none');
		}

		$price_sell_all = '';
		if (((SessionStore::read($username . '.txt_price_position') == ''
				|| SessionStore::read($username . '.txt_price_operate') == 1)
				&& $toggle_price_reached_sell_all == 1)
			|| SessionStore::read('config_' . $username . '.txt_sell_all_lock_high') == 0
			|| SessionStore::read('config_' . $username . '.txt_price_switch') == 0) {
			$toggle_price_reached_sell_all = 0;
			$price_sell_all = 'done';
		}
		SessionStore::write($username . '.txt_toggle_price_reached_sell_all', $toggle_price_reached_sell_all);

		return array('price_sell_all' => $price_sell_all);
	}

	private static function buildTransactionStats($username, $price_array, $buy_price)
	{
		$transactions = array();
		$align = 'center';
		$column_class = 'inline-block';
		if (!empty($price_array) && is_array($price_array)) {
			if (count($price_array) > 1) {
				$column_class = 'col-md-6';
			}
			if (count($price_array) > 2) {
				$column_class = 'col-md-4';
			}
			if (count($price_array) > 3) {
				$column_class = 'col-md-3';
			}

			$quantity_fields = array(
				'binance_' => 'quantity',
				'coinbase_' => 'size',
				'kraken_' => 'volume'
			);
			$i = 0;
			foreach ($price_array as $transaction) {
				foreach ($quantity_fields as $exchange => $quantity_field) {
					if (strpos($username, $exchange) !== false) {
						$desired_amount = ($transaction['query'][$quantity_field] * $transaction['price'])
							+ SessionStore::read('config_' . $username . '.txt_desired_amount');
						$starting_amount = $transaction['query'][$quantity_field] * $transaction['price'];
						$current_amount = $transaction['query'][$quantity_field] * $buy_price;
						$selling_fee = SessionStore::read('config_' . $username . '.txt_selling_fee');
						$buying_fee = SessionStore::read('config_' . $username . '.txt_buying_fee');
						$total_tx = number_format(
							(($current_amount - $starting_amount)
								- ($current_amount * $selling_fee)
								- ($starting_amount * $buying_fee)),
							2,
							'.',
							''
						);
						$selling_price = (($desired_amount
								+ ($desired_amount * $selling_fee)
								+ ($starting_amount * $buying_fee))
								/ $starting_amount) * $transaction['price'];
						$transactions[$total_tx . $i] = '<div align="' . $align . '" class="' . $column_class . '"><p><span class=\'text-success\'>ID: '
							. $transaction['txid'] . '</span> <span class=\'text-warning\'>INI: '
							. number_format($transaction['price'], 2, '.', '') . '</span> <span class=\'text-success\'>QTY: '
							. number_format($starting_amount, 2, '.', '') . '</span> <span class=\'text-info\'>RES: '
							. (strval($total_tx) > '0' ? '+' . $total_tx : $total_tx) . '</span> <span class=\'text-danger\'>SELL: '
							. number_format($selling_price, 2, '.', '') . '</span></p></div>';
					}
				}
				$i++;
			}
		}

		if (!empty($transactions)) {
			krsort($transactions);
		}

		return '<div class="row" align="' . $align . '">' . implode('', $transactions) . '</div>';
	}

	private static function buildPossibleFlags($step_one_long, $rsi_bottom_long, $price_reached, $buy_price, $top_level_trigger, $operate, $enough_money)
	{
		return array(
			'rsi' => strval($step_one_long) < strval($rsi_bottom_long) ? 'yes' : 'no',
			'price' => strval($price_reached) < strval($buy_price) ? 'yes' : 'no',
			'level' => $top_level_trigger == 'yes' ? 'yes' : 'no',
			'time' => $operate == 0 ? 'yes' : 'no',
			'money' => $enough_money == 1 ? 'yes' : 'no'
		);
	}
}

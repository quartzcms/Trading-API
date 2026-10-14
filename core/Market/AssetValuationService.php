<?php

namespace Core\Market;

use Core\Support\SessionStore;

class AssetValuationService
{
	public static function build($username)
	{
		if (SessionStore::read('config_' . $username . '.txt_api_key') == '') {
			return false;
		}

		$coin_array = SessionStore::read($username . '.txt_coin_array');
		$price_array_list = SessionStore::read($username . '.txt_price_array_list');
		$build_value = array();

		if (!empty($coin_array) && !empty($price_array_list)) {
			foreach ($coin_array as $coin) {
				$price_array_name = $coin['name'];
				if (!isset($price_array_list[$price_array_name])) {
					continue;
				}

				$actual_price = $price_array_list[$price_array_name];
				$decimal = explode('.', $actual_price);
				$rounded_price = (string) $actual_price < '0'
					? round($actual_price, (int) strlen($decimal[1]) - 1)
					: round($actual_price, -((int) strlen($decimal[0]) - 1));

				$build_value[$price_array_name]['value'] = (string) $rounded_price > '0'
					? $coin['value'] / $rounded_price
					: $coin['value'] / 1;
				$build_value[$price_array_name]['label'] = 'Current: '
					. number_format($coin['value'] * $price_array_list[$price_array_name], 2, '.', '')
					. '<br />Rounded Price: ' . $rounded_price;
			}
		}

		return array('build_value' => $build_value);
	}
}

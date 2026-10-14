<?php

namespace Core\Market;

class PortfolioProgressService
{
	public static function build($coins, $prices)
	{
		if (!is_array($coins) || !is_array($prices)) {
			throw new \UnexpectedValueException('Portfolio coins and prices must be arrays.');
		}

		$progress = array();
		foreach ($coins as $coin) {
			if (!isset($coin['name'], $coin['value']) || !isset($prices[$coin['name']])) {
				continue;
			}

			$actual_price = $prices[$coin['name']];
			if (!is_numeric($actual_price) || !is_numeric($coin['value'])) {
				throw new \UnexpectedValueException('Portfolio values and prices must be numeric.');
			}

			$actual_price = (float) $actual_price;
			$coin_value = (float) $coin['value'];
			$rounding_precision = $actual_price == 0
				? 0
				: (int) -floor(log10(abs($actual_price)));
			$rounded_price = round($actual_price, $rounding_precision);
			$progress[] = array(
				'name' => $coin['name'],
				'rounded_price' => $rounded_price,
				'width' => $coin_value / ($rounded_price > 0 ? $rounded_price : 1),
				'current_value' => number_format($coin_value * $actual_price, 2, '.', '')
			);
		}

		return $progress;
	}
}

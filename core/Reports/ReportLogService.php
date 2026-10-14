<?php

namespace Core\Reports;

class ReportLogService
{
	public static function getEntries($username, $date, $transaction_id_field, $show_transactions)
	{
		$log_path = 'config/logs/' . $username . '/' . $date . '_' . $username . '.txt';
		if (!is_file($log_path)) {
			return array();
		}

		$contents = file_get_contents($log_path);
		if ($contents === false) {
			throw new \RuntimeException('Unable to read report log: ' . $log_path);
		}

		$lines = explode("\n", $contents);
		arsort($lines);
		$entries = array();
		$transaction_count = 0;

		foreach ($lines as $line) {
			if ($line === '') {
				continue;
			}

			$line_parts = explode('=', $line, 2);
			if (!isset($line_parts[1])) {
				continue;
			}

			$line_date = explode('*', $line_parts[0]);
			$line_data = explode('|', $line_parts[1]);
			$json_data = isset($line_data[0]) ? json_decode($line_data[0], true) : null;
			if (empty($json_data)) {
				continue;
			}

			$is_sentiment = strpos($line_data[0], 'Sentiment') !== false;
			if ($is_sentiment) {
				$json_data = self::decodeSentiment($json_data);
				$message = implode('<br />', $json_data);
			} else {
				$is_transaction = strpos($line_data[0], '"' . $transaction_id_field . '"') !== false;
				if ($is_transaction) {
					$transaction_count++;
				}

				$message = $is_transaction && !$show_transactions
					? 'Transaction entry. This entry is hidden for security purpose.'
					: ReportDataService::flattenArray($json_data);
			}

			$entries[] = array(
				'date_parts' => $line_date,
				'data_parts' => $line_data,
				'message' => $message,
				'message_class' => self::getMessageClass($line_data[0]),
				'is_sentiment' => $is_sentiment,
				'is_transaction' => isset($is_transaction) && !$is_sentiment ? $is_transaction : false,
				'transaction_count' => $transaction_count,
				'action' => isset($line_data[3]) ? $line_data[3] : '',
				'past_range_parts' => isset($line_data[2]) ? explode('*', $line_data[2]) : array(),
				'method' => isset($line_data[6]) ? $line_data[6] : '',
				'sub_method' => isset($line_data[8]) ? $line_data[8] : '',
				'future_median' => isset($line_data[1]) ? $line_data[1] : '',
				'past_ratio' => isset($line_data[5]) ? $line_data[5] : '',
				'rsi' => isset($line_data[9]) ? $line_data[9] : '0 / 0',
				'amount' => isset($line_data[4]) ? $line_data[4] : ''
			);
			unset($is_transaction);
		}

		return $entries;
	}

	private static function decodeSentiment($items)
	{
		foreach ($items as $key => $item) {
			$item = htmlspecialchars_decode($item, ENT_QUOTES);
			$item = str_replace('88888888888888888888888888888888888', '=', $item);
			$item = str_replace('999999999999999999999999999', '"', $item);
			$item = str_replace(array('((^))', '(^)'), '=', $item);
			$item = str_replace('((_))', '*', $item);
			$items[$key] = str_replace('((-))', '|', $item);
		}

		return $items;
	}

	private static function getMessageClass($message)
	{
		$class_by_message = array(
			'Open Orders Count' => 'text-info',
			'There is not enough money' => 'text-balance',
			'The price is stagnant' => 'text-balance',
			'Buying is blocked' => 'text-warning',
			'End of the selling all' => 'text-selling',
			'Gained' => 'text-success',
			'In the selling all period' => 'text-selling',
			'No transaction has been' => 'text-balance',
			'Price limit is reached' => 'text-warning',
			'Out of the time' => 'text-warning',
			'No triggers were fired' => 'text-balance',
			'No purchase is allowed' => 'text-balance',
			"The today's total has reached" => 'text-warning'
		);

		foreach ($class_by_message as $phrase => $class) {
			if (strpos($message, $phrase) !== false) {
				return $class;
			}
		}

		return '';
	}
}

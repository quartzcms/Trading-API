<?php

namespace Core\Reports;

class TradingLogResponseService
{
	public static function buildForUser($username, $current_date, $current_time, $has_session_date_time, $session_date_time)
	{
		$file_path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'logs'
			. DIRECTORY_SEPARATOR . $username . DIRECTORY_SEPARATOR . $current_date . '_' . $username . '.txt';
		$file_content = '';

		if ($username !== null && is_file($file_path)) {
			$file_content = @file_get_contents($file_path);
			if ($file_content === false) {
				$file_content = '';
			}
		}

		return self::build(
			$file_content,
			$current_date,
			$current_time,
			$has_session_date_time,
			$session_date_time,
			$username !== null
		);
	}

	public static function build($file_content, $current_date, $current_time, $has_session_date_time, $session_date_time, $has_user)
	{
		$refresh = $has_user && empty($file_content);
		$lines = explode("\n", $file_content);
		asort($lines);

		$entries = array();
		$empty_entry = array(
			'today' => $current_date,
			'refresh' => $refresh,
			'date' => '',
			'price' => '',
			'data' => '',
			'future' => '',
			'columns' => '',
			'ratio' => '',
			'method' => '',
			'past' => '',
			'action' => '',
			'sub_method' => '',
			'amount' => '',
			'stagnant' => ''
		);

		foreach ($lines as $line) {
			if (empty($line)) {
				continue;
			}

			$line_parts = explode('=', $line);
			$line_date = isset($line_parts[0]) ? explode('*', $line_parts[0]) : array();
			$date_time = isset($line_date[0]) ? explode(' ', $line_date[0]) : array();
			$time = isset($date_time[1]) ? explode(':', $date_time[1]) : array();

			if (!isset($date_time[2], $line_date[0], $line_date[1], $time[0], $time[1], $time[2])) {
				continue;
			}

			$line_cursor = $time[0] . $time[1] . $time[2] . $date_time[2];
			$is_current_minute = !$has_session_date_time
				&& (int) $current_time === (int) ($time[0] . $time[1]);
			$is_newer_than_cursor = $has_session_date_time
				&& (string) $session_date_time < (string) $line_cursor;
			if (!$is_current_minute && !$is_newer_than_cursor) {
				continue;
			}

			$line_data = isset($line_parts[1]) ? explode('|', $line_parts[1]) : array();
			$index = count($entries);
			$entries['value' . $index] = array(
				'today' => $current_date,
				'refresh' => $refresh,
				'date' => $line_date[0],
				'price' => $line_date[1],
				'data' => isset($line_data[0]) ? $line_data[0] : 0,
				'future' => isset($line_data[1]) ? $line_data[1] : 0,
				'columns' => isset($line_data[7]) ? $line_data[7] : 0,
				'ratio' => isset($line_data[5]) ? $line_data[5] : 0,
				'method' => isset($line_data[6]) ? $line_data[6] : 0,
				'past' => isset($line_data[2]) ? $line_data[2] : 0,
				'action' => isset($line_data[3]) ? $line_data[3] : 0,
				'sub_method' => isset($line_data[8]) ? $line_data[8] : 0,
				'amount' => isset($line_data[4]) ? $line_data[4] : 0,
				'stagnant' => isset($line_data[9]) ? $line_data[9] : 0
			);
			$session_date_time = $line_cursor;
			$has_session_date_time = true;
		}

		if (empty($entries)) {
			$entries = array('value0' => $empty_entry);
		}

		return array(
			'response' => array('count' => $entries),
			'has_session_date_time' => $has_session_date_time,
			'session_date_time' => $session_date_time
		);
	}
}

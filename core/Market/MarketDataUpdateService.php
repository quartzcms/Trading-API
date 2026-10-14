<?php

namespace Core\Market;

class MarketDataUpdateService
{
	public static function run($connect, $path, $stocks_array, $stocks_function, $data_key, $api_key, $default_timezone, $selected_crypto = null)
	{
		$stocks = $stocks_array;
		$selected_stocks = array();
		foreach ($stocks as $value) {
			$crypto_directory = $path . 'crypto/' . $value;
			foreach (array('', '/data_daily', '/data_monthly', '/data_weekly', '/data_yearly') as $suffix) {
				$directory = $crypto_directory . $suffix;
				if (!is_dir($directory) && !mkdir($directory)) {
					throw new \RuntimeException('Unable to create market data directory: ' . $directory);
				}
			}
		}

		foreach ($stocks as $key => $value) {
			if ($selected_crypto !== null) {
				if ($selected_crypto == $value) {
					$selected_stocks[$key] = $value;
					break;
				}
			} else {
				$selected_stocks[$key] = $value;
			}
		}
	$current_array = array();
	$date_array = array();
	$next_up_first_array = array();
	$next_up_second_array = array();
	$next_up_third_array = array();
	$get_price = 0;
	
	foreach($selected_stocks as $stock_symbol => $stock_folder){
		$current_time = date('Hi');
		$current_time = intval(ltrim($current_time, '0'));
		if($current_time >= 800 && $current_time <= 1600){
			$crypto = $stock_folder;
			$json = file_get_contents('https://www.alphavantage.co/query?function='.$stocks_function[$stock_symbol].'&symbol='.$stock_symbol.'&market=USD&interval=1min&apikey='.$api_key);
			$data = json_decode($json,true);
			
			if(isset($data['Meta Data']) && isset($data_key[$stock_symbol]) && isset($data[$data_key[$stock_symbol]])){
				$time_zone = 'America/New_York';
				
				if(is_array($data['Meta Data']) && !empty($data['Meta Data'])){
					foreach($data['Meta Data'] as $metadata_label => $metadata_value){
						if($metadata_label != '' && strpos($metadata_label, 'Time Zone') !== false){
							$time_zone = $metadata_value;
							break;
						}
					}
				}
				
				$min = date('i');
				if(isset($data_key[$stock_symbol]) && isset($data[$data_key[$stock_symbol]]) && is_array($data[$data_key[$stock_symbol]])){
					foreach($data[$data_key[$stock_symbol]] as $time_series_timestamp => $time_series_values){
						$date_now = new \DateTime($time_series_timestamp, new \DateTimeZone($time_zone));
						$min_now = $date_now->format('i');
						if(intval($min - 1) == intval($min_now) && isset($time_series_values['4. close']) && isset($time_series_values['1. open'])){
							$current = floatval($time_series_values['4. close']);
							$current_open = floatval($time_series_values['1. open']);
							$high = floatval($time_series_values['2. high']);
							$low = floatval($time_series_values['3. low']);
							$volume = floatval($time_series_values['5. volume']);
							$date_now->setTimezone(new \DateTimeZone($default_timezone));
							break;
						}
					}
				}
	
				$files = \Core\Support\Filesystem::directoryToArray($path.'crypto/'.$crypto.'/data_weekly');
				$count_file = 0;
				asort($files);
				if(count($files) > 0){
					foreach($files as $key => $value){
						$count_file++;
					}
					if($count_file > 14){
						unlink($path.'crypto/'.$crypto.'/data_weekly/'.$files[0]);
					}
				}
	
				$files = \Core\Support\Filesystem::directoryToArray($path.'crypto/'.$crypto.'/data_monthly');
				asort($files);
				$count_file = 0;
				if(count($files) > 0){
					foreach($files as $key => $value){
						$count_file++;
					}
					if($count_file > 30){
						unlink($path.'crypto/'.$crypto.'/data_monthly/'.$files[0]);
					}
				}
	
				$files = \Core\Support\Filesystem::directoryToArray($path.'crypto/'.$crypto.'/data_yearly');
				asort($files);
				$count_file = 0;
				if(count($files) > 0){
					foreach($files as $key => $value){
						$count_file++;
					}
					if($count_file > 365){
						unlink($path.'crypto/'.$crypto.'/data_yearly/'.$files[0]);
					}
				}
				
				if(isset($current) && isset($current_open) && isset($high) && isset($low) && isset($volume) && isset($data_key[$stock_symbol])){
					$file_date = $date_now->format('Y-m-d');
					
					self::createFiles($connect, $stock_symbol, $path.'crypto/'.$crypto.'/data_daily/', $data, '', $data_key[$stock_symbol], $date_now, $current, $current_open, $high, $low, $volume);
					self::createFiles($connect, $stock_symbol, $path.'crypto/'.$crypto.'/data_weekly/_'.$file_date.'_', $data, '', $data_key[$stock_symbol], $date_now, $current, $current_open, $high, $low, $volume);
					self::createFiles($connect, $stock_symbol, $path.'crypto/'.$crypto.'/data_monthly/_'.$file_date.'_', $data, '', $data_key[$stock_symbol], $date_now, $current, $current_open, $high, $low, $volume);
					self::createFiles($connect, $stock_symbol, $path.'crypto/'.$crypto.'/data_yearly/_'.$file_date.'_', $data, '', $data_key[$stock_symbol], $date_now, $current, $current_open, $high, $low, $volume);
					$date = $date_now->format('Hi');
	
					$timeframe = array();
					
					if(!use_session('base.txt_next_up'.$stock_symbol)){
						write_session('base.txt_next_up'.$stock_symbol, 0);
					}
					
					if(!use_session('base.txt_current'.$stock_symbol)){
						$previous_minute = $date_now->sub(new \DateInterval('PT1M'));
						$previous_minute = $previous_minute->format('Hi');
						$data_output = file_get_contents($path.'crypto/'.$crypto.'/data_daily/data_btc.txt');
						$old = 0;
						$lines = explode("\n", $data_output);
						if(count($lines) > 0){
							foreach($lines as $key => $value){
								$get_current = explode(':', $value);
								if(isset($get_current[0]) && isset($get_current[2]) && $get_current[0] == $previous_minute.'00') {
									$old = $get_current[2];
									break;
								}
							}
						}
						write_session('base.txt_old'.$stock_symbol, $old);
						write_session('base.txt_current'.$stock_symbol, $current);
					} else {
						write_session('base.txt_old'.$stock_symbol, use_session('base.txt_current'.$stock_symbol));
						write_session('base.txt_current'.$stock_symbol, $current);
					}
					
					$timeframe[$date.'00'] = use_session('base.txt_current'.$stock_symbol) - use_session('base.txt_old'.$stock_symbol);
					
					self::insertLine($connect, $stock_symbol, $path.'crypto/'.$crypto.'/data_daily/', $data, $date, $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
					self::insertLine($connect, $stock_symbol, $path.'crypto/'.$crypto.'/data_weekly/_'.$file_date.'_', $data, $date, $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
					self::insertLine($connect, $stock_symbol, $path.'crypto/'.$crypto.'/data_monthly/_'.$file_date.'_', $data, $date, $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
					self::insertLine($connect, $stock_symbol, $path.'crypto/'.$crypto.'/data_yearly/_'.$file_date.'_', $data, $date, $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
					self::increaseRisk($connect, $stock_symbol, $date, $path.'crypto/'.$crypto.'/data_daily/', $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
					self::increaseRisk($connect, $stock_symbol, $date, $path.'crypto/'.$crypto.'/data_weekly/_'.$file_date.'_', $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
					self::increaseRisk($connect, $stock_symbol, $date, $path.'crypto/'.$crypto.'/data_monthly/_'.$file_date.'_', $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
					self::increaseRisk($connect, $stock_symbol, $date, $path.'crypto/'.$crypto.'/data_yearly/_'.$file_date.'_', $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
				}
			}
		}
	}
	
	
	}

	private static function createFiles($connect, $stock_symbol, $folder, $data, $content, $data_type, $date_object, $current, $current_open, $high, $low, $volume){
		if(!file_exists($folder.'data_btc.txt')){
			$myfile = fopen($folder."data_btc.txt", "w") or die("Unable to open file! 123");
			if($content == ''){
				fwrite($myfile, "\n");
			} else {
				if(isset($data[$data_type]) && is_array($data[$data_type]) && !empty($data[$data_type])){
					foreach($data[$data_type] as $key => $value){
						$time = explode(' ', $key);
						if(isset($time[1])){
							$time_output = str_replace(':', '', $time[1]);
							if(isset($data[$data_type]) && isset($data[$data_type][$key - 1]) && is_array($data[$data_type][$key - 1]) && isset($data[$data_type][$key - 1]['4. close'])) {
								$total = floatval($value['4. close']) - floatval($data[$data_type][$key - 1]['4. close']);
							} else {
								$total = 0;
							}
							$line_insert = $time_output.":1:".$total.":".$current_open.":".$current.":".$high.":".$low.":".$volume."\n";
							fwrite($myfile, $line_insert);
						}
					}
				}
			}
			fclose($myfile);
		}
	}

	private static function insertLine($connect, $stock_symbol, $folder, $data, $date, $timeframe, $date_object, $current, $current_open, $high, $low, $volume){
		$data_output = file_get_contents($folder.'data_btc.txt');
		if($data_output != '' && strpos($data_output, $date.'00') === false && isset($timeframe[$date.'00']) && strval($timeframe[$date.'00']) < '500' && strval($timeframe[$date.'00']) > '-500'){
			if(!file_exists($folder.'data_btc.txt')){
				$myfile = fopen($folder."data_btc.txt", "w") or die("Unable to open file! 456");
			} else {
				$myfile = fopen($folder."data_btc.txt", "a") or die("Unable to open file! 789");
			}
			$txt = $date.'00'.":1:".$timeframe[$date.'00'].":".$current_open.":".$current.":".$high.":".$low.":".$volume."\n";
			fwrite($myfile, $txt);
			fclose($myfile);
		}
	}

	private static function increaseRisk($connect, $stock_symbol, $date, $folder, $timeframe, $date_object, $current, $current_open, $high, $low, $volume){
		$next_minute = $date_object->add(new \DateInterval('PT1M'));
		$next_minute = $next_minute->format('Hi');
		$data_output = file_get_contents($folder.'data_btc.txt');
		$lines = explode("\n", $data_output);
		if(is_array($lines) && count($lines) > 0){
			foreach($lines as $key => $value){
				$get_hour = explode(':', $value);
				if(isset($get_hour[0]) && isset($get_hour[1]) && isset($get_hour[2]) && $get_hour[0] == $next_minute.'00') {
					write_session('base.txt_next_up'.$stock_symbol, $get_hour[0].':'.$get_hour[1].':'.$get_hour[2]);
					break;
				}
			}
		}
		if(use_session('base.txt_next_up'.$stock_symbol)){
			$data_output = file_get_contents($folder.'data_btc.txt');
			$next_interval_values = explode(':', use_session('base.txt_next_up'.$stock_symbol));
			
			if(isset($timeframe[$date.'00'])){
				if(strval($timeframe[$date.'00']) < '500' && strval($timeframe[$date.'00']) > '-500'){
					$pattern = '/('.$date.'00):(\d):(.*)/i';
					$replacement = '${1}:${2}:'.$timeframe[$date.'00'].":".$current_open.":".$current.":".$high.":".$low.":".$volume;
					$data_output = preg_replace($pattern, $replacement, $data_output);
				}
				
				if(strval($timeframe[$date.'00']) > strval($next_interval_values[2])) {
					if(strpos($data_output, $date.'00:3') !== false){
						$pattern = '/('.$date.'00):(\d):(.*)/i';
						$replacement = '${1}:1:${3}';
						$data_output = preg_replace($pattern, $replacement, $data_output);
					} 
					if(strpos($data_output, $date.'00:2') !== false){
						$pattern = '/('.$date.'00):(\d):(.*)/i';
						$replacement = '${1}:3:${3}';
						$data_output = preg_replace($pattern, $replacement, $data_output);
					}
					if(strpos($data_output, $date.'00:1') !== false){
						$pattern = '/('.$date.'00):(\d):(.*)/i';
						$replacement = '${1}:2:${3}';
						$data_output = preg_replace($pattern, $replacement, $data_output);
					}
				}
			}
			
			if(!empty($data_output)){
				$myfile = fopen($folder."data_btc.txt", "w") or die("Unable to open file! 999");
				fwrite($myfile, $data_output);
				fclose($myfile);
			}
		}
	}

}

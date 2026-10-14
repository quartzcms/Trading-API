<?php

namespace Core\Trading;

class AiTradingService
{
	public static function run($connect, $path, $default_timezone)
	{
		$folder_config = \Core\Support\Filesystem::directoryToArray($path . 'config');
		$session_data_directory = \Core\Support\SessionStore::getDataDirectory();
		asort($folder_config);

		foreach ($folder_config as $username) {
			$date = new \DateTime();
			$date->setTimezone(new \DateTimeZone($default_timezone));
			if (!is_array($username) && $username !== '.htaccess') {
				$config_directory = $session_data_directory . DIRECTORY_SEPARATOR . 'config_' . $username;
				$user_directory = $session_data_directory . DIRECTORY_SEPARATOR . $username;
				foreach (array($config_directory, $user_directory) as $directory) {
					if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
						throw new \RuntimeException('Unable to create trading session directory: ' . $directory);
					}
				}
				self::sellBuy($connect, $username, $path . 'config', $date, array(), $path);
			}
		}
	}

	private static function sellBuy($connect, $username, $folder_config, $date_object, $options_data, $path){
		$api_key = use_session('config_'.$username.'_api_key');
		if($api_key == ''){
			return false;
		}
		$folder = $path.'crypto/'.use_session('config_'.$username.'_crypto').'/data_'.use_session('config_'.$username.'_estimation_folder').'/';
		$secret_key = use_session('config_'.$username.'_secret_key');
		$passphrase = use_session('config_'.$username.'_passphrase');
		$stock_buy = use_session('config_'.$username.'_stock_buy');
		$stock_account = use_session('config_'.$username.'_stock_account');
		$self_adjust = use_session('config_'.$username.'_self_adjust');
		$price_switch = use_session('config_'.$username.'_price_switch');
		$block_selling = use_session('config_'.$username.'_block_selling');
		$precise_selling = use_session('config_'.$username.'_precise_selling');
		$sell_coin = use_session('config_'.$username.'_sell_coin');
		$self_adjust_minutes = use_session('config_'.$username.'_self_adjust_minutes');
		$self_adjust_minutes_interval = use_session('config_'.$username.'_self_adjust_minutes_interval');
		$minute_amount_interval = use_session('config_'.$username.'_minute_amount_interval');
		$wait_time_sell_delay_low = use_session('config_'.$username.'_wait_time_sell_delay_low');
		$wait_time_sell_delay_low_base = use_session('config_'.$username.'_wait_time_sell_delay_low_base');
		$calculate_wait_delay_low = use_session('config_'.$username.'_calculate_wait_delay_low');
		$minute_amount = use_session('config_'.$username.'_minute_amount');
		$price_reached = use_session('config_'.$username.'_price_reached');
		$news_override = use_session('config_'.$username.'_news_override');
		$rsi_top = use_session('config_'.$username.'_rsi_top');
		$rsi_bottom = use_session('config_'.$username.'_rsi_bottom');
		
		$max_precision = use_session('config_'.$username.'_sell_quantity');
		$max_precision = explode('.', $max_precision);
		$max_precision = isset($max_precision[1]) ? strlen($max_precision[1]) : 0;
		
		$array_of_intervals = array();
		if($self_adjust_minutes_interval != 0){
			$ms = floor(60 / $self_adjust_minutes_interval);
			$counting = $self_adjust_minutes_interval;
			if($ms > 0){
				for($i = 0; $i < $ms; $i++){
					if($counting == 60){
						$array_of_intervals[]= 0;
					} else {
						$array_of_intervals[]= $counting;
						$counting += $self_adjust_minutes_interval;
					}
				}
			}
		}
		
		$array_of_intervals_sta = array();
		$ms = floor(60 / 5);
		$counting = 5;
		for($i = 0; $i < $ms; $i++){
			if($counting == 60){
				$array_of_intervals_sta[]= 0;
			} else {
				$array_of_intervals_sta[]= $counting;
				$counting += 5;
			}
		}
		
		$array_of_intervals_sta_ex = array();
		$ms = floor(60 / 2);
		$counting = 2;
		for($i = 0; $i < $ms; $i++){
			if($counting == 60){
				$array_of_intervals_sta_ex[]= 0;
			} else {
				$array_of_intervals_sta_ex[]= $counting;
				$counting += 2;
			}
		}
		
		$date = date('s');
		$date = (int)($date);
		$date_m = date('i');
		$date_m = (int)($date_m);
		$date_time = $date_object->format('Y-m-d H:i:s');
		$date_time_operate = $date_object->format('Hi');
		$date_self_adjust = $date_object->format('YmdHis');
		$date_string = $date_object->format('Y-m-d');
		$date_order = $date_object->format('YmdHis');
		
		$coin_array = use_session($username.'_coin_array');
		
		/* Additional checkup for past reading */
		$price = 0;
		$price_array_list = [];
		
		if(strpos($username, 'coinbase_') !== false){
			if(in_array($date, $array_of_intervals_sta)){
				if(!empty($coin_array)){
					foreach($coin_array as $coin_symbol => $coin_data){
						$ch = curl_init('https://api.exchange.coinbase.com/products/'.$coin_symbol.'-'.$stock_account.'/ticker');
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
						curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'ACCEPT: application/json'));
						$response = curl_exec($ch);
						curl_close($ch);
						$response = json_decode($response, true);
						if(!empty($response) && isset($coin_array[$coin_symbol.$stock_account])){
							$price_array_list[$coin_symbol.'-'.$stock_account] = $response['price'];
						}
					}
				}
			}
			
			$ch = curl_init('https://api.exchange.coinbase.com/products/'.$stock_buy.'/ticker');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'ACCEPT: application/json'));
			$start = microtime(true);
			$response = curl_exec($ch);
			$time_elapsed_secs = microtime(true) - $start;
			curl_close($ch);
			$response = json_decode($response, true);
			if(!empty($response)){
				$price = $response['price'];
			}
		} elseif(strpos($username, 'binance_') !== false){
			$ch = curl_init('https://api.binance.com/api/v3/ticker/price');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$start = microtime(true);
			$json_query = curl_exec($ch);
			$time_elapsed_secs = microtime(true) - $start;
			$ticker_data = json_decode($json_query, true);
			if(isset($ticker_data[0]['symbol']) && isset($ticker_data[0]['price'])){
				foreach($ticker_data as $ticker_index => $ticker){
					if(isset($ticker['symbol']) && isset($ticker['price'])){
						if($ticker['symbol'] == $stock_buy){
							if(empty($buy_price)){
								$price = $ticker['price'];
							}
						}
						if(!empty($coin_array) && isset($coin_array[$ticker['symbol']])){
							$price_array_list[$ticker['symbol']] = $ticker['price'];
						}
					}
				}
			}
		} elseif(strpos($username, 'kraken_') !== false){
			if(in_array($date, $array_of_intervals_sta)){
				if(!empty($coin_array)){
					foreach($coin_array as $coin_symbol => $coin_data){
						$ch = curl_init('https://api.kraken.com/0/public/Ticker?pair='.$coin_symbol.$stock_account);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
						$response = curl_exec($ch);
						curl_close($ch);
						$response = json_decode($response, true);
						if(!empty($response) && isset($response['result'][$coin_symbol.$stock_account]['a'][0]) && isset($coin_array[$coin_symbol.$stock_account])){
							$price_array_list[$coin_symbol.$stock_account] = $response['result'][$coin_symbol.$stock_account]['a'][0];
						}				
					}			
				}
			}
			
			$ch = curl_init('https://api.kraken.com/0/public/Ticker?pair='.$stock_buy);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
			$start = microtime(true);
			$response = curl_exec($ch);
			$time_elapsed_secs = microtime(true) - $start;
			curl_close($ch);
			$response = json_decode($response, true);
			if(!empty($response) && isset($response['result'][$stock_buy]['a'][0])){
				$price = $response['result'][$stock_buy]['a'][0];
			}		
		}
		write_session($username.'_price_array_list', $price_array_list);
		write_session($username.'_server_time', $time_elapsed_secs);
		write_session($username.'_current_price_value', $price);
		
		if(strval($time_elapsed_secs) > '0.5'){
			$time_elapsed_secs = 0.5;
		}
		
		if(!empty($price) && !empty($time_elapsed_secs)){
			$array = use_session($username.'_server_time_average');
			$array = !empty($array) ? $array : array(1);
			if(in_array($date, $array_of_intervals_sta)){
				$time_return = $time_elapsed_secs * 10;
				if(count($array) >= 30){
					array_shift($array);
					$array[] = number_format(($time_return - (int)($time_return)), 3, '.', '');
				} else {
					$array[] = number_format(($time_return - (int)($time_return)), 3, '.', '');
				}
				
				write_session($username.'_server_time_average', $array);
			}
			
			$server_time_average = 0;
			foreach($array as $key => $value){
				$server_time_average = $server_time_average + $value;
			}
			$server_time_average = $server_time_average / count($array);
			
			$steps = array(
				'extra_long' => array(
					'var' => '_extra_long',
					'time' => 6,
					'rsi' => 2,
					'interval' => $array_of_intervals_sta_ex,
					'date' => $date_m
				),
				'long' => array(
					'var' => '_long',
					'time' => 120,
					'rsi' => 10,
					'interval' => $array_of_intervals_sta,
					'date' => $date
				),
				'short' => array(
					'var' => '',
					'time' => 30,
					'rsi' => 15,
					'interval' => $array_of_intervals_sta,
					'date' => $date
				)
			);
			foreach($steps as $step_name => $value){
				${'step_one'.$steps[$step_name]['var']} = use_session($username.'_rsi'.$steps[$step_name]['var']);
				${'step_one'.$steps[$step_name]['var']} = (${'step_one'.$steps[$step_name]['var']} == '') ? 50 : ${'step_one'.$steps[$step_name]['var']};
				
				$extra_long = 0;
				if($step_name == 'extra_long'){
					if($date != 0){
						$extra_long = 1;
					}
				}
				if(in_array($steps[$step_name]['date'], $steps[$step_name]['interval']) && $extra_long == 0){
					$array = use_session($username.'_stagnate_price'.$steps[$step_name]['var']);
					$array = !empty($array) ? $array : array(1);
					if(count($array) >= $steps[$step_name]['time']){
						array_shift($array);
						$array[] = $price;
					} else {
						$array[] = $price;
					}
					write_session($username.'_stagnate_price'.$steps[$step_name]['var'], $array);
					
					$count_average_negative = 0;
					$count_average_positive = 0;
					$count_periods = 0;
					$price_count = 0;
					$old_value = 0;
					foreach($array as $key => $value){
						if(isset($array[$key - 1])){
							$old_value = $array[$key - 1];
							$value_range = $value - $old_value;	
							$price_count = $price_count + (float)($value);
							
							if(strval($value_range) < '0'){
								$count_average_negative = $count_average_negative + (float)($value_range);
							} else {
								$count_average_positive = $count_average_positive + (float)($value_range);
							}
							$count_periods++;			
						}
					}
					
					if(strval($price_count) > '0' && $count_periods > 0){
						$average_gain = (($count_average_positive / $price_count) * 100);
						$average_lost = (($count_average_negative / $price_count) * 100);
						$average_lost = ($average_lost / $count_periods);
						$average_gain = ($average_gain / $count_periods);
						if(strval($average_lost) !== '0'){
							${'step_one'.$steps[$step_name]['var']} = 100 - (100 / (abs($average_gain / $average_lost) + 1));
							
							if(is_numeric(${'step_one'.$steps[$step_name]['var']})){
								write_session($username.'_rsi'.$steps[$step_name]['var'], ${'step_one'.$steps[$step_name]['var']});
							}
						}
						
						$rsi_history = use_session($username.'_rsi_self_adjust_array'.$steps[$step_name]['var']);
						$rsi_history = !empty($rsi_history) ? $rsi_history : array(1);
						if(count($rsi_history) >= $steps[$step_name]['time']){
							array_shift($rsi_history);
							$rsi_history[] = (int)(${'step_one'.$steps[$step_name]['var']});
						} else {
							$rsi_history[] = (int)(${'step_one'.$steps[$step_name]['var']});
						}
						write_session($username.'_rsi_self_adjust_array'.$steps[$step_name]['var'], $rsi_history);
						
						$total = 0;
						foreach($rsi_history as $key => $value){
							$total = $total + $value;
						}
						$total = (strval($total) > '0') ? ($total / count($rsi_history)) : 50;
						if(use_session('config_'.$username.'_rsi'.$steps[$step_name]['var'].'_self_adjust') == 1){
							if(strval($total + $steps[$step_name]['rsi']) < '90'){
								write_session('config_'.$username.'_rsi_top'.$steps[$step_name]['var'], (int)($total + $steps[$step_name]['rsi']));
							} else {
								write_session('config_'.$username.'_rsi_top'.$steps[$step_name]['var'], 90);
							}
							if(strval($total - $steps[$step_name]['rsi']) > '10'){
								write_session('config_'.$username.'_rsi_bottom'.$steps[$step_name]['var'], (int)($total - $steps[$step_name]['rsi']));
							} else {
								write_session('config_'.$username.'_rsi_bottom'.$steps[$step_name]['var'], 10);
							}
						}
					}
				}
			}
			
			if($calculate_wait_delay_low == 1){
				write_session('config_'.$username.'_wait_time_sell_delay_low', (int)($wait_time_sell_delay_low_base * ($step_one_extra_long / 100)));
			}
			
			$detect_count = 0;
			if(strval($step_one) < strval($rsi_top) && strval($step_one) > strval($rsi_bottom)){
				$detect_count = 1;
			}
			$price_ai = use_session($username.'_price_ai');
			$current_range_ai = 0;
			if(in_array($date, $array_of_intervals) && strval($price) != '0'){
				if($price_ai == ''){
					write_session($username.'_price_ai', $price);
				} else {
					write_session($username.'_price_ai', $price);
					
					if(strval($price) > '0' && strval($price_ai) > '0'){
						$current_range_ai = $price - $price_ai;
					}
				}
			}
			
			/* each second */
			$date_array = array(
				'short' => array(
					'var' => '',
					'multiplier' => 2
				),
				'long' => array(
					'var' => '_long',
					'multiplier' => 4
				),
				'extra_long' => array(
					'var' => '_extra_long',
					'multiplier' => 6
				)
			);
			
			foreach($date_array as $key => $value){
				${'date_ai'.$date_array[$key]['var']} = use_session($username.'_date_ai'.$date_array[$key]['var']);
				${'date_ai'.$date_array[$key]['var']} = !empty(${'date_ai'.$date_array[$key]['var']}) ? ${'date_ai'.$date_array[$key]['var']} : array(1);
				
				if(in_array($date, $array_of_intervals) && strval($current_range_ai) != '0'){
					if(count(${'date_ai'.$date_array[$key]['var']}) >= floor(($self_adjust_minutes * $date_array[$key]['multiplier']) / $self_adjust_minutes_interval)){
						array_shift(${'date_ai'.$date_array[$key]['var']});
					}
					
					${'date_ai'.$date_array[$key]['var']}[] = number_format($current_range_ai, 3, '.', '');
					write_session($username.'_date_ai'.$date_array[$key]['var'], ${'date_ai'.$date_array[$key]['var']});
				}
			}
			/*************/		
			
			if(use_session($username.'_count_second_range') == ''){
				write_session($username.'_count_second_range', $self_adjust_minutes_interval);
			}
			
			if(use_session($username.'_count_second_range') != $self_adjust_minutes_interval){
				write_session($username.'_count_second_range', $self_adjust_minutes_interval);
				write_session($username.'_increment_range', array(1));
				write_session($username.'_increment_range_long', array(1));
				write_session($username.'_increment_range_extra_long', array(1));
				write_session($username.'_date_ai', array(1));
				write_session($username.'_date_ai_long', array(1));
				write_session($username.'_date_ai_extra_long', array(1));
				write_session($username.'_stagnate_price', array(1));
			}
			
			if(use_session($username.'_count_minute_range') == ''){
				write_session($username.'_count_minute_range', $minute_amount_interval);
			}
			
			if(use_session($username.'_count_minute_range') != $minute_amount_interval){
				write_session($username.'_count_minute_range', $minute_amount_interval);
				write_session($username.'_array_count', array(1));
			}
			
			/******* range ********/
			
			$date_array = array(
				'short' => array(
					'var' => '',
					'multiplier' => 1
				),
				'long' => array(
					'var' => '_long',
					'multiplier' => 2
				),
				'extra_long' => array(
					'var' => '_extra_long',
					'multiplier' => 4
				)
			);
			if(in_array($date, $array_of_intervals) && strval($current_range_ai) != '0'){
				foreach($date_array as $key => $value){
					${'array_increment'.$date_array[$key]['var']} = use_session($username.'_increment_range'.$date_array[$key]['var']);
					${'array_increment'.$date_array[$key]['var']} = !empty(${'array_increment'.$date_array[$key]['var']}) ? ${'array_increment'.$date_array[$key]['var']} : array(1);
					
					if(count(${'array_increment'.$date_array[$key]['var']}) >= floor(($self_adjust_minutes * $date_array[$key]['multiplier']) / $self_adjust_minutes_interval)){
						array_shift(${'array_increment'.$date_array[$key]['var']});	
					}
					
					${'array_increment'.$date_array[$key]['var']}[] = number_format($current_range_ai, 3, '.', '');
					write_session($username.'_increment_range'.$date_array[$key]['var'], ${'array_increment'.$date_array[$key]['var']});
				}
			}
			/************/
			
			$action = '';
			$sub_method = '';
			
			if(in_array($date, $array_of_intervals)){
				$range = 0;
				$range_count = 0;
				$range_long = 0;
				$range_long_count = 0;
				$range_extra_long = 0;
				$range_extra_long_count = 0;
				$decreasing_range = 0;
				$increasing_range = 0;
				
				for($i = 0; $i < count($date_ai) + count($date_ai_long) + count($date_ai_extra_long); $i++){
					if($i < count($date_ai)){
						if(strval($date_ai[$i]) < '0'){
							$decreasing_range += $date_ai[$i];
						} else {
							$increasing_range += $date_ai[$i];
						}
						$range += $date_ai[$i];
						$range_count++;
					}
					if($i < count($date_ai_long)){
						$range_long += $date_ai_long[$i];
						$range_long_count++;
					}
					if($i < count($date_ai_extra_long)){
						$range_extra_long += $date_ai_extra_long[$i];
						$range_extra_long_count++;
					}
				}
			}
			
			if($block_selling == 1 && $precise_selling == 1 && use_session($username.'_price_sell_all') == 0){
				ExperimentalSellService::execute($path, $username, $date_object, $detect_count, $price, $server_time_average, $step_one);
			}
			
			/*********************/
			if($self_adjust == 1){
				if(use_session($username.'_date_time') == ''){
					write_session($username.'_date_time', $date_self_adjust);
				}
				$total_money = use_session($username.'_total_money');
				$total_money_old = use_session($username.'_total_money_old');
				$date_time = use_session($username.'_date_time');
				$buy = use_session($username.'_buy');
				$sell = use_session($username.'_sell');
				$buy_sell_count = use_session($username.'_buy_sell_count');
				
				if($date_time != '' && (($date_self_adjust - $date_time) > $self_adjust_minutes) && 
				$buy_sell_count >= $minute_amount){		
					if(strval($total_money) > '0' && strval($total_money_old) > '0'){
						$ratio_money_buy = ($buy != 0) ? ($buy / $buy_sell_count) : 0.1;
						$ratio_money_sell = ($sell != 0) ? ($sell / $buy_sell_count) : 0.1;
						if(strval($total_money) != strval($total_money_old)){
							$ratio_money_account_buy = $total_money_old / $total_money;
							$ratio_money_account_sell = $total_money / $total_money_old;
							if(strval($ratio_money_buy) < '1' && strval($ratio_money_sell) < '1'){
								if(strval($ratio_money_buy) > strval($ratio_money_sell)){
									write_session('config_'.$username.'_past_ratio_multiplier_limit', number_format($ratio_money_buy, 3, '.', ''));
								} else {
									write_session('config_'.$username.'_past_ratio_multiplier_limit', number_format($ratio_money_sell, 3, '.', ''));
								}
							}
							
							$ratio_money_account_buy = (strval($ratio_money_account_buy) >= '1') ? 0.1 : $ratio_money_account_buy;
							$ratio_money_account_sell = (strval($ratio_money_account_sell) >= '1') ? 0.1 : $ratio_money_account_sell;
							$ratio_money_account_buy = floor((abs($ratio_money_account_buy) * 100) * 0.3);
							$ratio_money_account_sell = floor((abs($ratio_money_account_sell) * 100) * 0.3);
							$ratio_money_buy = $ratio_money_buy * $ratio_money_account_buy;
							$ratio_money_sell = $ratio_money_sell * $ratio_money_account_sell;
							$ratio_money_buy = (strval($ratio_money_buy) > '50') ? 50 : $ratio_money_buy;
							$ratio_money_sell = (strval($ratio_money_sell) > '50') ? 50 : $ratio_money_sell;
							
							if(use_session('config_'.$username.'_self_adjust_balance_buying') == 1){
								write_session('config_'.$username.'_buying_price_multiplier', floor($ratio_money_buy));
							}
							if(use_session('config_'.$username.'_self_adjust_balance_selling') == 1){
								write_session('config_'.$username.'_selling_price_multiplier', floor($ratio_money_sell));
							}
						}
					}
					
					write_session($username.'_date_time', $date_self_adjust);
				}
				
				if(in_array($date, $array_of_intervals)){
					$crypto_news = use_session($username.'_crypto_news');
				
					$news_indicator_range_negative = 0;
					$news_indicator_range_positive = 0;
					
					if($crypto_news != ''){
						$count_news = 0;
						$crypto_news = json_decode($crypto_news, true);
						foreach($crypto_news as $key => $value){
							if(isset($crypto_news[$key])){
								if(strtolower($crypto_news[$key]['sentiment']) == 'negative'){
									$news_indicator_range_negative = $news_indicator_range_negative + 1;
									$count_news++;
								} elseif(strtolower($crypto_news[$key]['sentiment']) == 'positive'){
									$news_indicator_range_positive = $news_indicator_range_positive + 1;
									$count_news++;
								}
							}
						}
					}				
					
					$range_name = array(
						'range' => array($range_count, $range), 
						'range_long' => array($range_long_count, $range_long), 
						'range_extra_long' => array($range_extra_long_count, $range_extra_long)
					);
					foreach($range_name as $key => $value){
						if(strval($range_name[$key][1]) > '0'){
							$range_increasing = ($range_name[$key][0] == 0) ? 1 : $range_name[$key][0];
							$range_positive = $range_name[$key][1] / $range_increasing;
							if($crypto_news != ''){
								if(strval($news_override) == '0'){
									$range_positive = (($range_positive - ((abs($range_positive) * $news_indicator_range_negative) * 0.05)) + ((abs($range_positive) * $news_indicator_range_positive) * 0.05));
								} else {
									$range_positive = (($range_positive - ((abs($range_positive) * $news_indicator_range_negative) * 0.1)) + ((abs($range_positive) * $news_indicator_range_positive) * 0.1));
								}
							}
							$range_positive = (strval($range_positive) <= '0') ? 0.001 : $range_positive;
							$range_positive = number_format($range_positive, 3, '.', '');
							
							write_session('config_'.$username.'_past_'.$key.'_increase', $range_positive);
						}
						if(strval($range_name[$key][1]) < '0'){
							$range_decreasing = ($range_name[$key][0] == 0) ? 1 : $range_name[$key][0];
							$range_negative = $range_name[$key][1] / $range_decreasing;
							if($crypto_news != ''){
								if(strval($news_override) == '0'){
									$range_negative = (($range_negative + ((abs($range_negative) * $news_indicator_range_positive) * 0.05)) - ((abs($range_negative) * $news_indicator_range_negative) * 0.05));
								} else {
									$range_negative = (($range_negative + ((abs($range_negative) * $news_indicator_range_positive) * 0.1)) - ((abs($range_negative) * $news_indicator_range_negative) * 0.1));
								}
							}
							$range_negative = (strval($range_negative) >= '0') ? -0.001 : $range_negative;
							$range_negative = number_format($range_negative, 3, '.', '');
	
							write_session('config_'.$username.'_past_'.$key.'_decrease', $range_negative);
						}
					}
					
					if(strval(abs($increasing_range)) < strval(abs($decreasing_range))){
						$increasing_ratio_update = abs($increasing_range) / abs($decreasing_range);
						if($crypto_news != ''){
							if(strval($news_override) == '0'){
								$increasing_ratio_update = (($increasing_ratio_update - ((abs($increasing_ratio_update) / $news_indicator_range_negative) * 0.05)) + ((abs($increasing_ratio_update) / $news_indicator_range_positive) * 0.05));
							} else {
								$increasing_ratio_update = (($increasing_ratio_update - ((abs($increasing_ratio_update) / $news_indicator_range_negative) * 0.1)) + ((abs($increasing_ratio_update) / $news_indicator_range_positive) * 0.1));
							}
						}
						$increasing_ratio_update = (strval($increasing_ratio_update) <= '0') ? 0.1 : $increasing_ratio_update;
						$increasing_ratio_update = (strval($increasing_ratio_update) >= '1') ? 0.9 : $increasing_ratio_update;
						$increasing_ratio_update = number_format($increasing_ratio_update, 3, '.', '');
					
						write_session('config_'.$username.'_defined_increasing_ratio', $increasing_ratio_update);
					}
					
					if(strval(abs($increasing_range)) > strval(abs($decreasing_range))){
						$decreasing_ratio_update = abs($decreasing_range) / abs($increasing_range);
						if($crypto_news != ''){
							if(strval($news_override) == '0'){
								$decreasing_ratio_update = (($decreasing_ratio_update + ((abs($decreasing_ratio_update) / $news_indicator_range_positive) * 0.05)) - ((abs($decreasing_ratio_update) / $news_indicator_range_negative) * 0.05));
							} else {
								$decreasing_ratio_update = (($decreasing_ratio_update + ((abs($decreasing_ratio_update) / $news_indicator_range_positive) * 0.1)) - ((abs($decreasing_ratio_update) / $news_indicator_range_negative) * 0.1));
							}
						}
						$decreasing_ratio_update = (strval($decreasing_ratio_update) <= '0') ? 0.1 : $decreasing_ratio_update;
						$decreasing_ratio_update = (strval($decreasing_ratio_update) >= '1') ? 0.9 : $decreasing_ratio_update;
						$decreasing_ratio_update = number_format($decreasing_ratio_update, 3, '.', '');
						
						write_session('config_'.$username.'_defined_decreasing_ratio', $decreasing_ratio_update);
					}
				}
			}
		}
	}
}

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../vendor/autoload.php';

ini_set('display_errors', 1);
function sell_buy($connect, $username, $folder_config, $date_object, $options_data, $path){
	$api_key = use_session('config_'.$username.'_api_key');
	if($api_key == ''){
		return false;
	}
	$folder = $path.'crypto/'.use_session('config_'.$username.'_crypto').'/data_'.use_session('config_'.$username.'_estimation_folder').'/';
	$crypto_news_api_key = use_session('config_'.$username.'_crypto_news_api_key');
	$news_count = use_session('config_'.$username.'_news_count');
	$coin_value = use_session('config_'.$username.'_coin_value');
	$secret_key = use_session('config_'.$username.'_secret_key');
	$passphrase = use_session('config_'.$username.'_passphrase');
	$from_email = use_session('config_'.$username.'_from_email');
	$to_email = use_session('config_'.$username.'_to_email');
	$smtp_host = use_session('config_'.$username.'_smtp_host');
	$smtp_username = use_session('config_'.$username.'_smtp_username');
	$smtp_password = use_session('config_'.$username.'_smtp_password');
	$smtp_port = use_session('config_'.$username.'_smtp_port');
	$buy_price = use_session('config_'.$username.'_buy_price');
	$sell_price = use_session('config_'.$username.'_sell_price');
	$stock_buy = use_session('config_'.$username.'_stock_buy');
	$stock_account = use_session('config_'.$username.'_stock_account');
	$sell_coin = use_session('config_'.$username.'_sell_coin');
	$buy_quantity = use_session('config_'.$username.'_buy_quantity');
	$sell_quantity = use_session('config_'.$username.'_sell_quantity');
	$sell_quantity2 = use_session('config_'.$username.'_sell_quantity');
	$sell_at = use_session('config_'.$username.'_sell_at');
	$buy_at = use_session('config_'.$username.'_buy_at');
	$limit_of_days = use_session('config_'.$username.'_limit_of_day');
	$minute_amount = use_session('config_'.$username.'_minute_amount');
	$time_sell = use_session('config_'.$username.'_time_sell');
	$start_hour_hours = use_session('config_'.$username.'_start_hour_hours');
	$start_hour_minutes = use_session('config_'.$username.'_start_hour_minutes');
	$start_hour_int = intval($start_hour_hours.str_pad($start_hour_minutes, 2, "0", STR_PAD_LEFT));
	$end_hour_hours = use_session('config_'.$username.'_end_hour_hours');
	$end_hour_minutes = use_session('config_'.$username.'_end_hour_minutes');
	$end_hour_int = intval($end_hour_hours.str_pad($end_hour_minutes, 2, "0", STR_PAD_LEFT));
	$end_hour_adjust_top_level = 2359;
	$top_level_limit_adjust = use_session('config_'.$username.'_adjust_top_level');
	$top_level_adjust_percentage = use_session('config_'.$username.'_adjust_top_level_per');	
	$past_range_decrease = use_session('config_'.$username.'_past_range_decrease');
	$past_range_increase = use_session('config_'.$username.'_past_range_increase');
	$past_range_long_decrease = use_session('config_'.$username.'_past_range_long_decrease');
	$past_range_long_increase = use_session('config_'.$username.'_past_range_long_increase');
	$past_range_extra_long_decrease = use_session('config_'.$username.'_past_range_extra_long_decrease');
	$past_range_extra_long_increase = use_session('config_'.$username.'_past_range_extra_long_increase');
	$defined_decreasing_ratio = use_session('config_'.$username.'_defined_decreasing_ratio');
	$defined_increasing_ratio = use_session('config_'.$username.'_defined_increasing_ratio');
	$selling_price_multiplier = use_session('config_'.$username.'_selling_price_multiplier');
	$buying_price_multiplier = use_session('config_'.$username.'_buying_price_multiplier');
	$buying_price_multiplier_range = use_session('config_'.$username.'_buying_price_multiplier_range');
	$selling_price_multiplier_range = use_session('config_'.$username.'_selling_price_multiplier_range');
	$buying_long_range_override = use_session('config_'.$username.'_buying_long_range_override');
	$selling_long_range_override = use_session('config_'.$username.'_selling_long_range_override');
	$buying_price_ratio_toggle = use_session('config_'.$username.'_buying_price_ratio_toggle');
	$selling_price_ratio_toggle = use_session('config_'.$username.'_selling_price_ratio_toggle');
	$buying_long_range_override = use_session('config_'.$username.'_buying_long_range_override');
	$selling_long_range_override = use_session('config_'.$username.'_selling_long_range_override');
	$past_ratio_multiplier_limit = use_session('config_'.$username.'_past_ratio_multiplier_limit');
	$price_reached_added = use_session('config_'.$username.'_price_reached') + use_session('config_'.$username.'_price_increase');
	$price_reached = use_session('config_'.$username.'_price_reached');
	$self_adjust = use_session('config_'.$username.'_self_adjust');
	$update_low_limit = use_session('config_'.$username.'_update_low_limit');
	$sell_all_lock_low = use_session('config_'.$username.'_sell_all_lock_low');
	$sell_all_lock_high = use_session('config_'.$username.'_sell_all_lock_high');	
	$detect_lowest = use_session('config_'.$username.'_detect_lowest');
	$gain_limit = use_session('config_'.$username.'_gain_limit');
	$gain_limit_money = use_session('config_'.$username.'_gain_limit_money');
	$sell_all_high_cancel = use_session('config_'.$username.'_sell_all_high_cancel');
	$sell_all_low_cancel = use_session('config_'.$username.'_sell_all_low_cancel');
	$operate_switch = use_session('config_'.$username.'_operate_switch');
	$price_switch = use_session('config_'.$username.'_price_switch');
	$future_ind = use_session('config_'.$username.'_future_ind');
	$self_adjust_minutes = use_session('config_'.$username.'_self_adjust_minutes');
	$minute_amount_interval = use_session('config_'.$username.'_minute_amount_interval');
	$self_adjust_price = use_session('config_'.$username.'_self_adjust_price');
	$block_buying = use_session('config_'.$username.'_block_buying');
	$block_selling = use_session('config_'.$username.'_block_selling');
	$self_adjust_minimum_buy = use_session('config_'.$username.'_self_adjust_minimum_buy');
	$self_adjust_minimum_sell = use_session('config_'.$username.'_self_adjust_minimum_sell');
	$detect_lowest_time = use_session('config_'.$username.'_detect_lowest_time');
	$precise_selling = use_session('config_'.$username.'_precise_selling');
	$selling_fee = use_session('config_'.$username.'_selling_fee');
	$top_level = use_session('config_'.$username.'_top_level');
	$detect_lowest_break = use_session('config_'.$username.'_detect_lowest_break');
	$top_level_percentage = use_session('config_'.$username.'_top_level_percentage');
	$top_level_range = use_session('config_'.$username.'_top_level_range');
	$extra_long_rsi_selling = use_session('config_'.$username.'_extra_long_rsi_selling');
	$extra_long_rsi_selling_limit = use_session('config_'.$username.'_extra_long_rsi_selling_limit');
	$step_one_extra_long = use_session($username.'_rsi_extra_long');
	
	$max_precision = use_session('config_'.$username.'_sell_quantity');
	$max_precision = explode('.', $max_precision);
	$max_precision = isset($max_precision[1]) ? strlen($max_precision[1]) : 0;
	
	$rsi_top_long = use_session('config_'.$username.'_rsi_top_long');
	$rsi_bottom_long = use_session('config_'.$username.'_rsi_bottom_long');
	$sell_all_delay_stagnant = use_session('config_'.$username.'_sell_all_delay_stagnant');
	
	if(use_session('config_'.$username.'_hide') == ''){
		write_session('config_'.$username.'_hide', 'hide');
	}
	
	if(use_session('config_'.$username.'_history') == ''){
		write_session('config_'.$username.'_history', 'hide');
	}
	
	if(use_session($username.'_price_sell_all') == ''){
		write_session($username.'_price_sell_all', 0);
	}
	
	$folder_user = str_replace('.txt', '', $username);
	
	$date00 = $date_object->format('Y-m-d H:i:s');
	$date_time_operate = $date_object->format('Hi');
	$date_time_operate_hours = $date_object->format('H');
	$date_time_operate_minutes = $date_object->format('i');
	$date_time_operate = intval(ltrim($date_time_operate_hours.$date_time_operate_minutes, '0'));
	$date_self_adjust = $date_object->format('YmdHi');
	$date0011 = $date_object->format('Y-m-d');
	$date_order = $date_object->format('YmdHis');
	
	$current_price = 0;
	$current_price_value = use_session($username.'_current_price_value');
	if(empty($sell_price)){
		$sell_price = $current_price_value;
	}
	if(empty($buy_price)){
		$buy_price = $current_price_value;
		$current_price = $current_price_value;
	}
	
	if($self_adjust == 1){
		if($price_reached == ''){
			write_session('config_'.$username.'_price_reached', $current_price);
		}
		if(use_session('config_'.$username.'_price_increase') == ''){
			write_session('config_'.$username.'_price_increase', '90');
		}
	}
	
	$orders_left = array();
	$order_to_cancel = array();
	
	$txid_sell = use_session($username.'_txid_sell');
	$price_array = use_session($username.'_price_array');
	$total_money = use_session($username.'_total_money');
	
	if(strpos($username, 'binance_') !== false){
		$post_values = ['timestamp'   => (time() * 1000)];
		$query = http_build_query($post_values);
		$signature = hash_hmac('sha256', $query, $secret_key);
		$post_values['signature'] = $signature;
		$query = http_build_query($post_values);

		$ch = curl_init('https://api.binance.com/api/v3/openOrders?'.$query);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-MBX-APIKEY: ".$api_key));
		$open_orders = curl_exec($ch);
		curl_close($ch);
		$current_date_open_orders = date('YmdHis');
		$open_orders = json_decode($open_orders, true);
		if(isset($open_orders[0]['time']) && isset($open_orders[0]['orderId']) && isset($open_orders[0]['symbol'])){
			foreach($open_orders as $key => $value){
				if(isset($value['time']) && isset($value['orderId']) && isset($value['symbol'])){
					$open_order_date = date('YmdHis', ($value['time'] / 1000));
					
					$txid_sell = ($txid_sell != '') ? $txid_sell : array();
					$price_array2 = empty($price_array) ? array(array('txid' => 0)) : $price_array;
					
					foreach($price_array2 as $key6 => $value6){
						if((intval($current_date_open_orders) - intval($open_order_date)) >= 60){
							$order_to_cancel[$value['orderId']] = $value['symbol'];
						}						
					}
				}
			}
			
			foreach($order_to_cancel as $id => $symbol){
				$post_values = ['symbol' => $symbol, 'orderId' => $id, 'timestamp'   => (time() * 1000)];
				$query = http_build_query($post_values);
				$signature = hash_hmac('sha256', $query, $secret_key);
				$post_values['signature'] = $signature;
				$query = http_build_query($post_values);

				$ch = curl_init('https://api.binance.com/api/v3/order?'.$query);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-MBX-APIKEY: ".$api_key));
				$delete_orders = curl_exec($ch);
				curl_close($ch);
				$delete_orders = json_decode($delete_orders, true);
				
				if(isset($delete_orders['status']) && $delete_orders['status'] == 'CANCELED'){
					$orders_left[$id] = $id;
				}
			}
			
			if(!empty($orders_left)){
				$txid_sell = ($txid_sell != '') ? $txid_sell : array();
				
				foreach($orders_left as $key7 => $value7){
					foreach($txid_sell as $key9 => $value9){
						if($value9['id'] == $value7){
							unset($txid_sell[$key9]);
							write_session($username.'_txid_sell', $txid_sell);
						}
					}
					foreach($price_array as $key6 => $value6){		
						if($price_array[$key6]['txid'] == $key7){
							unset($price_array[$key6]);
							write_session($username.'_price_array', $price_array);
						}
					}
				}
				
				$path3 = $folder_config."/logs/".$folder_user.'/'.$date0011.'_'.$username;
				if(!file_exists($path3)){
					$myfile3 = fopen($path3, "w") or die("Unable to open file! 123");
				} else {
					$myfile3 = fopen($path3, "a") or die("Unable to open file! 123");
				}
				$money_wallet = number_format($total_money, 3, '.', '');
				fwrite($myfile3, $date00.' '.number_format(floatval(microtime()), 8, '.', '').'*'.$current_price." : 0=".json_encode(array('Open Orders Count' => count($orders_left), 'Delayed Orders Cancelled' => implode('-', array_keys($orders_left))))."|0|0*0*0|0|0/".$money_wallet."|0|0|0|0|0\n");
				fclose($myfile3);
			}
		}
	} elseif(strpos($username, 'coinbase_') !== false){
		$ch = curl_init('https://api.exchange.coinbase.com/orders?limit=100&status=open');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
		$sign_key = base64_encode(hash_hmac('sha256', time().'GET/orders?limit=100&status=open', base64_decode($secret_key), true));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'ACCEPT: application/json',
		'CB-ACCESS-KEY: '.$api_key,
		'CB-ACCESS-SIGN: '.$sign_key,
		'CB-ACCESS-PASSPHRASE: '.$passphrase,
		'CB-ACCESS-TIMESTAMP: '.time()));
		$response = curl_exec($ch);
		curl_close($ch);
		$current_date_open_orders = date('YmdHis');
		$open_orders = json_decode($response, true);
		if(isset($open_orders[0]['created_at']) && isset($open_orders[0]['id'])){
			foreach($open_orders as $key => $value){
				if(isset($value['created_at']) && isset($value['id'])){
					$open_order_date = date('YmdHis', strtotime($value['created_at']));
					
					$txid_sell = ($txid_sell != '') ? $txid_sell : array();
					$price_array2 = empty($price_array) ? array(array('txid' => 0)) : $price_array;
					
					foreach($price_array2 as $key6 => $value6){
						if((intval($current_date_open_orders) - intval($open_order_date)) >= 60){
							$order_to_cancel[$value['id']] = $value['id'];
						}
					}
				}
			}
			
			foreach($order_to_cancel as $index => $id){
				$ch = curl_init('https://api.exchange.coinbase.com/orders/'.$id);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');			
				$sign_key = base64_encode(hash_hmac('sha256', time().'DELETE/orders/'.$id, base64_decode($secret_key), true));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'ACCEPT: application/json',
				'CB-ACCESS-KEY: '.$api_key,
				'CB-ACCESS-SIGN: '.$sign_key,
				'CB-ACCESS-PASSPHRASE: '.$passphrase,
				'CB-ACCESS-TIMESTAMP: '.time()));
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				$response = curl_exec($ch);
				curl_close($ch);
				
				if($response == $index){
					$orders_left[$index] = $index;
				}
			}
			
			if(!empty($orders_left)){
				$txid_sell = ($txid_sell != '') ? $txid_sell : array();
				
				foreach($orders_left as $key7 => $value7){
					foreach($txid_sell as $key9 => $value9){
						if($value9['id'] == $value7){
							unset($txid_sell[$key9]);
							write_session($username.'_txid_sell', $txid_sell);
						}
					}
					foreach($price_array as $key6 => $value6){		
						if($price_array[$key6]['txid'] == $key7){
							unset($price_array[$key6]);
							write_session($username.'_price_array', $price_array);
						}
					}
				}
				
				$path3 = $folder_config."/logs/".$folder_user.'/'.$date0011.'_'.$username;
				if(!file_exists($path3)){
					$myfile3 = fopen($path3, "w") or die("Unable to open file! 123");
				} else {
					$myfile3 = fopen($path3, "a") or die("Unable to open file! 123");
				}
				$money_wallet = number_format($total_money, 3, '.', '');
				fwrite($myfile3, $date00.' '.number_format(floatval(microtime()), 8, '.', '').'*'.$current_price." : 0=".json_encode(array('Open Orders Count' => count($orders_left), 'Delayed Orders Cancelled' => implode('-', array_keys($orders_left))))."|0|0*0*0|0|0/".$money_wallet."|0|0|0|0|0\n");
				fclose($myfile3);
			}
		}
	} elseif(strpos($username, 'kraken_') !== false) {
		$ch = curl_init('https://api.kraken.com/0/private/OpenOrders');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$sign_key = base64_encode(hash_hmac('sha512', utf8_encode('/0/private/OpenOrders').hash('sha256', utf8_encode((time() * 1000).'nonce='.(time() * 1000).'&trades=true'), true), base64_decode($secret_key), true));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
		'API-Key: '.$api_key,
		'API-Sign: '.$sign_key));
		curl_setopt($ch, CURLOPT_POSTFIELDS, "nonce=".(time() * 1000)."&trades=true");
		$response = curl_exec($ch);
		curl_close($ch);
		$current_date_open_orders = date('YmdHis');
		$open_orders = json_decode($response, true);
		if(isset($open_orders['result']['open'])){
			foreach($open_orders['result']['open'] as $key => $value){
				if(isset($value['opentm'])){
					$open_order_date = date('YmdHis', $value['opentm']);
					
					$txid_sell = ($txid_sell != '') ? $txid_sell : array();
					$price_array2 = empty($price_array) ? array(array('txid' => 0)) : $price_array;
					
					foreach($price_array2 as $key6 => $value6){
						if((intval($current_date_open_orders) - intval($open_order_date)) >= 60){
							$order_to_cancel[$key] = $key;
						}
					}
				}
			}
			
			foreach($order_to_cancel as $index => $id){
				$ch = curl_init('https://api.kraken.com/0/private/CancelOrder');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$sign_key = base64_encode(hash_hmac('sha512', utf8_encode('/0/private/CancelOrder').hash('sha256', utf8_encode((time() * 1000).'nonce='.(time() * 1000).'&txid='.$id), true), base64_decode($secret_key), true));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
				'API-Key: '.$api_key,
				'API-Sign: '.$sign_key));
				curl_setopt($ch, CURLOPT_POSTFIELDS, "nonce=".(time() * 1000)."&txid=".$id);
				$response = curl_exec($ch);
				curl_close($ch);
				$response = json_decode($response, true);
				
				if(isset($response['result']) && isset($response['result']['count']) && $response['result']['count'] > 0){
					$orders_left[$index] = $index;
				}
			}
			
			if(!empty($orders_left)){
				$txid_sell = ($txid_sell != '') ? $txid_sell : array();
				
				foreach($orders_left as $key7 => $value7){
					foreach($txid_sell as $key9 => $value9){
						if($value9['id'] == $value7){
							unset($txid_sell[$key9]);
							write_session($username.'_txid_sell', $txid_sell);
						}
					}
					foreach($price_array as $key6 => $value6){		
						if($price_array[$key6]['txid'] == $key7){
							unset($price_array[$key6]);
							write_session($username.'_price_array', $price_array);
						}
					}
				}
				
				$path3 = $folder_config."/logs/".$folder_user.'/'.$date0011.'_'.$username;
				if(!file_exists($path3)){
					$myfile3 = fopen($path3, "w") or die("Unable to open file! 123");
				} else {
					$myfile3 = fopen($path3, "a") or die("Unable to open file! 123");
				}
				$money_wallet = number_format($total_money, 3, '.', '');
				fwrite($myfile3, $date00.' '.number_format(floatval(microtime()), 8, '.', '').'*'.$current_price." : 0=".json_encode(array('Open Orders Count' => count($orders_left), 'Delayed Orders Cancelled' => implode('-', array_keys($orders_left))))."|0|0*0*0|0|0/".$money_wallet."|0|0|0|0|0\n");
				fclose($myfile3);
			}
		}
	}
	
	$method = 0;
	$m = $minute_amount;
	
	/* Additional checkup for past reading */
	if(!empty($buy_price)){
		$current_price = ($current_price != 0) ? number_format($current_price, 3, '.', '') : 0;
		
		$current_range_ai = 0;
		$price_ai_min = use_session($username.'_price_ai_min');
		if(strval($current_price) != '0'){
			if($price_ai_min == ''){
				write_session($username.'_price_ai_min', $current_price);
			} else {
				write_session($username.'_price_ai_min', $current_price);
				
				if(strval($price_ai_min) > '0' && strval($current_price) > '0'){
					$current_range_ai = $current_price - $price_ai_min;
				}
			}
		}
		
		$current_range_ai = ($current_range_ai != 0) ? number_format($current_range_ai, 3, '.', '') : 0;
		$action_count = '';
		$action = '';
		$sub_method = '';
		
		$step_one_long = use_session($username.'_rsi_long');
		$step_one_long = ($step_one_long == '') ? 50 : $step_one_long;
		$detect_count = 0;
		if(strval($step_one_long) < strval($rsi_top_long) && strval($step_one_long) > strval($rsi_bottom_long)){
			$detect_count = 1;
		}
		
		$full_range_count = 0;
		$full_range_long_count = 0;
		$full_range_extra_long_count = 0;
		$full_decreasing_range = 0;
		$full_increasing_range = 0;
		
		$date_ai = use_session($username.'_date_ai');
		$date_ai = !empty($date_ai) ? $date_ai : array(1);
		
		for($i = 0; $i < count($date_ai); $i++){
			if(strval($date_ai[$i]) < '0'){
				$full_decreasing_range = abs($full_decreasing_range) + abs($date_ai[$i]);
			} else {
				$full_increasing_range = abs($full_increasing_range) + abs($date_ai[$i]);
			}
			$full_range_count++;
		}
		
		$date_ai_long = use_session($username.'_date_ai_long');
		$date_ai_long = !empty($date_ai_long) ? $date_ai_long : array(1);
		
		for($i = 0; $i < count($date_ai_long); $i++){
			$full_range_long_count++;
		}
		
		$date_ai_extra_long = use_session($username.'_date_ai_extra_long');
		$date_ai_extra_long = !empty($date_ai_extra_long) ? $date_ai_extra_long : array(1);
		
		for($i = 0; $i < count($date_ai_extra_long); $i++){
			$full_range_extra_long_count++;
		}

		$increment_range = 0;
		$increment_range_long = 0;
		$increment_range_extra_long = 0;
		$increasing_range = 0;
		$decreasing_range = 0;
		
		$array_increment_range_extra_long = use_session($username.'_increment_range_extra_long');
		$array_increment_range_extra_long = !empty($array_increment_range_extra_long) ? $array_increment_range_extra_long : array(1);
		
		foreach($array_increment_range_extra_long as $key => $value){
			$increment_range_extra_long = $increment_range_extra_long + $value;
		}
		$increment_range_extra_long = ($full_range_extra_long_count > 0) ? ($increment_range_extra_long / $full_range_extra_long_count) : 0;
		$increment_range_extra_long = (strval($increment_range_extra_long) != '0') ? number_format($increment_range_extra_long, 3, '.', '') : 0;

		$array_increment_range_long = use_session($username.'_increment_range_long');
		$array_increment_range_long = !empty($array_increment_range_long) ? $array_increment_range_long : array(1);
		
		foreach($array_increment_range_long as $key => $value){
			$increment_range_long = $increment_range_long + $value;
		}
		$increment_range_long = ($full_range_long_count > 0) ? ($increment_range_long / $full_range_long_count) : 0;
		$increment_range_long = (strval($increment_range_long) != '0') ? number_format($increment_range_long, 3, '.', '') : 0;

		$array_increment_range = use_session($username.'_increment_range');
		$array_increment_range = !empty($array_increment_range) ? $array_increment_range : array(1);
		
		foreach($array_increment_range as $key => $value){
			if(strval($value) > '0'){
				$increasing_range = abs($increasing_range) + abs($value);
			} else {
				$decreasing_range = abs($decreasing_range) + abs($value);
			}
			$increment_range = $increment_range + $value;
		}
		$increment_range = ($full_range_count > 0) ? ($increment_range / $full_range_count) : 0;
		$increment_range = (strval($increment_range) != '0') ? number_format($increment_range, 3, '.', '') :  0;

		$decreasing_ratio = 0;
		$increasing_ratio = 0;
		$ratio_for_multiplier = 0;
		
		if(strval($decreasing_range) != '0' && strval($increasing_range) != '0'){
			$decreasing_ratio = (strval($full_increasing_range) != '0') ? abs($decreasing_range / $full_increasing_range) : 0;
			$increasing_ratio = (strval($full_decreasing_range) != '0') ? abs($increasing_range / $full_decreasing_range) : 0;
			$decreasing_ratio = (strval($decreasing_ratio) != '0') ? number_format($decreasing_ratio, 3, '.', '') : 0;
			$increasing_ratio = (strval($increasing_ratio) != '0') ? number_format($increasing_ratio, 3, '.', '') : 0;
			
			if(strval(abs($decreasing_range)) < strval(abs($full_increasing_range)) && strval($decreasing_ratio) > '0.5'){
				if(strval($decreasing_ratio) != '0' && strval($decreasing_ratio) > strval($defined_decreasing_ratio) && 
				strval($decreasing_ratio) > '0' && strval($decreasing_ratio) < '1' && strval($decreasing_ratio) != strval($defined_decreasing_ratio) && 
				$action == '' && $selling_long_range_override == 0 && strval($current_range_ai) < '0'){
					$action = 'buy';
					$method = 'past ratio';
				}
				$ratio_for_multiplier = $decreasing_ratio;
			} elseif(strval(abs($increasing_range)) < strval(abs($full_decreasing_range)) && strval($increasing_ratio) > '0.5'){
				if(strval($increasing_ratio) != '0' && strval($increasing_ratio) > strval($defined_increasing_ratio) && 
				strval($increasing_ratio) > '0' && strval($increasing_ratio) < '1' && strval($increasing_ratio) != strval($defined_increasing_ratio) && 
				$action == '' && $buying_long_range_override == 0 && strval($current_range_ai) > '0') {
					$action = 'sell';
					$method = 'past ratio';
				}
				$ratio_for_multiplier = $increasing_ratio;
			}
		}

		if(strval($increment_range_extra_long) != '0' && strval($increment_range_extra_long) < strval($past_range_extra_long_decrease) &&
		strval($increment_range_extra_long) != strval($past_range_extra_long_decrease) && strval($increment_range_extra_long) < '0') {
			if($action == '' && strval($current_range_ai) < '0'){
				$action = 'buy';
				$method = 'past median';
				$sub_method = 'extra_long';
			}
		} elseif(strval($increment_range_extra_long) != '0' && strval($increment_range_extra_long) > strval($past_range_extra_long_increase) &&
		strval($increment_range_extra_long) != strval($past_range_extra_long_increase) && strval($increment_range_extra_long) > '0'){
			if($action == '' && strval($current_range_ai) > '0'){
				$action = 'sell';
				$method = 'past median';
				$sub_method = 'extra_long';
			}
		}

		if(strval($increment_range_long) != '0' && strval($increment_range_long) < strval($past_range_long_decrease) &&
		strval($increment_range_long) != strval($past_range_long_decrease) && strval($increment_range_long) < '0') {
			if($action == '' && strval($current_range_ai) < '0'){
				$action = 'buy';
				$method = 'past median';
				$sub_method = 'long';
			}
		} elseif(strval($increment_range_long) != '0' && strval($increment_range_long) > strval($past_range_long_increase) &&
		strval($increment_range_long) != strval($past_range_long_increase) && strval($increment_range_long) > '0'){
			if($action == '' && strval($current_range_ai) > '0'){
				$action = 'sell';
				$method = 'past median';
				$sub_method = 'long';
			}
		}

		if(strval($increment_range) != '0' && strval($increment_range) < strval($past_range_decrease) &&
		strval($increment_range) != strval($past_range_decrease) && strval($increment_range) < '0') {
			if($action == '' && $selling_long_range_override == 0 && strval($current_range_ai) < '0'){
				$action = 'buy';
				$sub_method = 'short';
				$method = 'past median';
			}
		} elseif(strval($increment_range) != '0' && strval($increment_range) > strval($past_range_increase) &&
		strval($increment_range) != strval($past_range_increase) && strval($increment_range) > '0'){
			if($action == '' && $buying_long_range_override == 0 && strval($current_range_ai) > '0'){
				$action = 'sell';
				$sub_method = 'short';
				$method = 'past median';
			}
		}
		
		/*****/
		$check = 0;
		$check1 = 0;
		if($future_ind == 1){
			$data_minutes = array();
			for($x = 0; $x < $m; $x++){
				$date11 = $date_object->add(new DateInterval('PT1M'));
				$date11 = $date11->format('Hi');
				$data_minutes[$x]['time'] = $date11.'00';
			}

			$files = dirToArray($folder);
			asort($files);
			if(count($files) > 0 && !empty($data_minutes)){
				foreach($files as $key2 => $value2){
					$file = file_get_contents($folder.$value2);
					$line_by_line = explode("\n", $file);
					asort($line_by_line);
					if(count($line_by_line) > 0){
						foreach($line_by_line as $value){
							$line = explode(':', $value);
							foreach($data_minutes as $key_m => $value_minute){
								if(isset($line[0]) && isset($line[2]) && $line[0] == $value_minute['time']){
									if(isset($data_minutes[$key_m]['total'])){
										$data_minutes[$key_m]['total'] += floatval($line[2]);
									} else {
										$data_minutes[$key_m]['total'] = floatval($line[2]);
									}
									if(isset($data_minutes[$key_m]['count'])){
										$data_minutes[$key_m]['count']++;
									} else {
										$data_minutes[$key_m]['count'] = 1;
									}
								}
							}
						}
					}
				}
			}
			
			$array_count = 0;
			if(!empty($data_minutes)){
				foreach($data_minutes as $key => $value){
					if(isset($value['total']) && isset($value['count'])){
						$array_count_new_data = $value['total'] / $value['count'];
						$array_count = $array_count + floatval($array_count_new_data);
						$array_count = number_format($array_count, 3, '.', '');
						if($limit_of_days > $value['count']){
							$check1 = 1;
						}
					}
				}
			}	
		
			if(strval($array_count) != '0' && strval($array_count) < strval($sell_at) && strval($array_count) != strval($sell_at) && $action == '' && 
			$selling_long_range_override == 0 && strval($array_count) < '0' && $check1 == 0 && strval($current_range_ai) < '0'){
				$method = 'future median';
				$action = 'buy';
			} elseif(strval($array_count) != '0' && strval($array_count) > strval($buy_at) && strval($array_count) != strval($buy_at) && $action == '' && 
			$buying_long_range_override == 0 && strval($array_count) > '0' && $check1 == 0 && strval($current_range_ai) > '0'){
				$method = 'future median';
				$action = 'sell';
			}
			
			$date_h = date('i');
			$array_of_intervals_h = array();
			if($minute_amount_interval != 0){
				$mh = floor(60 / $minute_amount_interval);
				$counting_h = $minute_amount_interval;
				if($mh > 0){
					for($i = 0; $i < $mh; $i++){
						if($counting_h == 60){
							$array_of_intervals_h[] = 0;
						} else {
							$array_of_intervals_h[]= $counting_h;
							$counting_h += $minute_amount_interval;
						}
					}
				}
			}
			
			$array_count_table = use_session($username.'_array_count');
			$array_count_table = !empty($array_count_table) ? $array_count_table : array(1);
			
			if(in_array($date_h, $array_of_intervals_h)){
				if(count($array_count_table) >= floor($m / $minute_amount_interval)){
					array_shift($array_count_table);
					$array_count_table[] = $array_count;
				} else {
					$array_count_table[] = $array_count;
				}
			}
			write_session($username.'_array_count', $array_count_table);
			
			$array_count_storing = 0;
			$array_count_storing_count = 0;
			
			for($i = 0; $i < count($array_count_table); $i++){
				$array_count_storing += $array_count_table[$i];
				$array_count_storing_count = $array_count_storing_count + 1;
			}
		}
		
		/**********/
		$min_notional = 10;
		$min_quantity = 0;
		$quotePrecision = 8;
		
		if(strpos($username, 'binance_') !== false){
			$ch = curl_init('https://api.binance.com/api/v3/exchangeInfo');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$json_query = curl_exec($ch);
			$exchange = json_decode($json_query, true);
			if(isset($exchange['symbols'])){
				foreach($exchange['symbols'] as $key => $value){
					if(isset($value['symbol']) && isset($value['filters'])){
						if($value['symbol'] == $stock_buy){
							foreach($value['filters'] as $key2 => $value2){
								if(isset($value2['filterType']) && isset($value2['minNotional'])){
									if($value2['filterType'] == 'MIN_NOTIONAL'){
										$min_notional = floatval($value2['minNotional']);
										$min_quantity = number_format(floatval($min_notional / $buy_price), $max_precision, '.', '');
									}
								}
								if(isset($value2['filterType']) && isset($value2['tickSize'])){
									if($value2['filterType'] == 'PRICE_FILTER'){
										$decimal = rtrim($value2['tickSize'], '0');
										$decimal = explode('.', strval($decimal));
										$decimal = (isset($decimal[1])) ? strlen($decimal[1]) : 0;
										$quotePrecision = $decimal;
									}
								}
							}
						}
					}
				}
			}
		} elseif(strpos($username, 'coinbase_') !== false){
			$ch = curl_init('https://api.exchange.coinbase.com/products');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'ACCEPT: application/json'));
			$response = curl_exec($ch);
			curl_close($ch);
			
			$exchange = json_decode($response, true);
			foreach($exchange as $key => $value){
				if(!empty($value['base_min_size'])){
					if($value['id'] == $stock_buy){
						$decimal = explode('.', strval($value['quote_increment']));
						$decimal = (isset($decimal[1])) ? strlen($decimal[1]) : 0;
						$quotePrecision = $decimal;
						$min_quantity = number_format(floatval($value['base_min_size']), $max_precision, '.', '');
						$min_notional = $value['min_market_funds'];
					}
				}
			}
		} elseif(strpos($username, 'kraken_') !== false){
			$ch = curl_init('https://api.kraken.com/0/public/AssetPairs?pair='.$stock_buy);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
			$response = curl_exec($ch);
			curl_close($ch);
			$exchange = json_decode($response, true);
			
			foreach($exchange['result'] as $key => $value){
				if(!empty($value['ordermin'])){
					if($key == $stock_buy){
						$quotePrecision = $value['pair_decimals'];
						$min_quantity = number_format(floatval($value['ordermin']), $max_precision, '.', '');
						$min_notional = round($value['ordermin'] * $buy_price);
					}
				}
			}
		}
		
		write_session('config_'.$username.'_quote_precision', $quotePrecision);
		write_session('config_'.$username.'_min_quantity', $min_quantity);
		write_session('config_'.$username.'_min_notional', $min_notional);
		
		if(strval($min_quantity) > strval($buy_quantity) && $min_quantity != 0){
			if($self_adjust_minimum_buy == 1){
				write_session('config_'.$username.'_buy_quantity', number_format($min_quantity + ($min_quantity * 0.05), $max_precision, '.', ''));
			}
			
			if($self_adjust_minimum_sell == 1){
				write_session('config_'.$username.'_sell_quantity', number_format($min_quantity + ($min_quantity * 0.05), $max_precision, '.', ''));
			}
		}
		
		/****************/
		$buy_balance = 1;
		$sell_balance = 1;
		$coin_array = [];
		
		if(strpos($username, 'binance_') !== false){
			$post_values = ['timestamp'   => (time() * 1000)];
			$query = http_build_query($post_values);
			$signature = hash_hmac('sha256', $query, $secret_key);
			$post_values['signature'] = $signature;
			$query = http_build_query($post_values);

			$ch = curl_init('https://api.binance.com/api/v3/account?'.$query);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-MBX-APIKEY: ".$api_key));
			$account_info = curl_exec($ch);
			curl_close($ch);
			$value_account = json_decode($account_info, true);
			
			if(isset($value_account['balances'])){
				if(is_array($value_account['balances']) && !empty($value_account['balances'])){
					foreach($value_account['balances'] as $key7 => $y){
						if(isset($y['asset']) && isset($y['free'])){
							if($y['asset'] == $stock_account){
								$buy_balance = $y['free'];
								$buy_balance_quantity = number_format(floatval($buy_balance / $buy_price), $max_precision, '.', '');
							}
							if($y['asset'] == $sell_coin){
								$sell_balance = $y['free'];
								$sell_balance_quantity = number_format(floatval($sell_balance), $max_precision, '.', '');
							}
							if(strval($y['free']) > '0'){
								$coin_array[$y['asset'].$stock_account]['name'] = $y['asset'].$stock_account;
								$coin_array[$y['asset'].$stock_account]['value'] = $y['free'];
							}
						}
					}
				}
			}
		} elseif(strpos($username, 'coinbase_') !== false){
			$ch = curl_init('https://api.exchange.coinbase.com/accounts');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
			$sign_key = base64_encode(hash_hmac('sha256', time().'GET/accounts', base64_decode($secret_key), true));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'ACCEPT: application/json',
			'CB-ACCESS-KEY: '.$api_key,
			'CB-ACCESS-SIGN: '.$sign_key,
			'CB-ACCESS-PASSPHRASE: '.$passphrase,
			'CB-ACCESS-TIMESTAMP: '.time()));
			$response = curl_exec($ch);
			curl_close($ch);
			$value_account = json_decode($response, true);
			
			if(isset($value_account[0]['balance'])){
				if(is_array($value_account[0]) && !empty($value_account[0])){
					foreach($value_account as $key7 => $y){
						if(isset($value_account[$key7]['currency']) && isset($value_account[$key7]['available'])){
							if($value_account[$key7]['currency'] == $stock_account){
								$buy_balance = $value_account[$key7]['available'];
								$buy_balance_quantity = number_format(floatval($buy_balance / $buy_price), $max_precision, '.', '');
							}
							if($value_account[$key7]['currency'] == $sell_coin){
								$sell_balance = $value_account[$key7]['available'];
								$sell_balance_quantity = number_format(floatval($sell_balance), $max_precision, '.', '');
							}
							
							if(strval($value_account[$key7]['available']) > '0'){
								$coin_array[$value_account[$key7]['currency'].'-'.$stock_account]['name'] = $value_account[$key7]['currency'].'-'.$stock_account;
								$coin_array[$value_account[$key7]['currency'].'-'.$stock_account]['value'] = $value_account[$key7]['available'];
							}
						}
					}
				}
			}
		} elseif(strpos($username, 'kraken_') !== false){
			$ch = curl_init('https://api.kraken.com/0/private/Balance');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$sign_key = base64_encode(hash_hmac('sha512', utf8_encode('/0/private/Balance').hash('sha256', utf8_encode((time() * 1000).'nonce='.(time() * 1000)), true), base64_decode($secret_key), true));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
			'API-Key: '.$api_key,
			'API-Sign: '.$sign_key));
			curl_setopt($ch, CURLOPT_POSTFIELDS, "nonce=".(time() * 1000));
			$response = curl_exec($ch);
			curl_close($ch);
			$value_account = json_decode($response, true);
			
			if(isset($value_account['result'])){
				if(is_array($value_account['result']) && !empty($value_account['result'])){
					foreach($value_account['result'] as $key7 => $y){
						if($key7 == $stock_account){
							$buy_balance = $y;
							$buy_balance_quantity = number_format(floatval($buy_balance / $buy_price), $max_precision, '.', '');
						}
						if($key7 == $sell_coin){
							$sell_balance = $y;
							$sell_balance_quantity = number_format(floatval($sell_balance), $max_precision, '.', '');
						}
						
						if(strval($y) > '0'){
							$coin_array[$key7.$stock_account]['name'] = $key7.$stock_account;
							$coin_array[$key7.$stock_account]['value'] = $y;
						}
					}
				}
			}
		}
		
		write_session($username.'_coin_array', $coin_array);
		write_session($username.'_buy_balance', $buy_balance);
		write_session($username.'_sell_balance', ($sell_balance * $sell_price));
	
		$total_money = $buy_balance + ($sell_balance * $sell_price);
		if(use_session($username.'_total_money') == ''){
			write_session($username.'_total_money', $total_money);
		} else {
			write_session($username.'_total_money_old', use_session($username.'_total_money'));
			write_session($username.'_total_money', $total_money);
		}
			
		$quantity_selling_percentage = 0;
		$quantity_buying_percentage = 0;
		$quantity_selling_ratio = 0;
		$quantity_buying_ratio = 0;
		
		if(strval($buy_balance) > '0' || strval($sell_balance) > '0'){
			if(
				($selling_price_multiplier > 0 && $selling_price_multiplier <= 100 && $selling_price_multiplier_range == 0) || 
				($selling_price_multiplier > 0 && $selling_price_multiplier <= 100 && $selling_price_multiplier_range == 1 && $method == 'past median' && ($sub_method == 'long' || $sub_method == 'extra_long'))
			){
				$quantity_selling_percentage = $sell_balance * ($selling_price_multiplier / 100);
			}
			
			if(
				($buying_price_multiplier > 0 && $buying_price_multiplier <= 100 && $buying_price_multiplier_range == 0) || 
				($buying_price_multiplier > 0 && $buying_price_multiplier <= 100 && $buying_price_multiplier_range == 1 && $method == 'past median' && ($sub_method == 'long' || $sub_method == 'extra_long'))
			){
				$buying_multiplier	= $buy_balance * ($buying_price_multiplier / 100);
				$quantity_buying_percentage = $buying_multiplier / $buy_price;
			}
			
			if(strval($past_ratio_multiplier_limit) >= strval($ratio_for_multiplier) && strval($ratio_for_multiplier) > '0' && strval($ratio_for_multiplier) < '1'){
				if(
					($selling_price_ratio_toggle == 1 && $selling_price_multiplier_range == 0) || 
					($selling_price_ratio_toggle == 1 && $selling_price_multiplier_range == 1 && $method == 'past median' && ($sub_method == 'long' || $sub_method == 'extra_long'))
				){
					$quantity_selling_ratio = $sell_balance * $ratio_for_multiplier;
				}
				if(
					($buying_price_ratio_toggle == 1 && $buying_price_multiplier_range == 0) || 
					($buying_price_ratio_toggle == 1 && $buying_price_multiplier_range == 1 && $method == 'past median' && ($sub_method == 'long' || $sub_method == 'extra_long'))
				){
					$buying_ratio_multiplier = $buy_balance * $ratio_for_multiplier;
					$quantity_buying_ratio = $buying_ratio_multiplier / $buy_price;
				}
			}
		}
		
		$buy_quantity = number_format(($buy_quantity + $quantity_buying_percentage + $quantity_buying_ratio), $max_precision, '.', '');
		$sell_quantity = number_format(($sell_quantity + $quantity_selling_percentage + $quantity_selling_ratio), $max_precision, '.', '');	
		$min_notional_toggle = 0;
		if(strval($buy_quantity * $buy_price) <= strval($min_notional) && ($action == '' || $action == 'buy')){
			$min_notional_toggle = 1;
		} 
		if(strval($sell_quantity * $sell_price) <= strval($min_notional) && ($action == '' || $action == 'sell')){
			$min_notional_toggle = 1;
		}
		/*********************/
		$total81 = 0;
		
		if(use_session($username.'_price_reached_toggle') == ''){
			write_session($username.'_price_reached_toggle', 0);
		}
		
		if(use_session($username.'_price_reached_block_buy_start') == ''){
			write_session($username.'_price_reached_block_buy_start', 0);
		}
		
		if(use_session($username.'_trigger_lowest_price') == ''){
			write_session($username.'_trigger_lowest_price', 0);
		}
		
		if(use_session($username.'_time_sell') == ''){
			write_session($username.'_time_sell', time());
		}
		
		$price_operate = use_session($username.'_price_operate');
		
		if($detect_lowest == 0){
			write_session($username.'_price_reached_block_buy_start', 0);
			write_session($username.'_trigger_lowest_price', 0);
			write_session($username.'_time', time());
		}
		
		if($date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int && use_session($username.'_operate') == 1) {
			write_session($username.'_operate', 0);
		}
		
		if((strval($price_reached) > strval($current_price) || $date_time_operate == $start_hour_int) && 
		use_session($username.'_price_operate') == 1) {
			write_session($username.'_price_operate', 0);
		}
		
		if(use_session($username.'_wait_time_sell') == '') {
			write_session($username.'_wait_time_sell', time());
		}
		
		if(use_session($username.'_time_sell_trigger') == '') {
			write_session($username.'_time_sell_trigger', 0);
		}
		
		if(use_session($username.'_gain_limit_trigger') == '') {
			write_session($username.'_gain_limit_trigger', 0);
		}
		
		if(use_session($username.'_operate_gain') == '' || $gain_limit == 0){
			write_session($username.'_operate_gain', 0);
		}
		
		if(strval($current_price) > strval($price_reached)){
			write_session($username.'_wait_time_sell', time());				
		}
		
		if(strval($price_reached) > strval($current_price) || strval($price_reached_added) < strval($current_price)){
			write_session($username.'_highest_today_sell', $current_price);
		}
		
		if(strval($gain_limit_money) > strval(use_session($username.'_price_today'))){
			write_session($username.'_wait_time_operate_gain', time());				
		}
		
		$price_array8 = use_session($username.'_price_array');
		
		if($gain_limit == 1 && empty($price_array8) && use_session($username.'_wait_time_operate_gain') < (time() - 180) &&
		use_session($username.'_price_operate') == 0 && use_session($username.'_operate_gain') == 0 && 
		use_session($username.'_price_sell_all') == 0 && $date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int && 
		use_session($username.'_price_reached_block_buy_start') == 0) {
			write_session($username.'_wait_time_operate_gain', time());
			write_session($username.'_gain_limit_trigger', 1);
			write_session($username.'_price_sell_all', 1);
			write_session($username.'_price_value', $current_price);
			write_session($username.'_price_array', array());
			write_session($username.'_txid_time', array());
		}
		
		if(strval($price_reached) > strval($current_price) && use_session($username.'_price_sell_all') == 0 && ((strval($step_one_long) < strval($rsi_bottom_long) && $sell_all_delay_stagnant == 1) || $sell_all_delay_stagnant == 0) && use_session($username.'_operate_gain') == 0 && $price_switch == 1 && $sell_all_low_cancel == 0 &&
		$date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int && use_session($username.'_price_reached_block_buy_start') == 0 && 
		use_session($username.'_gain_limit_trigger') == 0) {
			if((intval(use_session($username.'_wait_time_sell') + use_session('config_'.$username.'_wait_time_sell_delay_low')) < intval(time()))){
				write_session($username.'_price_reached_toggle', 0);
				if(use_session($username.'_price_position') == ''){
					write_session($username.'_price_position', 'below');
				}
				write_session($username.'_price_value', $current_price);
				write_session($username.'_price_sell_all', 1);
				
				if($detect_lowest == 1 && $sell_all_lock_low == 1){
					write_session($username.'_price_reached_block_buy_start', 1);
					write_session($username.'_trigger_lowest_price', 0);
					write_session($username.'_time', time());
				}
				write_session($username.'_price_array', array());
				write_session($username.'_txid_time', array());
			}
		}
		
		if(strval($price_reached) < strval($current_price) && use_session($username.'_price_sell_all') == 1 && $price_switch == 1 && use_session($username.'_price_position') == 'below' && $sell_all_low_cancel == 0 && use_session($username.'_operate_gain') == 0 && use_session($username.'_gain_limit_trigger') == 0 &&
		$date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int && $sell_all_lock_low == 0 && use_session($username.'_price_reached_block_buy_start') == 0) {
			write_session($username.'_price_position', '');
			write_session($username.'_price_sell_all', 0);
		}
		
		if($precise_selling == 0){
			write_session($username.'_price_array', array());
			write_session($username.'_txid_time', array());
		}
		
		if((strval($price_reached_added) < strval($current_price) || (strval(use_session($username.'_highest_today_sell')) > strval($price_reached) && 
		intval(use_session($username.'_time_sell') + use_session('config_'.$username.'_wait_time_sell_delay_high')) < intval(time()) && $time_sell == 1)) && use_session($username.'_operate_gain') == 0 &&
		use_session($username.'_price_operate') == 0 && use_session($username.'_price_sell_all') == 0 && $price_switch == 1 && $sell_all_high_cancel == 0 &&
		$date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int && use_session($username.'_price_reached_block_buy_start') == 0 && 
		use_session($username.'_gain_limit_trigger') == 0) {
			write_session($username.'_price_reached_toggle', 0);
			if(use_session($username.'_price_position') == ''){
				write_session($username.'_price_position', 'above');
			}
			write_session($username.'_price_value', $current_price);
			write_session($username.'_price_sell_all', 1);

			if($detect_lowest == 1 && $sell_all_lock_high == 1){
				write_session($username.'_price_reached_block_buy_start', 1);
				write_session($username.'_trigger_lowest_price', 0);
				write_session($username.'_time', time());
			}
			write_session($username.'_price_array', array());
			write_session($username.'_txid_time', array());
		}
		
		if((strval(use_session($username.'_highest_today_sell')) > strval($price_reached) && 
		intval(use_session($username.'_time_sell') + use_session('config_'.$username.'_wait_time_sell_delay_high')) < intval(time()) && $time_sell == 1 && strval(use_session($username.'_price_today')) > strval($current_price * 0.001))){
			write_session($username.'_time_sell_trigger', 1);
		}
		
		if(strval($price_reached_added) > strval($current_price) && use_session($username.'_time_sell_trigger') == 0 && use_session($username.'_operate_gain') == 0 &&
		use_session($username.'_price_operate') == 0 && use_session($username.'_price_sell_all') == 1 && $price_switch == 1 && use_session($username.'_price_position') == 'above' && $sell_all_high_cancel == 0 && use_session($username.'_gain_limit_trigger') == 0 &&
		$date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int && $sell_all_lock_high == 0 && use_session($username.'_price_reached_block_buy_start') == 0) {
			write_session($username.'_price_position', '');
			write_session($username.'_price_sell_all', 0);
		}
		
		if($date_time_operate == $end_hour_int && 
		use_session($username.'_operate') == 0 && use_session($username.'_price_sell_all') == 0 && $operate_switch == 1 && 
		use_session($username.'_gain_limit_trigger') == 0 && use_session($username.'_operate_gain') == 0) {
			write_session($username.'_price_sell_all', 1);
			write_session($username.'_price_value', $current_price);
			write_session($username.'_price_array', array());
			write_session($username.'_txid_time', array());
		}
		
		if($date_time_operate == $start_hour_int && $self_adjust_price == 1) {
			write_session($username.'_price_reached_toggle', 0);
			write_session('config_'.$username.'_price_reached', $current_price);
		}
		
		if($date_time_operate == 5 && $gain_limit == 1) {
			write_session($username.'_operate_gain', 0);
		}
		
		$highest_of_day = use_session($username.'_highest_today');
		$lowest_of_day = use_session($username.'_lowest_today');
		$range_between = $highest_of_day - $lowest_of_day;
		$range_actual = $current_price - $lowest_of_day;
		$allow_payment = 0;
		if($top_level == 0){
			$allow_payment = 1;
		}
		if(strval($range_between) > '0'){
			if($top_level == 1 && strval($range_actual / $range_between) < strval($top_level_percentage) && strval($top_level_range) < strval($range_between)){
				$allow_payment = 1;
			}
		}
		if($allow_payment == 1){
			write_session($username.'_top_level_trigger', 'yes');
		} else {
			write_session($username.'_top_level_trigger', 'no');
		}
		
		if((($action == 'sell' && $min_notional_toggle == 0) || (use_session($username.'_price_sell_all') == 1 && $min_notional_toggle == 0)) && ($block_selling == 0 || ($block_selling == 1 && use_session($username.'_price_sell_all') == 1)) && (use_session($username.'_price_sell_all') == 1 || (use_session($username.'_price_sell_all') == 0 && $detect_count == 0 && strval($step_one_long) > strval($rsi_top_long)))){
			if(use_session($username.'_price_sell_all') == 1 && strval($sell_balance_quantity) < strval($sell_quantity)){
				$sell_quantity = number_format(floatval($sell_balance_quantity - (($sell_balance_quantity * $selling_fee) + ($sell_balance_quantity * 0.05))), $max_precision, '.', '');
			}
			
			if(strval($sell_quantity * $sell_price) > strval($min_notional)){		
				$sell_price_output = number_format($sell_price - ($sell_price * 0.002), intval(use_session('config_'.$username.'_quote_precision')), '.', '');
				
				if(strpos($username, 'binance_') !== false){
					$post_values = [
						'symbol' => $stock_buy,
						'side'   => 'SELL',
						'type'   => 'LIMIT',
						'timeInForce'   => 'GTC',
						'quantity'   => $sell_quantity,
						'price' => $sell_price_output,
						'recvWindow' => '5000',
						'timestamp'   => (time() * 1000)
					];
				} elseif(strpos($username, 'coinbase_') !== false){
					
					$post_values = [
						'type' => 'limit',
						'side' => 'sell',
						'time_in_force' => 'GTC',
						'price' => $sell_price_output,
						'size' => $sell_quantity,
						'product_id' => $stock_buy,
					];
				} elseif(strpos($username, 'kraken_') !== false){
					$post_values = [
						'ordertype' => 'limit',
						'type' => 'sell',
						'timeinforce' => 'GTC',
						'price' => $sell_price_output,
						'volume' => $sell_quantity,
						'pair' => $stock_buy,
					];
				}
				$check = 1;
				if(strval($sell_quantity * $sell_price) > '0'){
					$total81 = $sell_quantity * $sell_price;
				}
			}
		}
		
		$crypto_news = json_decode(use_session($username.'_crypto_news'), true);
		$positive = 0;
		$negative = 0;
		$news_total = 1;
		
		if(!empty($crypto_news)){
			$news_total = 0;
			foreach($crypto_news as $key => $value){
				if(strtolower($crypto_news[$key]['sentiment']) == 'positive') {
					$positive++;
				} elseif(strtolower($crypto_news[$key]['sentiment']) == 'negative') {
					$negative++;
				}
			}
			$news_total = $positive - $negative;
		}
		
		if($action == 'buy' && $allow_payment == 1 && $min_notional_toggle == 0 && use_session($username.'_price_sell_all') == 0 && use_session($username.'_price_reached_block_buy_start') == 0 && $block_buying == 0 && $detect_count == 0 && strval($step_one_long) < strval($rsi_bottom_long) && (($news_total > 0 && $news_count == 0) || $news_count == 1) && (($extra_long_rsi_selling == 1 && (strval($step_one_extra_long) > strval($extra_long_rsi_selling_limit)) && strval($step_one_extra_long) != '' && $precise_selling == 1) || ($extra_long_rsi_selling == 0 && $precise_selling == 1) || $precise_selling == 0)){
			$buy_price_output = number_format($buy_price + ($buy_price * 0.002), intval(use_session('config_'.$username.'_quote_precision')), '.', '');
			
			if(strpos($username, 'binance_') !== false){
				$post_values = [
					'symbol' => $stock_buy,
					'side'   => 'BUY',
					'type'   => 'LIMIT',
					'timeInForce'   => 'GTC',
					'quantity'   => $buy_quantity,
					'price' => $buy_price_output,
					'recvWindow' => '5000',
					'timestamp'   => (time() * 1000)
				];
			} elseif(strpos($username, 'coinbase_') !== false){
				$post_values = [
					'type' => 'limit',
					'side' => 'buy',
					'time_in_force' => 'GTC',
					'price' => $buy_price_output,
					'size' => $buy_quantity,
					'product_id' => $stock_buy,
				];
			} elseif(strpos($username, 'kraken_') !== false){
				$post_values = [
					'ordertype' => 'limit',
					'type' => 'buy',
					'timeinforce' => 'GTC',
					'price' => $buy_price_output,
					'volume' => $buy_quantity,
					'pair' => $stock_buy,
				];
			}
			$check = 1;
			if(strval($buy_quantity * $buy_price) > '0'){
				$total81 = $buy_quantity * $buy_price;
			}
			
			write_session($username.'_highest_today_sell', $current_price);
			write_session($username.'_time_sell', time());
		}
		
		if($action == 'buy' && strval($price_reached) > strval($current_price) && $date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int && $update_low_limit == 1 && $price_switch == 1){
			write_session($username.'_buy_limit', use_session($username.'_buy_limit') + 1);
		}
		
		if(strval($price_reached) < strval($current_price) || $action == '' && $date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int && $update_low_limit == 1 && $price_switch == 1){
			write_session($username.'_buy_limit', 0);
		}
		
		if($action == 'buy' && strval(use_session($username.'_buy_limit')) > '10' && $date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int && $update_low_limit == 1 && $price_switch == 1){
			if(strval($price_reached) > strval($current_price) && use_session($username.'_price_reached_toggle') == 0) {
				write_session('config_'.$username.'_price_reached', $current_price);
				write_session($username.'_price_reached_toggle', 1);
				write_session($username.'_buy_limit', 0);
			}
		}
		
		if($date_time_operate == 0) {
			write_session($username.'_highest_today', $current_price);
			write_session($username.'_lowest_today', $current_price);
		}
		
		$get_total = file_get_contents($folder_config."/logs/".$folder_user.'/'.$date0011.'_'.$username);
		if(!empty($get_total)){
			$lines = explode("\n", $get_total);
			$first_key = 0;
			$last_key = count($lines) - 1;
			foreach($lines as $key => $line){
				if(strpos($lines[$key], 'experimental feature') === false){
					$first_key = $key;
					break;
				}
			}
			
			for($i = (((count($lines) - 1) > -1) ? (count($lines) - 1) : 0); $i > -1; $i--){
				if(isset($lines[$i]) && !empty($lines[$i]) && strpos($lines[$i], 'experimental feature') === false && 
				strpos($lines[$i], '00:00:') !== false) {
					$last_key = $i;
					break;
				} else if (isset($lines[$i]) && !empty($lines[$i]) && strpos($lines[$i], 'experimental feature') === false) {
					$last_key = $i;
					break;
				}
			}
			
			$first = explode('=', $lines[$first_key]);
			$first = explode('|',$first[1]);
			$first = explode('/',$first[4]);
			$last = explode('=', $lines[$last_key]);
			$last = explode('|',$last[1]);
			$last = explode('/',$last[4]);
			
			$get_total =  floatval($last[1]) - floatval($first[1]);
			$get_total =  number_format($get_total, 2, '.', '');
			write_session($username.'_price_today', $get_total);
			
			if(intval($get_total) != 0 && $date_time_operate == $end_hour_int && $to_email != '' && $from_email != '' && $smtp_host != '' && $smtp_username != '' && $smtp_password != '' && $smtp_port != '') {
				if(intval($get_total) > 0){
					$mail_body = "Today, you gained ".$get_total."$";
				} else {
					$mail_body = "Today, you lost ".$get_total."$";
				}

				$mail = new PHPMailer(true);

				try {
					//Server settings
					//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
					$mail->isSMTP();                                            //Send using SMTP
					$mail->Host       = $smtp_host;                     //Set the SMTP server to send through
					$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
					$mail->Username   = $smtp_username;                     //SMTP username
					$mail->Password   = $smtp_password;                               //SMTP password
					$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
					$mail->Port       = $smtp_port;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

					//Recipients
					$mail->setFrom($from_email, 'Trading-API');
					$mail->addAddress($to_email);     //Add a recipient
					
					$mail->isHTML(true);
					$mail->Subject = "Trading Results";
					$mail->Body    = $mail_body;
					$mail->AltBody = $mail_body;

					$mail->send();
				} catch (Exception $e) {
					
				}
			}
			
			$lines = array_reverse($lines);
			if(isset($lines[1])){
				$first1 = explode('=', $lines[1]);
				if(isset($first1[0])){
					$first2 = explode(' ', $first1[0]);
					foreach($lines as $key => $file){
						$last1 = explode('=', $lines[$key]);
						if(isset($last1[0])){
							$last2 = explode(' ', $last1[0]);
							if(isset($last2[2])){
								$last3 = explode('*', $last2[2]);
								
								if(isset($last2[0]) && isset($last2[1]) && isset($first2[0]) && isset($first2[1]) && isset($last3[1]) &&
								strval($last3[1]) > strval($price_reached)){
									$line_time = new DateTime($last2[0]. ' '. $last2[1]);
									$current_time = $line_time->diff(new DateTime($first2[0]. ' '. $first2[1]));
									$range_time = $current_time->h.'h'. str_pad($current_time->i, 2, "0", STR_PAD_LEFT);
									write_session($username.'_time_lowest_limit', $range_time);
									break;
								}
							}
						}
					}
				}
			}
		}
		
		$total_money_logs = 0;
		$files = dirToArray('../config/logs/'.$folder_user);
		$total_money_withdrew = use_session('config_'.$username.'_total_money_withdrew');
		$total_money_deposited = use_session('config_'.$username.'_total_money_deposited');
		
		$total_money_withdrew = explode(',', $total_money_withdrew);
		$total_money_deposited = explode(',', $total_money_deposited);
		$total_money_difference = 0;
		
		foreach($files as $key => $file){
			$filename_logs = explode('_', $file);
			$filename_logs = str_replace('-', '', $filename_logs[0]);
			$get_total = file_get_contents($folder_config."/logs/".$folder_user.'/'.$file);
			if(!empty($get_total)){
				$lines = explode("\n", $get_total);
				$first_key = 0;
				$last_key = count($lines) - 1;
				foreach($lines as $key => $line){
					if(strpos($lines[$key], 'experimental feature') === false){
						$first_key = $key;
						break;
					}
				}
				
				for($i = (((count($lines) - 1) > -1) ? (count($lines) - 1) : 0); $i > -1; $i--){
					if(isset($lines[$i]) && !empty($lines[$i]) && strpos($lines[$i], 'experimental feature') === false && 
					strpos($lines[$i], '00:00:') !== false) {
						$last_key = $i;
						break;
					} else if (isset($lines[$i]) && !empty($lines[$i]) && strpos($lines[$i], 'experimental feature') === false) {
						$last_key = $i;
						break;
					}
				}
				
				$first = explode('=', $lines[$first_key]);
				$first = explode('|',$first[1]);
				$first = explode('/',$first[4]);
				$last = explode('=', $lines[$last_key]);
				$last = explode('|',$last[1]);
				$last = explode('/',$last[4]);
				
				$get_total =  floatval($last[1]) - floatval($first[1]);	
				
				foreach($total_money_withdrew as $key => $value){
					$parts = explode(':', $value);
					$parts_date = explode('-', $parts[0]);
					if(isset($parts_date[2]) && strlen($parts_date[2]) == 4){
						$parts[0] = $parts_date[2].'-'.$parts_date[1].'-'.$parts_date[0];
					}
					$parts[0] = str_replace('-', '', $parts[0]);
					if(intval($parts[0]) == intval($filename_logs)) {
						$total_money_difference = $total_money_difference - floatval($parts[1]);
					}
				}
				
				foreach($total_money_deposited as $key => $value){
					$parts = explode(':', $value);
					$parts_date = explode('-', $parts[0]);
					if(isset($parts_date[2]) && strlen($parts_date[2]) == 4){
						$parts[0] = $parts_date[2].'-'.$parts_date[1].'-'.$parts_date[0];
					}
					$parts[0] = str_replace('-', '', $parts[0]);
					if(intval($parts[0]) == intval($filename_logs)) {
						$total_money_difference = $total_money_difference + floatval($parts[1]);
					}
				}
				
				$get_total =  number_format($get_total, 2, '.', '');
			}
			$total_money_logs = $total_money_logs + floatval($get_total);
		}
		write_session($username.'_total_money_difference', $total_money_difference);
		write_session($username.'_price_total_from_today', $total_money_logs);
		write_session($username.'_price_total_median', ($total_money_logs / count($files)));
		
		if($date_time_operate > $start_hour_int && $date_time_operate < $end_hour_int) {
			if($price_switch == 1 && use_session($username.'_price_reached_block_buy_start') == 0){
				if(use_session($username.'_highest_today_sell') == '' || strval(use_session($username.'_highest_today_sell')) < strval($current_price)){
					write_session($username.'_highest_today_sell', $current_price);
					write_session($username.'_time_sell', time());
				}
			}				
			if($detect_lowest == 1 && (use_session($username.'_trigger_lowest_price') == 0 || strval(use_session($username.'_trigger_lowest_price')) > strval($current_price)) && 
			use_session($username.'_price_reached_block_buy_start') == 1 && $price_switch == 1){
				write_session($username.'_trigger_lowest_price', $current_price);
				write_session($username.'_time', time());
			}			
		}
		
		if(use_session($username.'_highest_today') == '' || strval(use_session($username.'_highest_today')) < strval($current_price)){
			write_session($username.'_highest_today', $current_price);
		}
		if(use_session($username.'_lowest_today') == '' || strval(use_session($username.'_lowest_today')) > strval($current_price)){
			write_session($username.'_lowest_today', $current_price);
		}
		
		if($detect_lowest == 1 && (intval(use_session($username.'_time') + $detect_lowest_time) < intval(time())) && $price_switch == 1 && use_session($username.'_price_reached_block_buy_start') == 1){
			write_session($username.'_trigger_lowest_price', number_format(use_session($username.'_trigger_lowest_price') + ($current_price * 0.00001), 3, '.', ''));
		}
		
		if(use_session($username.'_count_long') == ''){
			write_session($username.'_count_long', 0);
		}
		
		if(use_session($username.'_count_x_buy') == ''){
			write_session($username.'_count_x_buy', 0);
		}
		
		if($action != 'buy'){
			write_session($username.'_count_x_buy', use_session($username.'_count_x_buy') + 1);
		} else {
			write_session($username.'_count_x_buy', 0);
		}
		
		if($sub_method == 'extra_long' && $action == 'buy'){
			if(use_session($username.'_long_increase_range_total') == ''){
				write_session($username.'_long_increase_range_total', $past_range_extra_long_increase);
			} else {
				write_session($username.'_long_increase_range_total', use_session($username.'_long_increase_range_total') + $past_range_extra_long_increase);
			}
			if(use_session($username.'_long_increase_increment_total') == ''){
				write_session($username.'_long_increase_increment_total', $increment_range_extra_long);
			} else {
				write_session($username.'_long_increase_increment_total', use_session($username.'_long_increase_increment_total') + $increment_range_extra_long);
			}
			write_session($username.'_count_long', use_session($username.'_count_long') + 1);
		} else {
			write_session($username.'_long_increase_increment_total', 0);
			write_session($username.'_long_increase_range_total', 0);
			write_session($username.'_count_long', 0);
		}
		
		$third_quarter = 0;
		if(strval(use_session($username.'_long_increase_increment_total')) > '0'){
			$third_quarter = use_session($username.'_long_increase_range_total') / use_session($username.'_long_increase_increment_total');
		}
		
		if($detect_lowest == 1 && use_session($username.'_price_reached_block_buy_start') == 1 && (((intval(use_session($username.'_time') + $detect_lowest_time) < intval(time())) && 
		strval(use_session($username.'_trigger_lowest_price') + ($current_price * 0.005)) > strval($current_price)) || (use_session($username.'_count_long') > 1 && strval($third_quarter) < '0.65' && $detect_lowest_break == 1)) && $price_switch == 1){
			write_session('config_'.$username.'_price_reached', $current_price);
			write_session($username.'_trigger_lowest_price', $current_price);
			write_session($username.'_price_reached_block_buy_start', 0);
			write_session($username.'_highest_today_sell', $current_price);
			write_session($username.'_wait_time_sell', time());	
			write_session($username.'_price_operate', 0);
			write_session($username.'_time_sell', time());
			write_session($username.'_time', time());
		}
		
		if($top_level_limit_adjust == 1 && $date_time_operate == $end_hour_adjust_top_level){
			$new_top_level_limit = floatval(use_session($username.'_highest_today')) * floatval($top_level_adjust_percentage);
			write_session('config_'.$username.'_top_level_range', $new_top_level_limit);
		}
		
		$price_array_row = 0;
		$new_row = 0;
		
		write_session($username.'_enough_money', 0);
		if(isset($buy_balance_quantity) && strval($buy_balance_quantity) > strval($buy_quantity)){
			write_session($username.'_enough_money', 1);
		}
		
		$final = json_encode(array('Transaction' => 'No transaction has been completed for this minute. No indicators of the following had been triggered by the system.')); 
		if(use_session($username.'_operate_gain') == 1) {
			$final = json_encode(array('Transaction' => 'The today\'s total has reached the maximum gain limit ('.$gain_limit_money.'$) for the day. The operation will resume tomorrow at 00:05.'));
		} elseif(use_session($username.'_operate') == 1) {
			$start_hour_display = str_pad($start_hour_hours, 2, "0", STR_PAD_LEFT).":".str_pad($start_hour_minutes, 2, "0", STR_PAD_LEFT);
			$end_hour_display = str_pad($end_hour_hours, 2, "0", STR_PAD_LEFT).":".str_pad($end_hour_minutes, 2, "0", STR_PAD_LEFT);
			$final = json_encode(array('Transaction' => 'Out of the time to operate. The hours to operate are: '.$start_hour_display.' to '.$end_hour_display.'. Will operate again when the start time is reached'));
		} elseif(use_session($username.'_price_operate') == 1 && $detect_lowest == 0) {
			$final = json_encode(array('Transaction' => 'Price limit is reached. The transaction are disabled until the price reach '.$price_reached.' because the price self-adjust option is off.'));
		} elseif($check == 1 && (((isset($buy_balance_quantity) && strval($buy_balance_quantity) > strval($buy_quantity) && $action == 'buy' && use_session($username.'_price_sell_all') == 0 && $sell_all_low_cancel == 1) ||
		(isset($buy_balance_quantity) && strval($buy_balance_quantity) > strval($buy_quantity) && $action == 'buy' && use_session($username.'_price_sell_all') == 0 && $sell_all_low_cancel == 0 && strval($price_reached) < strval($current_price))) || 
		(strval($sell_balance_quantity) > strval($sell_quantity) && ($action == 'sell' || use_session($username.'_price_sell_all') == 1)))) {
			$order_id = 0;
			if(strpos($username, 'binance_') !== false){
				$query = http_build_query($post_values);
				$signature = hash_hmac('sha256', $query, $secret_key);
				$post_values['signature'] = $signature;
				$query = http_build_query($post_values);

				$ch = curl_init('https://api.binance.com/api/v3/order');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded","X-MBX-APIKEY: ".$api_key));
				$final = curl_exec($ch);
				curl_close($ch);

				if(strpos(strtolower($final), 'insufficient balance') !== false){
					$final = json_encode(array('Transaction' => 'No transaction has been completed for this minute. No indicators of the following had been triggered by the system.'));
				} else {
					$transaction_array = json_decode($final, true);
					if(isset($transaction_array['orderId'])){
						$order_id = $transaction_array['orderId'];
					}
				}
			} elseif(strpos($username, 'coinbase_') !== false){
				$ch = curl_init('https://api.exchange.coinbase.com/orders');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
				$sign_key = base64_encode(hash_hmac('sha256', time().'POST/orders'.json_encode($post_values), base64_decode($secret_key), true));
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_values));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'ACCEPT: application/json',
				'CONTENT-TYPE: application/json',
				'CB-ACCESS-KEY: '.$api_key,
				'CB-ACCESS-SIGN: '.$sign_key,
				'CB-ACCESS-PASSPHRASE: '.$passphrase,
				'CB-ACCESS-TIMESTAMP: '.time()));
				$final = curl_exec($ch);
				curl_close($ch);
				
				if(strpos(strtolower($final), 'insufficient funds') !== false){
					$final = json_encode(array('Transaction' => 'No transaction has been completed for this minute. No indicators of the following had been triggered by the system.'));
				} else {
					$transaction_array = json_decode($final, true);
					if(isset($transaction_array['id'])){
						$order_id = $transaction_array['id'];
					}
				}
			} elseif(strpos($username, 'kraken_') !== false){
				$ch = curl_init('https://api.kraken.com/0/private/AddOrder');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$sign_key = base64_encode(hash_hmac('sha512', utf8_encode('/0/private/AddOrder').hash('sha256', utf8_encode((time() * 1000).'nonce='.(time() * 1000).'&'.http_build_query($post_values)), true), base64_decode($secret_key), true));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
				'API-Key: '.$api_key,
				'API-Sign: '.$sign_key));
				curl_setopt($ch, CURLOPT_POSTFIELDS, "nonce=".(time() * 1000).'&'.http_build_query($post_values));
				$final = curl_exec($ch);
				curl_close($ch);
				
				if(strpos(strtolower($final), 'insufficient funds') !== false){
					$final = json_encode(array('Transaction' => 'No transaction has been completed for this minute. No indicators of the following had been triggered by the system.'));
				} else {
					$transaction_array = json_decode($final, true);
					if(isset($transaction_array['result']) && isset($transaction_array['result']['txid'])){
						$order_id = $transaction_array['result']['txid'];
					}
				}
			}
			
			if($action == 'buy' && $order_id != 0 && use_session($username.'_price_sell_all') == 0){
				$price_array_row = array('time' => intval(time()), 'txid' => $order_id, 'price' => $current_price, 'query' => $post_values);
				$new_row = 1;
			}
			
			if(use_session($username.'_price_sell_all') == 1){
				$final = json_encode(array('Transaction' => 'In the selling all period because or whether the price limit has been reached or the operating end time limit has been crossed.'));
			}
		} else {
			if(use_session($username.'_price_sell_all') == 1 && empty($order_to_cancel)){
				write_session($username.'_txid_sell', array());
				
				if(use_session($username.'_gain_limit_trigger') == 1 && $gain_limit == 1){
					write_session($username.'_operate_gain', 1);
					write_session($username.'_gain_limit_trigger', 0);
					write_session($username.'_price_sell_all', 0);
				} elseif($date_time_operate >= $end_hour_int && $operate_switch == 1) {								
					write_session($username.'_operate', 1);
					write_session($username.'_price_sell_all', 0);
				} elseif(use_session($username.'_price_position') == 'above' && $price_switch == 1) {
					write_session($username.'_price_position', '');
					write_session($username.'_price_operate', 1);
					write_session($username.'_price_sell_all', 0);
					write_session($username.'_time_sell_trigger', 0);
				} elseif(use_session($username.'_price_position') == 'below' && $price_switch == 1) {
					write_session($username.'_price_position', '');
					write_session($username.'_price_sell_all', 0);
				} elseif(use_session($username.'_price_position') == 'none') {
					write_session($username.'_price_position', '');
					write_session($username.'_price_operate', 1);
					write_session($username.'_price_sell_all', 0);
					write_session($username.'_time_sell_trigger', 0);
				}
			}
			
			if($allow_payment == 0 && $action == 'buy' && $detect_count == 0 && $detect_lowest == 0){
				$final = json_encode(array('Transaction' => 'No purchase is allowed because the price is above '.($top_level_percentage * 100).'% in between the highest and lowest today\'s price'));
			} elseif(use_session($username.'_price_reached_block_buy_start') == 1 && $detect_lowest == 1) {
				$time_left = floor(((use_session($username.'_time') + $detect_lowest_time) - time()) / 60);
				$time_left = ($time_left > 0) ? $time_left : 0;
				$final = json_encode(array('Transaction' => 'Buying is blocked because the low price detection isn\'t done yet. The low price detection will be set in '.$time_left.' minutes if it stay above '.use_session($username.'_trigger_lowest_price').'$'));
			} elseif($detect_count == 1) {
				$final = json_encode(array('Transaction' => 'The price is stagnant. Therefore no transaction could be made. You will need an RSI higher than '.$rsi_top_long.' or lower than '.$rsi_bottom_long.' to execute transactions.'));
			} else {
				$final = json_encode(array('Transaction' => 'No triggers were fired for the moment. The price is neither in lowest detection mode; in between the RSI limit or above the top level limit.'));
			}
		}
		
		$today = date('Y-m-d');
		$path4 = $folder_config."/logs/".$folder_user.'/'.$date0011.'_'.$username;
		
		if(($block_selling == 0 || ($block_selling == 1 && use_session($username.'_price_sell_all') == 1))){} else {
			if($action == 'sell'){
				$action = '';
				$method = '';
				$sub_method = '';
			}
		} 
		if($block_buying == 0){} else {
			if($action == 'buy'){
				$action = '';
				$method = '';
				$sub_method = '';
			}
		} 
		if(!file_exists($path4)){
			$myfile4 = fopen($path4, "w") or die("Unable to open file! 123");
		} else {
			$myfile4 = fopen($path4, "a") or die("Unable to open file! 123");
		}
		if($future_ind == 1){
			$final_array_count = (strval($array_count) > '0') ? '+'.number_format($array_count, 3, '.', '') : number_format($array_count, 3, '.', '');
			$final_array_count_limit = ($action == 'sell' ? '+'.number_format($buy_at, 3, '.', '') : number_format($sell_at, 3, '.', ''));
		} else {
			$final_array_count = 0;
			$final_array_count_limit = 0;
		}
		$final_increment_range = (strval($increment_range) > '0' ? '+'.number_format($increment_range, 3, '.', '') : number_format($increment_range, 3, '.', ''));
		$final_increment_range_limit = ($action == 'sell' ? '+'.number_format($past_range_increase, 3, '.', '') : number_format($past_range_decrease, 3, '.', ''));
		$final_increment_range_long = (strval($increment_range_long) > '0' ? '+'.number_format($increment_range_long, 3, '.', '') : number_format($increment_range_long, 3, '.', ''));
		$final_increment_range_long_limit = ($action == 'sell' ? '+'.number_format($past_range_long_increase, 3, '.', '') : number_format($past_range_long_decrease, 3, '.', ''));
		$final_increment_range_extra_long = (strval($increment_range_extra_long) > '0' ? '+'.number_format($increment_range_extra_long, 3, '.', '') : number_format($increment_range_extra_long, 3, '.', ''));
		$final_increment_range_extra_long_limit = ($action == 'sell' ? '+'.number_format($past_range_extra_long_increase, 3, '.', '') : number_format($past_range_extra_long_decrease, 3, '.', ''));
		$final_ratio = ($action == 'sell') ? '+'.number_format($increasing_ratio, 3, '.', '') : '-'.number_format($decreasing_ratio, 3, '.', '');
		$final_ratio_limit = ($action == 'sell' ? '+'.number_format($defined_increasing_ratio, 3, '.', '') : '-'.number_format($defined_decreasing_ratio, 3, '.', ''));
		$rsi_result = number_format(($action == 'sell' ? $rsi_top_long : $rsi_bottom_long), 3, '.', '');
		
		$current_range_ai = (strval($current_range_ai) > '0') ? '+'.$current_range_ai : $current_range_ai;
		fwrite($myfile4, $date00.' '.number_format(floatval(microtime()), 8, '.', '').'*'.$current_price.' : '.$current_range_ai."=".$final."|F: ".$final_array_count.' / '.$final_array_count_limit."|S: ".$final_increment_range.' / '.$final_increment_range_limit.
		"*L: ".$final_increment_range_long.' / '.$final_increment_range_long_limit.
		"*XL: ".$final_increment_range_extra_long.' / '.$final_increment_range_extra_long_limit."|".$action."|".number_format($total81, 3, '.', '')."/".number_format($total_money, 3, '.', '')."|R: ".
		$final_ratio.' / '.$final_ratio_limit."|".$method."||".$sub_method."|".number_format($step_one_long, 3, '.', '')." / ".$rsi_result."\n");
		fclose($myfile4);
		
		if($new_row == 1 && $price_array_row != 0 && $precise_selling == 1){
			$price_array = use_session($username.'_price_array');
			$price_array_row['line'] = json_encode(array('Transaction' => 'In the selling all period because the price went below this transaction original price. This transaction is related to the experimental feature.'));
			$price_array_row['money'] = number_format($total81, 3, '.', '')."/".number_format($total_money, 3, '.', '');
			$price_array[] = $price_array_row; 
			write_session($username.'_price_array', $price_array);
			write_session($username.'_detect_purchase_lower', 0);
		}
		
		if(use_session($username.'_time_news') == '') {
			write_session($username.'_time_news', time() - 900);
		}
		
		if(use_session($username.'_crypto_news') == '' || $crypto_news_api_key == '') {
			write_session($username.'_crypto_news', json_encode(array()));
		}
		
		if(intval(use_session($username.'_time_news') + 900) < intval(time())){
			if(!empty($crypto_news_api_key) && !empty($coin_value)){
				$ch = curl_init('https://cryptonews-api.com/api/v1?tickers='.$coin_value.'&items=10&token='.$crypto_news_api_key);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$news_final = curl_exec($ch);
				$news = json_decode($news_final, true);
				curl_close($ch);
				$news_array =  array();
				if(isset($news['data'])){
					foreach($news['data'] as $key => $value){
						$news_array[$key]['text'] = $news['data'][$key]['text'];
						$news_array[$key]['sentiment'] = $news['data'][$key]['sentiment'];
						$news_array[$key]['url'] = $news['data'][$key]['news_url'];
					}
				}
				$news_array = json_encode($news_array);
				write_session($username.'_time_news', time());
				write_session($username.'_crypto_news', $news_array);
			
				$decoded = json_decode(use_session($username.'_crypto_news'), true);
				if(!empty($decoded)){
					$table = array();
					foreach($decoded as $key => $value) {
						if(strtolower($decoded[$key]['sentiment']) == 'negative'){
							$range_adjust = '-1';
							$class = 'text-danger';
						} elseif(strtolower($decoded[$key]['sentiment']) == 'positive'){
							$range_adjust = '+1';
							$class = 'text-success';
						} elseif(strtolower($decoded[$key]['sentiment']) == 'neutral'){
							$range_adjust = '0.00';
							$class = 'text-balance';
						}
						$text = substr($decoded[$key]['text'], 0, 160);
						$text = explode(' ', $text);
						$description = array_pop($text);
						$text = implode(' ', $text);
						$text = str_replace('=', '((^))', '<a href="'.$decoded[$key]['url'].'" class="'.$class.'">'.$text.'...</a>');
						$text = str_replace('*', '((_))', $text);
						$text = str_replace('|', '((-))', $text);
						$table[] = htmlspecialchars($text, ENT_QUOTES, 'UTF-8').' Sentiment:'.$decoded[$key]['sentiment'].' ('.$range_adjust.')';
					}
					$table = json_encode($table);

					if(!file_exists($path4)){
						$myfile4 = fopen($path4, "w") or die("Unable to open file! 123");
					} else {
						$myfile4 = fopen($path4, "a") or die("Unable to open file! 123");
					}
					fwrite($myfile4, $date00.' '.number_format(floatval(microtime()), 8, '.', '').'*'.$current_price.' : '.$current_range_ai."=".$table."|F: ".$final_array_count.' / '.$final_array_count_limit."|S: ".$final_increment_range.' / '.$final_increment_range_limit.
					"*L: ".$final_increment_range_long.' / '.$final_increment_range_long_limit.
					"*XL: ".$final_increment_range_extra_long.' / '.$final_increment_range_extra_long_limit."|".$action."|".number_format($total81, 3, '.', '')."/".number_format($total_money, 3, '.', '')."|R: ".
					$final_ratio.' / '.$final_ratio_limit."|".$method."||".$sub_method."|".number_format($step_one_long, 3, '.', '')." / ".$rsi_result."\n");
					fclose($myfile4);
				}
			}
		}
		
		if(use_session($username.'_total_money') == '') {
			write_session($username.'_total_money', $total_money);
		}
		
		if($self_adjust == 1){
			if($action_count == 'buy'){
				write_session($username.'_buy', use_session($username.'_buy') + 1);
				write_session($username.'_buy_sell_count', use_session($username.'_buy_sell_count') + 1);
			}
			
			if($action_count == 'sell'){
				write_session($username.'_sell', use_session($username.'_sell') + 1);
				write_session($username.'_buy_sell_count', use_session($username.'_buy_sell_count') + 1);
			}
			
			if($future_ind == 1){
				if(strval($array_count_storing) > '0'){
					$_array_count_increasing_count = ($array_count_storing_count == 0) ? 1 : $array_count_storing_count;
					$array_count_positive = $array_count_storing / $_array_count_increasing_count;
					$array_count_positive = (strval($array_count_positive) <= '0') ? 0.5 : $array_count_positive;
					$array_count_positive = number_format($array_count_positive, 3, '.', '');
					
					write_session('config_'.$username.'_buy_at', $array_count_positive);
				}
				
				if(strval($array_count_storing) < '0'){	
					$_array_count_decreasing_count = ($array_count_storing_count == 0) ? 1 : $array_count_storing_count;
					$array_count_negative = $array_count_storing / $_array_count_decreasing_count;
					$array_count_negative = (strval($array_count_negative) >= '0') ? -0.5 : $array_count_negative;
					$array_count_negative = number_format($array_count_negative, 3, '.', '');
					
					write_session('config_'.$username.'_sell_at', $array_count_negative);
				}
			}
			
			if(use_session($username.'_buy_sell_count') > ($m * 2)){
				write_session($username.'_buy_sell_count', 0);
				write_session($username.'_buy', 0);
				write_session($username.'_sell', 0);
			}
		}
	}
}

$folder_config = dirToArray($path.'config');
asort($folder_config);
foreach($folder_config as $key9 => $value9){
	$date_now2 = new DateTime();
	$date_now2->setTimezone(new DateTimeZone($default_timezone));
	if(!is_array($value9) && $value9 != '.htaccess'){
		$get_price = sell_buy($connect, $value9, $path.'config', $date_now2, array(), $path);
	}
}
?>
<?php 
ini_set('display_errors', 1);

function execute_experimental($path, $username, $date_object, $detect_count, $price, $server_time_average, $step_one){
	$api_key = use_session('config_'.$username.'_api_key');
	if($api_key == ''){
		return false;
	}
	$extreme_bearish_peanuts = use_session('config_'.$username.'_extreme_bearish_peanuts');
	$extra_long_rsi_selling = use_session('config_'.$username.'_extra_long_rsi_selling');
	$extra_long_rsi_selling_limit = use_session('config_'.$username.'_extra_long_rsi_selling_limit');
	$step_one_extra_long = use_session($username.'_rsi_extra_long');
	
	$secret_key = use_session('config_'.$username.'_secret_key');
	$passphrase = use_session('config_'.$username.'_passphrase');
	$desired_nonbuy = use_session('config_'.$username.'_desired_nonbuy');
	$disable_low_limit = use_session('config_'.$username.'_disable_low_limit');
	$desired_amount_extra = use_session('config_'.$username.'_desired_amount');
	$selling_fee = use_session('config_'.$username.'_selling_fee');
	$buying_fee = use_session('config_'.$username.'_buying_fee');
	$sell_coin = use_session('config_'.$username.'_sell_coin');
	$max_precision = use_session('config_'.$username.'_sell_quantity');
	$max_precision = explode('.', $max_precision);
	$max_precision = isset($max_precision[1]) ? strlen($max_precision[1]) : 0;
	
	$current_range_price = 0;		
	if(strval(use_session($username.'_price_ai')) > '0' && strval(use_session($username.'_price_old_ai')) > '0'){
		$current_range_price = use_session($username.'_price_ai') - use_session($username.'_price_old_ai');
		$current_range_price = number_format($current_range_price, 3, '.', '');
	}
	
	$txid_sell = (use_session($username.'_txid_sell') == '') ? array() : use_session($username.'_txid_sell');
	$price_array = use_session($username.'_price_array');
	$txid_time = use_session($username.'_txid_time');

	$current_transaction_selling = array();
	$date0011 = $date_object->format('Y-m-d');
	$date00 = $date_object->format('Y-m-d H:i:s');
	$folder_user = str_replace('.txt', '', $username);
	$path4 = $path.'config/logs/'.$folder_user.'/'.$date0011.'_'.$username;
	$time_general = use_session($username.'_time_general');

	if((!empty($price_array) && is_array($price_array)) && (empty($time_general) || intval($time_general) <= intval(time() - 5))){	
		write_session($username.'_time_general', time());
		$desired_sell = use_session('config_'.$username.'_desired_sell');
		
		foreach($price_array as $key1 => $value1){
			$sell_array = array(
				'binance_' => 'quantity',
				'coinbase_' => 'size',
				'kraken_' => 'volume'
			);
			
			foreach($sell_array as $key9 => $value9){
				if(strpos($username, $key9) !== false){
					$desired_amount = ((($price_array[$key1]['query'][$value9])) * $price_array[$key1]['price']) + $desired_amount_extra;
					$starting_amount = (($price_array[$key1]['query'][$value9]) * $price_array[$key1]['price']);
					$current_amount = (($price_array[$key1]['query'][$value9]) * $price);
					$total_tx = ((($current_amount - $starting_amount) - ($current_amount * $selling_fee)) - ($starting_amount * $buying_fee));
					$selling_price = ((($desired_amount + ($desired_amount * $selling_fee)) + ($starting_amount * $buying_fee)) / ($starting_amount)) * $price_array[$key1]['price'];
				}
			}
			
			if(strval($price_array[$key1]['price']) < strval($price) || !isset($txid_time[$price_array[$key1]['txid']])){
				$txid_time[$price_array[$key1]['txid']] = intval(time());
				write_session($username.'_txid_time', $txid_time);
			}
			
			if(((strval($price_array[$key1]['price']) > strval($price) && $disable_low_limit == 0 && intval($txid_time[$price_array[$key1]['txid']] + ($desired_sell - intval(((floor(60 * ((100 - $step_one) / 100))) * floor($desired_sell / 60)) + ((($server_time_average / 4) * 60) * floor($desired_sell / 60))))) <= intval(time()) && $detect_count == 0) || (intval(use_session($username.'_count_x_buy')) >= intval($desired_nonbuy) && $disable_low_limit == 0 && strval($price_array[$key1]['price']) > strval($price)) || (strval($selling_price) < strval($price) && $extreme_bearish_peanuts == 1) || isset($txid_sell[$price_array[$key1]['txid']])) || ($extra_long_rsi_selling == 1 && (strval($step_one_extra_long) < strval($extra_long_rsi_selling_limit)) && strval($step_one_extra_long) != '')){
				if(isset($txid_sell[$price_array[$key1]['txid']])){
					$cleared = 1;
					/******************** BINANCE *********************/
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
						
						$order_to_cancel = array();
						$open_orders = json_decode($open_orders, true);
						if(isset($open_orders[0])){
							foreach($open_orders as $key => $value){
								if($txid_sell[$price_array[$key1]['txid']]['id'] == $value['orderId']){
									$cleared = 0;
								}
							}
						}
					/******************** COINBASE *********************/
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
						
						$order_to_cancel = array();
						$open_orders = json_decode($response, true);
						if(isset($open_orders[0])){
							foreach($open_orders as $key => $value){
								if($txid_sell[$price_array[$key1]['txid']]['id'] == $value['id']){
									$cleared = 0;
								}
							}
						}
					/******************** KRAKEN *********************/
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
						
						$order_to_cancel = array();
						$open_orders = json_decode($response, true);
						if(isset($open_orders['result'])){
							foreach($open_orders['result']['open'] as $key => $value){
								if($txid_sell[$price_array[$key1]['txid']]['id'] == $key){
									$cleared = 0;
								}
							}
						}
					}
					
					if($cleared == 1){
						unset($txid_sell[$price_array[$key1]['txid']]);
						write_session($username.'_txid_sell', $txid_sell);
						unset($price_array[$key1]);
						write_session($username.'_price_array', $price_array);
						if(count($price_array) == 0 && intval(use_session('config_'.$username.'_wait_time_sell_delay_low')) > 3600){
							write_session('config_'.$username.'_price_reached', $price);
						}
					}
				} else {
					$current_transaction_selling[$price_array[$key1]['txid']] = $price_array[$key1];
				}
			}
		}
		
		if(!empty($current_transaction_selling) && (count($current_transaction_selling) > 1 || count($price_array) == 1)){
			$size_total = 0;
			
			$array_type = array(
				'binance_' => array('quantity', 'symbol'),
				'coinbase_' => array('size', 'product_id'),
				'kraken_' => array('volume', 'pair')
			);
			$count_size = 0;
			foreach($current_transaction_selling as $key4 => $value4){
				foreach($array_type as $key8 => $value8){
					if(strpos($username, $key8) !== false){
						$size_total = $size_total + $current_transaction_selling[$key4]['query'][$value8[0]];
						$symbol = $current_transaction_selling[$key4]['query'][$value8[1]];
						$count_size++; 
					}
				}
			}
			
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
								if($y['asset'] == $sell_coin){
									$sell_balance = $y['free'];
									$sell_balance_quantity = number_format(floatval($sell_balance), $max_precision, '.', '');
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
								if($value_account[$key7]['currency'] == $sell_coin){
									$sell_balance = $value_account[$key7]['available'];
									$sell_balance_quantity = number_format(floatval($sell_balance), $max_precision, '.', '');
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
							if($key7 == $sell_coin){
								$sell_balance = $y;
								$sell_balance_quantity = number_format(floatval($sell_balance), $max_precision, '.', '');
							}
						}
					}
				}
			}
			
			if($count_size == 0){
				$count_size = 1;
			}
			$ratio_gain = $desired_amount_extra / $price;
			$size_total = $size_total + ($count_size * $ratio_gain);
			$size_total = number_format($size_total, $max_precision, '.', '');
			
			$sell_quantity = 0;
			if(use_session('config_'.$username.'_min_quantity') != '' && strval($sell_balance_quantity) < strval($size_total) && 
			strval(use_session('config_'.$username.'_min_quantity')) < strval($sell_balance_quantity)){
				$sell_quantity = number_format(floatval($sell_balance_quantity - (($sell_balance_quantity * $selling_fee) + ($sell_balance_quantity * 0.01))), $max_precision, '.', '');
			}
			
			$sell_price_output = number_format($price - ($price * 0.03), intval(use_session('config_'.$username.'_quote_precision')), '.', '');
			
			if(strpos($username, 'binance_') !== false){
				$post_values = [
					'symbol' => $symbol,
					'side'   => 'SELL',
					'type'   => 'LIMIT',
					'timeInForce'   => 'GTC',
					'quantity'   => $size_total,
					'price' => $sell_price_output,
					'recvWindow' => '5000',
					'timestamp'   => (time() * 1000)
				];
				
				if($sell_quantity != 0){
					$post_values['quantity'] = $sell_quantity;
				}
			} elseif(strpos($username, 'coinbase_') !== false){
				$post_values = [
					'type' => 'limit',
					'side' => 'sell',
					'time_in_force' => 'GTC',
					'price' => $sell_price_output,
					'size' => $size_total,
					'product_id' => $symbol,
				];
				
				if($sell_quantity != 0){
					$post_values['size'] = $sell_quantity;
				}
			} elseif(strpos($username, 'kraken_') !== false){
				$post_values = [
					'ordertype' => 'limit',
					'type' => 'sell',
					'timeinforce' => 'GTC',
					'price' => $sell_price_output,
					'volume' => $size_total,
					'pair' => $symbol,
				];
				
				if($sell_quantity != 0){
					$post_values['volume'] = $sell_quantity;
				}
			}
			
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
				
				$transaction_array = json_decode($final, true);
				if(isset($transaction_array['orderId'])){
					$order_id = $transaction_array['orderId'];
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
				
				$transaction_array = json_decode($final, true);
				if(isset($transaction_array['id'])){
					$order_id = $transaction_array['id'];
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
				
				$transaction_array = json_decode($final, true);
				if(isset($transaction_array['result']) && isset($transaction_array['result']['txid'])){
					$order_id = $transaction_array['result']['txid'];
				}
			}
			
			if($order_id != 0){	
				$ids = array();
				$total_tx = 0;
				$line_text = json_encode(array());
				$money_text = 0;
				$total_txid = 0;
				
				foreach($current_transaction_selling as $key7 => $value7){
					$txid_sell[$key7] = array('id' => $order_id, 'time' => time());
					$ids[] = $key7;
					$sell_array = array(
						'binance_' => 'quantity',
						'coinbase_' => 'size',
						'kraken_' => 'volume'
					);
					foreach($sell_array as $key10 => $value10){
						if(strpos($username, $key10) !== false){
							$starting_amount = (($current_transaction_selling[$key7]['query'][$value10]) * $current_transaction_selling[$key7]['price']);
							$current_amount = (($current_transaction_selling[$key7]['query'][$value10]) * $price);
							$total_tx = $total_tx + ((($current_amount - $starting_amount) - ($current_amount * $selling_fee)) - ($starting_amount * $buying_fee));
							$total_txid = $total_txid + $starting_amount;
						}
					}
					
					$line_text = $current_transaction_selling[$key7]['line'];
					$money_text = $current_transaction_selling[$key7]['money'];
				}					
				
				$total_tx = number_format($total_tx, 2, '.', '');
				$line = json_decode($line_text, true);
				if(strval($total_tx) > '0'){
					$line['Transaction'] = 'In the selling all period because the price went above this transaction original price. This transaction is related to the experimental feature.';
				} else {
					$line['Transaction'] = 'In the selling all period because the price went below this transaction original price. This transaction is related to the experimental feature.';
				}
				$line['id'] = implode(', ', $ids);
				$line['result'] = (strval($total_tx) > '0') ? 'Gained: +'.$total_tx : 'Lost: '.$total_tx;
				$line_encode = json_encode($line);
				$total_money = explode('/', $money_text);
				$total_money[0] = number_format($total_txid, 3, '.', '');
				$total_money = implode('/', $total_money);
				
				if(!file_exists($path4)){
					$myfile4 = fopen($path4, "w") or die("Unable to open file! 123");
				} else {
					$myfile4 = fopen($path4, "a") or die("Unable to open file! 123");
				}
				fwrite($myfile4, $date00.' '.number_format(floatval(microtime()), 8, '.', '').'*'.number_format($price, 3, '.', '')." : ".$current_range_price."=".$line_encode."|0|0*0*0|0|".$total_money."|0|0|0|0|0\n");
				fclose($myfile4);
				write_session($username.'_txid_sell', $txid_sell);
			}
		}
	}
}

?>
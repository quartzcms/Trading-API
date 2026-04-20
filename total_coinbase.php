<?php 
	session_start(); 
	
	include "config.php";
	include "initialize.php";
	
	if(!isset($_SESSION['logged_in'])){
		header('Location: weekly.php');
	}
	
	if(isset($_POST['login']) && isset($_POST['password'])){
		if($_POST['login'] == $login && $_POST['password'] == $password){
			$_SESSION['logged_in'] = 1;
			header('Location: weekly.php');
		}
	}	
?><!DOCTYPE html>
<html>
	<head>
		<title><?php $symbol = array_keys($stocks_array, $crypto_folder); echo $symbol[0].' - '.strtoupper(str_replace('_', ' ', $_SESSION['crypto'])); ?> - Graph</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<link rel="stylesheet" href="assets/bootstrap.min.css" />
		<script src="assets/jquery-3.6.0.min.js"></script>
		<link rel="stylesheet" href="assets/charts.min.css">
		<?php include "includes/header.php"; ?>
	</head>
	<body>
		<?php if (isset($_SESSION['logged_in'])){ ?>
			<div class="container-fluid">
				<div class="row" style="display:none;">
					<div class="col-md-12">
						<h1 align="center" style="text-transform: uppercase;" class="text-success">Generate Statement</h1>
					</div>
				</div>
				<div class="row" style="display:none;">
					<div class="col-md-2">
						<div style="padding: 10px;" align="center">
							<h3>Miscellaneous</h3>
							<div class="buttons" style="display:none">
								<?php $current_date = date('Hi'); ?>
								<span id="<?php echo $current_date.'00'; ?>" class="current-date btn btn-primary">Scroll to current</span>
							</div>
							<h4 align="center"><?php $files = dirToArray('crypto/'.$crypto_folder.'/data_yearly'); echo count($files); ?> day collected</h4>
							<h4 class="text-warning range-between" style="display:none">Est.: <span></span></h4>
						</div>
					</div>
					<div class="col-md-8">
						<?php include "includes/main.php"; ?>
					</div>
					<div class="col-md-2">
						<div style="padding: 10px;" align="center">
							<h3>Gold Period</h2>
							<p>Click on the gold labels to <br />scroll on the timeline.</p>
							<div class="periods">
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="container-fluid">
				<div class="row" style="display:none;">
					<div class="col-md-12">
						<?php
							date_default_timezone_set('UTC');
							$secret_key = use_session('config_'.$_SESSION['user_in'].'.txt'.'_secret_key');
							$api_key = use_session('config_'.$_SESSION['user_in'].'.txt'.'_api_key');
							$passphrase = use_session('config_'.$_SESSION['user_in'].'.txt'.'_passphrase');
							$stock_buy = use_session('config_'.$_SESSION['user_in'].'.txt'.'_stock_buy');
							$sell_coin = use_session('config_'.$_SESSION['user_in'].'.txt'.'_sell_coin');
							$price = use_session($_SESSION['user_in'].'.txt'.'_price_ai');
							if(isset($_GET['cache']) && $_GET['cache'] == 'clear'){
								write_session($_SESSION['user_in'].'.txt'.'_date_collect', array());
								write_session($_SESSION['user_in'].'.txt'.'_withdraw_deposit', array());
							}
							if(isset($_GET['balance']) && $_GET['balance'] == 'clear'){
								write_session($_SESSION['user_in'].'.txt'.'_withdraw_deposit', array());
							}
							
							$date_collect = use_session($_SESSION['user_in'].'.txt'.'_date_collect');
							if(empty($date_collect)){
								$date_collect = array();
							}
							if(!isset($_GET['withdraw'])){
								$_GET['withdraw'] = 'off';
							}
							
							if(isset($_GET['currencies']) || isset($_POST['currencies'])){
								if(isset($_POST['currencies'])){
									$currencies = $_POST['currencies'];
								} elseif(isset($_GET['currencies'])){
									$currencies = $_GET['currencies'];
								}
							}
							if(!isset($currencies) || (isset($currencies) && empty($currencies))){
								$currencies = $sell_coin;
							}
							$currencies = explode(',', $currencies);
							
							if(isset($_GET['currencies_full']) || isset($_POST['currencies_full'])){
								if(isset($_POST['currencies_full'])){
									$currencies_full = $_POST['currencies_full'];
								} elseif(isset($_GET['currencies_full'])){
									$currencies_full = $_GET['currencies_full'];
								}
							}
							if(!isset($currencies_full) || (isset($currencies_full) && empty($currencies_full))){
								$currencies_full = $stock_buy;
							}
							$currencies_full = explode(',', $currencies_full);
							
							$data = [];
							$x = 0;
							
							if(isset($_POST['date'])){
								$_GET['time'] = strtotime(date('Y-m-d', strtotime($_POST['date'])));
								$date_post = explode('-', $_POST['date']);
							
								foreach($date_collect as $key2 => $value2){
									if(isset($date_collect[$key2][$date_post[0]]) && !empty($date_collect[$key2][$date_post[0]])){
										ksort($date_collect[$key2][$date_post[0]]);
										foreach($date_collect[$key2][$date_post[0]] as $key1 => $value1){
											$time_next = strtotime("-60 minutes", $_GET['time']);
											if($key1 >= $time_next){
												$_GET['time'] = $key1;
												break;
											}
										}
									}
								}
								
							}
							$year2 = date('Y');
							$year2 = intval($year2) - 1;
							$time_first = strtotime($year2.'-01-01');
							$time_end = strtotime('now');
							if(!isset($_GET['time'])){
								$_GET['time'] = $time_first;
							}
							
							$time = $_GET['time'];
							$time_next = strtotime("+60 minutes", $time);
							$date_format_start = date('Y-m-d\TH:i:s\Z', $time);
							
							/*DEPOSIT HISTORY*/
							/*WITHDRAW HISTORY*/
							if(empty(use_session($_SESSION['user_in'].'.txt'.'_withdraw_deposit'))){
								$array = array();
								if(!empty($currencies)){
									foreach($currencies as $key1 => $value1){
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
										$response = json_decode($response, true);
										$profile_id = 0;
										foreach($response as $key => $value){
											if($value['currency'] == $value1){
												$profile_id = $value['id'];
											}
										}
										
										$ch = curl_init('https://api.exchange.coinbase.com/accounts/'.$profile_id.'/transfers?limit=100&after='.$date_format_start);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
										curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
										$sign_key = base64_encode(hash_hmac('sha256', time().'GET/accounts/'.$profile_id.'/transfers?limit=100&after='.$date_format_start, base64_decode($secret_key), true));
										curl_setopt($ch, CURLOPT_HTTPHEADER, array(
										'ACCEPT: application/json',
										'CB-ACCESS-KEY: '.$api_key,
										'CB-ACCESS-SIGN: '.$sign_key,
										'CB-ACCESS-PASSPHRASE: '.$passphrase,
										'CB-ACCESS-TIMESTAMP: '.time()));
										$response = curl_exec($ch);
										curl_close($ch);
										$response = json_decode($response, true);
										if(isset($response[0]['id'])){
											$array[$value1] = $response;
										}
									}
									write_session($_SESSION['user_in'].'.txt'.'_withdraw_deposit', $array);
								}
							} else {
								$response = use_session($_SESSION['user_in'].'.txt'.'_withdraw_deposit');
							}
							
							if(!empty($response)){
								foreach($response as $index => $value2){
									foreach($value2 as $key => $value){
										if(isset($value['id'])){
											if(!isset($data[$index])){
												$data[$index] = array();
											}
											if(!isset($data[$index][$x])){
												$data[$index][$x] = array();
											}
											$data[$index][$x]['type'] = $value['type'];
											$data[$index][$x]['coin'] = $index;
											$data[$index][$x]['amount'] = abs($value['amount']);
											$data[$index][$x]['time'] = intval(strtotime($value['processed_at']));
											$data[$index][$x]['fees'] = (isset($value['details']['fee']) ? $value['details']['fee'] : 0);
											$data[$index][$x]['id'] = $value['id'];
											$x++;
										}
									}
								}
							}
							
							
							
							/*ALL ORDERS*/
							
							foreach($currencies_full as $key2 => $value2){
								$date_format = date('Y-m-d\TH:i:s\Z', $time);
								$date_format_end = date('Y-m-d\TH:i:s\Z', $time_next);
								
								$ch = curl_init('https://api.exchange.coinbase.com/orders?product_id='.$value2.'&limit=800&status=done&status=rejected&status=active&start_date='.$date_format.'&end_date='.$date_format_end);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_USERAGENT, 'Trading-API');
								$sign_key = base64_encode(hash_hmac('sha256', time().'GET/orders?product_id='.$value2.'&limit=800&status=done&status=rejected&status=active&start_date='.$date_format.'&end_date='.$date_format_end, base64_decode($secret_key), true));
								curl_setopt($ch, CURLOPT_HTTPHEADER, array(
								'ACCEPT: application/json',
								'CB-ACCESS-KEY: '.$api_key,
								'CB-ACCESS-SIGN: '.$sign_key,
								'CB-ACCESS-PASSPHRASE: '.$passphrase,
								'CB-ACCESS-TIMESTAMP: '.time()));
								$response = curl_exec($ch);
								curl_close($ch);
								$all_orders = json_decode($response, true);
								
								if(!empty($all_orders)){
									foreach($all_orders as $key => $value){																				
										$symbol = $currencies[0];
										
										foreach($currencies as $key3 => $value3){
											if(strpos($value2, $value3) === 0){
												$symbol = $value3;
												break;
											}
										}
										
										if(!isset($data[$symbol])){
											$data[$symbol] = array();
										}
										if(!isset($data[$symbol][$x])){
											$data[$symbol][$x] = array();
										}
									
										$data[$symbol][$x]['type'] = 'transaction';
										$data[$symbol][$x]['coin'] = $value2;
										$data[$symbol][$x]['amount'] = $value['size'];
										$data[$symbol][$x]['price'] = $value['price'];
										$data[$symbol][$x]['time'] = intval(strtotime($value['created_at']));
										$data[$symbol][$x]['status'] = $value['status'];
										$data[$symbol][$x]['side'] = $value['side'];
										$data[$symbol][$x]['fees'] = $value['fill_fees'] / $value['price'];
										$data[$symbol][$x]['id'] = $value['id'];
										$x++;
										
									}
								}
							}
							
							function cmp($a, $b){
								return ($a['time'] > $b['time']);
							}
							foreach($data as $key => $value){
								usort($data[$key], "cmp");
							}
							$deposit = [];
							$withdraw = [];
							$filled = [];
							$total = [];
							$account = [];
							$money_in = [];
							$last_price_ratio = [];
							$diff = [];
							$buys = [];
							$sells = [];
							$year = 0;
							$price = [];
							$x = [];
							$y = 0;
							$lines = [];
							$cur_sym = [];
							$coin = [];
							
							foreach($data as $key => $value){
								foreach($value as $key1 => $value1){
									$time_next = strtotime("+60 minutes", $time);
									
									if((intval($time) <= intval($value1['time']) && intval($time_next) >= intval($value1['time'])) || ($_GET['withdraw'] == 'on' && intval($time_next) >= intval($value1['time']))){
										$year = date('Y', $value1['time']);
										$date = date('Y-m-d H:i:s', $value1['time']);
										
										if(($_GET['withdraw'] == 'on' && ($value1['type'] == 'withdraw' || $value1['type'] == 'deposit')) || 
										($_GET['withdraw'] == 'off' && ($value1['type'] == 'transaction' || $value1['type'] == 'withdraw' || $value1['type'] == 'deposit'))){
											if(!isset($total[$key])){
												$total[$key] = array();
											}
											if(!isset($total[$key][$key1])){
												$total[$key][$key1] = array();
											}
											if(!isset($cur_sym[$key])){
												$cur_sym[$key] = array();
											}
											if(!isset($coin[$key])){
												$coin[$key] = array();
											}
											if(!isset($cur_sym[$key][$year])){
												$cur_sym[$key][$year] = 0;
											}
											if(!isset($coin[$key][$year])){
												$coin[$key][$year] = 0;
											}
										}
										
										if(!isset($lines[$key])){
											$lines[$key] = array();
										}
										if(!isset($account[$key])){
											$account[$key] = array();
										}
										if(!isset($filled[$key])){
											$filled[$key] = array();
										}
										if(!isset($withdraw[$key])){
											$withdraw[$key] = array();
										}
										if(!isset($deposit[$key])){
											$deposit[$key] = array();
										}
										if(!isset($diff[$key])){
											$diff[$key] = array();
										}
										if(!isset($last_price_ratio[$key])){
											$last_price_ratio[$key] = array();
										}
										if(!isset($money_in[$key])){
											$money_in[$key] = array();
										}
										if(!isset($buys[$key])){
											$buys[$key] = array();
										}
										if(!isset($sells[$key])){
											$sells[$key] = array();
										}
										if(!isset($price[$key])){
											$price[$key] = array();
										}
										if(!isset($x[$key])){
											$x[$key] = array();
										}
										
										if(!isset($account[$key][$year])){
											$account[$key][$year] = 0;
										}
										if(!isset($filled[$key][$year])){
											$filled[$key][$year] = 0;
										}
										if(!isset($withdraw[$key][$year])){
											$withdraw[$key][$year] = 0;
										}
										if(!isset($deposit[$key][$year])){
											$deposit[$key][$year] = 0;
										}
										if(!isset($diff[$key][$year])){
											$diff[$key][$year] = 0;
										}
										if(!isset($last_price_ratio[$key][$year])){
											$last_price_ratio[$key][$year] = 0;
										}
										if(!isset($money_in[$key][$year])){
											$money_in[$key][$year] = 0;
										}
										if(!isset($buys[$key][$year])){
											$buys[$key][$year] = 0;
										}
										if(!isset($sells[$key][$year])){
											$sells[$key][$year] = 0;
										}
										if(!isset($price[$key][$year])){
											$price[$key][$year] = 0;
										}
										if(!isset($x[$key][$year])){
											$x[$key][$year] = 0;
										}
										
										if($value1['type'] == 'deposit'){
											$fees = $value1['amount'];
											$account[$key][$year] = $account[$key][$year] + $fees;
											$coin[$key][$year] = $coin[$key][$year] + $fees;
											$total[$key][$key1]['total'] = $account[$key][$year];
											$total[$key][$key1]['price'] = 0;
											$total[$key][$key1]['status'] = 0;
											$total[$key][$key1]['diff'] = 0;
											$total[$key][$key1]['coin'] = $value1['coin'];
											$total[$key][$key1]['type'] = $value1['type'];
											$total[$key][$key1]['time'] = $date;
											$total[$key][$key1]['fees'] = number_format($value1['fees'], 6, '.', '');
											$total[$key][$key1]['id'] = $value1['id'];
											$total[$key][$key1]['side'] = 0;
											$deposit[$key][$year]++;
										} elseif ($value1['type'] == 'withdraw'){
											$fees = $value1['amount'] + $value1['fees'];
											$account[$key][$year] = $account[$key][$year] - $fees;
											$coin[$key][$year] = $coin[$key][$year] - $fees;
											$total[$key][$key1]['price'] = 0;
											$total[$key][$key1]['status'] = 0;
											$total[$key][$key1]['total'] = $account[$key][$year];
											$total[$key][$key1]['diff'] = 0;
											$total[$key][$key1]['coin'] = $value1['coin'];
											$total[$key][$key1]['type'] = $value1['type'];
											$total[$key][$key1]['time'] = $date;
											$total[$key][$key1]['fees'] = number_format($value1['fees'], 6, '.', '');
											$total[$key][$key1]['id'] = $value1['id'];
											$total[$key][$key1]['side'] = 0;
											$withdraw[$key][$year]++;
										} elseif($value1['type'] == 'transaction') {
											if($_GET['withdraw'] == 'off'){
												$price[$key][$year] = $price[$key][$year] + $value1['price'];
												$x[$key][$year]++;
												$y++;
												$total[$key][$key1]['side'] = $value1['side'];
												$total[$key][$key1]['price'] = $value1['price'];
												$total[$key][$key1]['total'] = $value1['price'] * $money_in[$key][$year];
												$total[$key][$key1]['status'] = $value1['status'];
												$total[$key][$key1]['fees'] = number_format($value1['fees'], 6, '.', '');
												
												if(strtolower($value1['status']) == 'done'){
													if(strtolower($value1['side']) == 'buy'){
														$coins = ($cur_sym[$key][$year] / $value1['price']) + $value1['amount'];
														$curs = ($coin[$key][$year] * $value1['price']) - ($value1['amount'] * $value1['price']);
														$coin[$key][$year] = $coins;
														$cur_sym[$key][$year] = $curs;
														
														$fees = $value1['amount'];
														$buys[$key][$year] = $buys[$key][$year] + $fees;
														$money_in[$key][$year] = $money_in[$key][$year] + $fees;
														$last_price_ratio[$key][$year] = $money_in[$key][$year] / $value1['price'];
														$diff[$key][$year] = ($fees * $value1['price']);
													} else {
														$coins = ($cur_sym[$key][$year] / $value1['price']) - $value1['amount'];
														$curs = ($coin[$key][$year] * $value1['price']) + ($value1['amount'] * $value1['price']);
														$coin[$key][$year] = $coins;
														$cur_sym[$key][$year] = $curs;
														
														$fees = $value1['amount'] + $value1['fees'];
														$sells[$key][$year] = $sells[$key][$year] + $fees;
														$money_in[$key][$year] = $money_in[$key][$year] - $fees;
														$diff[$key][$year] = (($last_price_ratio[$key][$year] * $value1['price']) - ($value1['amount'] * $value1['price'])) - ($value1['fees'] * $value1['price']);
														$account[$key][$year] = $account[$key][$year] + ($diff[$key][$year] / $value1['price']);
													}
													$filled[$key][$year]++;
												}
												$total[$key][$key1]['diff'] = isset($diff[$key][$year]) ? $diff[$key][$year] : 0;
												$total[$key][$key1]['coin'] = $value1['coin'];
												$total[$key][$key1]['type'] = $value1['type'];
												$total[$key][$key1]['time'] = $date;
												$total[$key][$key1]['id'] = $value1['id'];
											}
										}
										if(isset($total[$key][$key1]['id']) && isset($total[$key][$key1]['type']) && isset($total[$key][$key1]['time']) && isset($total[$key][$key1]['price']) && isset($total[$key][$key1]['total']) && isset($total[$key][$key1]['status']) && isset($total[$key][$key1]['coin']) && isset($total[$key][$key1]['side']) && isset($total[$key][$key1]['diff']) && isset($total[$key][$key1]['fees'])){
											$lines[$key][] = "\"".$total[$key][$key1]['id']."\",\"".$total[$key][$key1]['type']."\",\"".$total[$key][$key1]['time']."\",\"".$total[$key][$key1]['price']."\",\"".$total[$key][$key1]['total']."\",\"".$total[$key][$key1]['status']."\",\"".$total[$key][$key1]['coin']."\",\"".$total[$key][$key1]['side']."\",\"".$total[$key][$key1]['diff']."\",\"".$total[$key][$key1]['fees']."\"";
										}
									}
								}								
							}	
							
							if($_GET['withdraw'] == 'off'){		
								if(isset($coin) && !empty($coin)){
									foreach($coin as $index => $years){
										if(isset($years) && !empty($years)){
											foreach($years as $year => $value){
												$price_temp = 0;
												if(isset($price[$index][$year]) && isset($x[$index][$year]) && $price[$index][$year] != 0 && $x[$index][$year] != 0){								
													$price_temp = ($price[$index][$year] / $x[$index][$year]);
												}
												if(!isset($date_collect[$index])){
													$date_collect[$index]= array();
												}
												if(!isset($date_collect[$index][$year])){
													$date_collect[$index][$year] = array();
												}
												if(!isset($cur_sym[$index])){
													$cur_sym[$index] = array();
												}
												if(!isset($cur_sym[$index][$year])){
													$cur_sym[$index][$year] = 0;
												}
												$date_collect[$index][$year][$time] = array('coin' => $coin[$index][$year], 'cur' => $cur_sym[$index][$year], 'price' => $price_temp);
											}
										}
									}
								}
								write_session($_SESSION['user_in'].'.txt'.'_date_collect', $date_collect);
							}
								
							if(!empty($lines) && $_GET['withdraw'] == 'off'){
								$array_merge = array();
								
								foreach($lines as $key => $value){
									if(!empty($value)){
										foreach($value as $key1 => $value1){
											$array_merge[] = $value1;
										}
									}
								}
								$write = fopen("config/stats/".$_SESSION['user_in']."/".$time.".txt", 'w');
								if(flock($write, LOCK_EX)) {
									fwrite($write, implode("\n", $array_merge));
									fflush($write);
									flock($write, LOCK_UN);
								}
								fclose($write);
							}
							
							if(isset($_GET['profit']) && $_GET['profit'] == 'on'){
								$files_dir = dirToArray("config/stats/".$_SESSION['user_in']);
								$year = date('Y', $time);
								$files = array();
								if(!empty($files_dir)){
									foreach($files_dir as $key => $value){
										if(strpos($value, '.csv') === false){
											$time_string = str_replace(".txt", '', $value);
											$time_year = date('Y', $time_string);
											if($year == $time_year){
												$content = file_get_contents("config/stats/".$_SESSION['user_in']."/".$value);
												$files[] = $content;
											}
										}
									}
								}
							
								$files = implode("\n", $files);
								$files = explode("\n", $files);
								$withdraw1 = array();
								$deposit1 = array();
								foreach($files as $key => $value){
									$line = explode(',', $value);
									$type = str_replace('"', '', $line[1]);
									$date = str_replace('"', '', $line[2]);
									$date = strtotime($date);
									$coin = 0;
									
									if($type == "deposit"){
										$coin = str_replace('"', '', $line[6]);
										$amount = str_replace('"', '', $line[4]);
										
										if(!isset($deposit1[$coin])){
											$deposit1[$coin] = array();
										}
										$deposit1[$coin][$date] = abs(floatval($amount));
									}
									if($type == "withdraw"){
										$coin = str_replace('"', '', $line[6]);
										$amount = str_replace('"', '', $line[4]);
										
										if(!isset($withdraw1[$coin])){
											$withdraw1[$coin] = array();
										}
										$withdraw1[$coin][$date] = abs(floatval($amount));
									}
								}
								
								write_session($_SESSION['user_in'].'.txt'.'_deposit_total_'.$year, $deposit1);
								write_session($_SESSION['user_in'].'.txt'.'_withdraw_total_'.$year, $withdraw1);
							}
							
							
							if(isset($_GET['consolidate']) && $_GET['consolidate'] == 'on'){
								$files = [];
								$files_dir = dirToArray("config/stats/".$_SESSION['user_in']);
								$year = date('Y', $time);
								$files[] = "\"id\",\"type\",\"date\",\"price\",\"total\",\"status\",\"coin\",\"side\",\"difference\",\"fees\"";
								if(!empty($files_dir)){
									foreach($files_dir as $key => $value){
										if(strpos($value, '.csv') === false){
											$time_string = str_replace(".txt", '', $value);
											$time_year = date('Y', $time_string);
											if($year == $time_year){
												$content = file_get_contents("config/stats/".$_SESSION['user_in']."/".$value);
												$files[] = $content;
											}
										}
									}
									
									if(!empty($files)){
										$write = fopen("config/stats/".$_SESSION['user_in']."/".$year.".csv", 'w');
										if(flock($write, LOCK_EX)) {
											fwrite($write, implode("\n", $files));
											fflush($write);
											flock($write, LOCK_UN);
										}
										fclose($write);
									}
								}
							}
						?>
						<div class="alert alert-info" align="left" style="background-color: rgba(41, 171, 224, 0.1); margin-top: 10px;">
						<div class="row" style="display:none;">
						<div class="col-md-6">
						<p>Step 1 - Click the retake button.<br />
						Step 2 - Choose your currencies to export.<br />
						Step 3 - Run the script to process all of the last year transaction (make sure you are on the all transaction tab).
						</p>
						</div>
						<div class="col-md-6">
						<p>Step 4 - Wait until it's finished processed (this can take hours).<br />
						Step 5 - Consolidate the CSV part into one file.<br />
						Step 6 - Download the CSV file and navigate to the current year at 01 January to see last year total.
						</p>
						</div>
						</div>
						<div class="row" style="display:none;">
						<div class="col-md-12">
						<p>Step 7 - Once you are finished processing all files, navigate to the last day of the year and click on Generate X Profit to view the total of transactions without deposit or withdraw.</p>
						</div>
						</div>
						</div>
							<h1 align="center"><?php echo date("Y-m-d H:i:s", $time); ?> - <span class="text-warning time_counter" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Time before all of the stats number refresh."></span></h1>
							<div align="center">
								<?php 
									$year2 = date('Y');
									$year2 = intval($year2) - 1;
									$time_first = strtotime($year2.'-01-01');
								
									$last_key = $time_first;
									if(!empty($date_collect)){
										foreach($date_collect as $index => $value2){
											foreach($value2 as $key => $value){
												if(isset($date_collect[$index][$key])){
													ksort($date_collect[$index][$key]);
													foreach($date_collect[$index][$key] as $key1 => $value1){
														$last_key = $key1;
													}	
												}
											}
										}
									}
								?>
								<a href="?time=<?php echo $last_key; ?>" class="btn btn-warning">Retake</a>
								<a href="#" class="run btn <?php if(isset($_GET['run'])){ echo "btn-success"; } else { echo "btn-warning"; } ?>">Run</a>
								<a href="#" class="stop btn btn-danger">Stop</a>								
								<?php 
									$year2 = date('Y');
									$year2 = intval($year2) - 1;
									$time_first = strtotime($year2.'-01-01');
									
									if(isset($_GET['time'])){
										$time = $_GET['time'];
										$time_next = strtotime("+60 minutes", $time);
										$time_prev = strtotime("-60 minutes", $time);
									}
								?>
								<?php if($time_prev > $time_first){ ?>
								<a href="?time=<?php echo $time_prev; ?>&currencies=<?php echo implode(',', $currencies); ?>&currencies_full=<?php echo implode(',', $currencies_full); ?>&withdraw=<?php echo $_GET['withdraw']; ?>" class="btn btn-warning">Previous</a>
								<?php } ?>
								<?php if($time < $time_end){ ?>
								<a href="?time=<?php echo $time_next; ?>&currencies=<?php echo implode(',', $currencies); ?>&currencies_full=<?php echo implode(',', $currencies_full); ?>&withdraw=<?php echo $_GET['withdraw']; ?>" class="next-time btn btn-warning">Next</a>
								<?php } ?>
								<a href="?time=<?php echo $time; ?>&withdraw=<?php echo $_GET['withdraw']; ?>" class="btn <?php if(!isset($_GET['currencies'])){ echo "btn-success"; } else { echo "btn-warning"; } ?>">Clear Currencies</a>
								<a href="?time=<?php echo $time; ?>&currencies=<?php echo implode(',', $currencies); ?>&currencies_full=<?php echo implode(',', $currencies_full); ?>&withdraw=<?php echo $_GET['withdraw']; ?>&cache=clear" class="btn <?php if(isset($_GET['cache'])){ echo "btn-success"; } else { echo "btn-warning"; } ?>">Clear Cache</a>
								<a href="?time=<?php echo $time; ?>&currencies=<?php echo implode(',', $currencies); ?>&currencies_full=<?php echo implode(',', $currencies_full); ?>&withdraw=<?php echo $_GET['withdraw']; ?>&balance=clear" class="btn <?php if(isset($_GET['balance'])){ echo "btn-success"; } else { echo "btn-warning"; } ?>">Clear Deposits Cache</a>
								<a href="?time=<?php echo $time; ?>&currencies=<?php echo implode(',', $currencies); ?>&currencies_full=<?php echo implode(',', $currencies_full); ?>&withdraw=on" class="btn <?php if(isset($_GET['withdraw']) && $_GET['withdraw'] == 'on'){ echo "btn-success"; } else { echo "btn-warning"; } ?>">Withdraw / Deposit</a>
								<a href="?time=<?php echo $time; ?>&currencies=<?php echo implode(',', $currencies); ?>&currencies_full=<?php echo implode(',', $currencies_full); ?>&withdraw=off" class="btn <?php if(isset($_GET['withdraw']) && $_GET['withdraw'] == 'off'){ echo "btn-success"; } else { echo "btn-warning"; } ?>">All Transactions</a>
								<a href="?time=<?php echo $time; ?>&currencies=<?php echo implode(',', $currencies); ?>&currencies_full=<?php echo implode(',', $currencies_full); ?>&withdraw=off&consolidate=on" class="btn <?php if(isset($_GET['consolidate']) && $_GET['consolidate'] == 'on'){ echo "btn-success"; } else { echo "btn-primary"; } ?>">Consolidate CSV</a>
								<?php 
								$year = date('Y', $time);
								if(file_exists("config/stats/".$_SESSION['user_in']."/".$year.".csv")){ ?>
								<a href="<?php echo "config/stats/".$_SESSION['user_in']."/".$year.".csv"; ?>" target="_blank" class="btn btn-primary">Download CSV</a>
								<?php } ?>
								<a href="?time=<?php echo $time; ?>&currencies=<?php echo implode(',', $currencies); ?>&currencies_full=<?php echo implode(',', $currencies_full); ?>&withdraw=off&profit=on" class="btn <?php if(isset($_GET['profit']) && $_GET['profit'] == 'on'){ echo "btn-success"; } else { echo "btn-primary"; } ?>">Generate <?php echo $year; ?> Profit</a>
								<form action="" method="POST" style="margin: 10px auto; max-width: 800px;">
									<input type="text" name="date" class="form-control" placeholder="E.g.: 2021-01-01 00:00:00" value="<?php echo date("Y-m-d H:i:s", $time); ?>" style="display:inline-block; width: 250px;" />
									<input type="text" name="currencies" class="form-control" placeholder="E.g.: ETH,BTC" value="<?php echo implode(',', $currencies); ?>" style="display:inline-block; width: 250px;" />
									<input type="text" name="currencies_full" class="form-control" placeholder="E.g.: ETH-USDC,ETH-USDT" value="<?php echo implode(',', $currencies_full); ?>" style="display:inline-block; width: 250px;" />
									<p><input type="submit" value="Get date" class="btn btn-success" style="width: auto; height: auto;" /></p>
								</form>
							</div>
						
							<h3>ACCOUNT STATEMENT</h3>
							<div class="row" style="display:none;">
							<div class="col-md-6">
							<div style="background-color: rgba(0,0,0,0.3);" class="box-stat">
							<table class="table stats" cellpadding="5">
							<tr>
								<td width="10%">Year</td>
								<td width="15%">Last Price</td>
								<td width="15%">Balance</td>
								<td width="15%">Coins</td>
								<td width="15%">Total</td>
								<td width="15%">Profit</td>
								<td width="15%">Profit($)</td>
							</tr>
							<?php
								if(isset($date_collect) && !empty($date_collect)){
									foreach($date_collect as $index => $value3){
										$collect_deposit = 0;
										$collect_withdraw = 0;
										$collect_coin = 0;
										$collect_cur = 0;
										$time_next = strtotime("+60 minutes", $time);
										
										foreach($value3 as $key => $value){
											$y = 0;
											$profit_do = 0;
											$price_temp = 0;
											$profit = 0;
											$total2 = 0;
											
											foreach($value as $key2 => $value2){
												if(intval($time_next) > intval($key2)){
													$price_temp = $value2['price'];
													$collect_coin = $collect_coin + floatval($value2['coin']);												
													$collect_cur = $collect_cur + floatval($value2['cur']);												
													$y = $y + floatval($value2['coin']);
												}
											}	
											
											
											$deposit1 = use_session($_SESSION['user_in'].'.txt'.'_deposit_total_'.$key);
											$withdraw1 = use_session($_SESSION['user_in'].'.txt'.'_withdraw_total_'.$key);
											if(isset($deposit1[$index])){
												$deposit1 = $deposit1[$index];
											} else {
												$deposit1 = array();
											}
											if(isset($withdraw1[$index])){
												$withdraw1 = $withdraw1[$index];
											} else {
												$withdraw1 = array();
											}
											
											if(!empty($deposit1)){
												foreach($deposit1 as $key2 => $value2){
													if(intval($time_next) > intval($key2)){
														$collect_deposit = $collect_deposit + abs($value2);
													}
												}	
											}
											
											if(!empty($withdraw1)){
												foreach($withdraw1 as $key2 => $value2){
													if(intval($time_next) > intval($key2)){
														$collect_withdraw = $collect_withdraw + abs($value2);
													}
												}	
											}
											
											if($price_temp != 0){
												$profit = ((($collect_cur / $price_temp) + $collect_coin) - $collect_deposit) + $collect_withdraw;
												$profit_do = $profit * $price_temp;
												$total2 = ($collect_coin * $price_temp) + $collect_cur;
											}
											
											if($y != 0){
								?>	
									<tr>
										<td><?php echo $key; ?></td>
										<td><?php echo number_format($price_temp, 6, '.', ''); ?></td>
										<td class="text-warning"><?php echo number_format($collect_cur, 6, '.', ''); ?></td>
										<td class="text-warning"><?php echo number_format($collect_coin, 6, '.', ''); echo " ". $index; ?></td>
										<td><?php echo number_format($total2, 6, '.', ''); ?></td>
										<td class="<?php if(strval($profit) > 0){ echo "text-success"; }else{ echo "text-danger"; } ?>"><?php echo number_format($profit, 6, '.', ''); echo " ". $index; ?></td>
										<td class="<?php if(strval($profit_do) > 0){ echo "text-success"; }else{ echo "text-danger"; } ?>"><?php echo number_format($profit_do, 6, '.', ''); ?></td>
									</tr>
							<?php
											}
										}
									}
								}
							?>
							</table>
							</div>
							</div>
							<div class="col-md-2">
							<div style="background-color: rgba(0,0,0,0.3);" class="box-stat">
							<table class="table stats" cellpadding="5">
							<tr>
								<td width="33.3333%">Now</td>
								<td width="33.3333%">Filled</td>
								<td width="33.3333%">Coin</td>
							</tr>
							<?php
								if(isset($filled) && !empty($filled)){
									foreach($filled as $index => $value1){
										foreach($value1 as $key => $value){
							?>	
								<tr>
									<td><?php echo $key; ?></td>
									<td><?php echo $value; ?></td>
									<td><?php echo $index; ?></td>
								</tr>
							<?php
										}
									}
								}
							?>
							</table>
							</div>
							</div>
							<div class="col-md-2">
							<div style="background-color: rgba(0,0,0,0.3);" class="box-stat">
							<table class="table stats" cellpadding="5">
							<tr>
								<td width="33.3333%">Now</td>
								<td width="33.3333%">Deposit</td>
								<td width="33.3333%">Coin</td>
							</tr>
							<?php
								if(isset($deposit) && !empty($deposit)){
									foreach($deposit as $index => $value1){
										foreach($value1 as $key => $value){
							?>	
								<tr>
									<td><?php echo $key; ?></td>
									<td><?php echo $value; ?></td>
									<td><?php echo $index; ?></td>
								</tr>
							<?php
										}
									}
								}
							?>
							</table>
							</div>
							</div>
							<div class="col-md-2">
							<div style="background-color: rgba(0,0,0,0.3);" class="box-stat">
							<table class="table stats" cellpadding="5">
							<tr>
								<td width="33.3333%">Now</td>
								<td width="33.3333%">Withdraw</td>
								<td width="33.3333%">Coin</td>
							</tr>
							<?php
								if(isset($withdraw) && !empty($withdraw)){
									foreach($withdraw as $index => $value1){
										foreach($value1 as $key => $value){
							?>	
								<tr>
									<td><?php echo $key; ?></td>
									<td><?php echo $value; ?></td>
									<td><?php echo $index; ?></td>
								</tr>
							<?php
										}
									}
								}
							?>
							</table>
							</div>
							</div>
							</div>
						<?php
							if(isset($total) && !empty($total)){
							foreach($total as $key => $value){
						?>
						<h3><?php echo $key; ?></h3>
						<div style="background-color: rgba(0,0,0,0.3); margin-top: 10px;" class="box-stat">
						<table class="table stats" cellpadding="5">
						<tr>
							<td width="22%">ID</td>
							<td width="8%">Type</td>
							<td width="10%">Time</td>
							<td width="10%">Price</td>
							<td width="10%">Total</td>
							<td width="6%">Status</td>
							<td width="8%">Coin</td>
							<td width="6%">Side</td>
							<td width="10%">Difference</td>
							<td width="10%">Fees</td>
						</tr>
						<?php
							foreach($value as $key1 => $value1){
						?>	
							<tr>
								<td><?php echo $value1['id']; ?></td>
								<td><?php echo $value1['type']; ?></td>
								<td><?php echo $value1['time']; ?></td>
								<td><?php echo number_format($value1['price'], 6, '.', ''); ?></td>
								<td><?php echo number_format($value1['total'], 6, '.', ''); ?></td>
								<td><?php echo $value1['status']; ?></td>
								<td><?php echo $value1['coin']; ?></td>
								<td><?php echo $value1['side']; ?></td>
								<td><?php echo number_format($value1['diff'], 6, '.', ''); ?></td>
								<td><?php echo ($value1['type'] == 'transaction') ? ($value1['fees'] * 100).'%' : $value1['fees']; ?></td>
							</tr>
						<?php
							}
						?>
						</table>
						</div>
						<?php
							}
							}
						?>
					</div>
				</div>
			</div>
			<br />
			<?php include "includes/footer.php"; ?>
			<br />
		<?php } ?>
	</body>
</html>	
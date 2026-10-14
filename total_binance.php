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
							$sell_coin = use_session('config_'.$_SESSION['user_in'].'.txt'.'_sell_coin');
							$stock_buy = use_session('config_'.$_SESSION['user_in'].'.txt'.'_stock_buy');
							
							\Core\Reports\TotalPageRequestService::clearRequestedCaches($_SESSION['user_in'], $_GET, array('deposit', 'withdraw'), array('deposit', 'withdraw'));

							$date_collect = use_session($_SESSION['user_in'] . '.txt' . '_date_collect');
							$request_data = \Core\Reports\TotalPageRequestService::resolve(
								$_GET,
								$_POST,
								$date_collect,
								$sell_coin,
								$stock_buy,
								60
							);
							$currencies = $request_data['currencies'];
							$currencies_full = $request_data['currency_pairs'];
							$_GET['withdraw'] = $request_data['withdraw'];
							$_GET['time'] = $request_data['time'];
							$time_first = $request_data['time_first'];
							$time_end = $request_data['time_end'];
							$time = $request_data['time'];
							$time_next = $request_data['time_next'];
							$data = array();
							$x = 0;
							/*DEPOSIT HISTORY*/
							if(empty(use_session($_SESSION['user_in'].'.txt'.'_deposit'))){
								$post_values = ['timestamp'   => (time() * 1000), 'startTime' => intval($time * 1000)];
								$query = http_build_query($post_values);
								$signature = hash_hmac('sha256', $query, $secret_key);
								$post_values['signature'] = $signature;
								$query = http_build_query($post_values);

								$ch = curl_init('https://api.binance.com/sapi/v1/capital/deposit/hisrec?'.$query);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-MBX-APIKEY: ".$api_key));
								$deposit_history = curl_exec($ch);
								curl_close($ch);
								$deposit_history = json_decode($deposit_history, true);
								if(isset($deposit_history[0]['txId'])){
									write_session($_SESSION['user_in'].'.txt'.'_deposit', $deposit_history);
								}
							} else {
								$deposit_history = use_session($_SESSION['user_in'].'.txt'.'_deposit');
							}
							
							if(!empty($deposit_history)){
								foreach($deposit_history as $key => $value){									
									if(isset($value['coin']) && in_array($value['coin'], $currencies)){
										if(isset($value['txId'])){
											if(!isset($data[$value['coin']])){
												$data[$value['coin']] = array();
											}
											if(!isset($data[$value['coin']][$x])){
												$data[$value['coin']][$x] = array();
											}
											
											$data[$value['coin']][$x]['type'] = 'deposit';
											$data[$value['coin']][$x]['coin'] = $value['coin'];
											$data[$value['coin']][$x]['id'] = $value['txId'];
											$data[$value['coin']][$x]['amount'] = abs($value['amount']);
											$data[$value['coin']][$x]['time'] = intval($value['insertTime'] / 1000);
											$data[$value['coin']][$x]['fees'] = (isset($value['transactionFee'])) ? $value['transactionFee'] : 0;
											$x++;
										}
									}
								}
							}							
							
							/*WITHDRAW HISTORY*/
							if(empty(use_session($_SESSION['user_in'].'.txt'.'_withdraw'))){
								$post_values = ['timestamp'   => (time() * 1000), 'startTime' => intval($time * 1000)];
								$query = http_build_query($post_values);
								$signature = hash_hmac('sha256', $query, $secret_key);
								$post_values['signature'] = $signature;
								$query = http_build_query($post_values);

								$ch = curl_init('https://api.binance.com/sapi/v1/capital/withdraw/history?'.$query);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-MBX-APIKEY: ".$api_key));
								$withdraw_history = curl_exec($ch);
								curl_close($ch);
								$withdraw_history = json_decode($withdraw_history, true);
								if(isset($withdraw_history[0]['txId'])){
									write_session($_SESSION['user_in'].'.txt'.'_withdraw', $withdraw_history);
								}
							} else {
								$withdraw_history = use_session($_SESSION['user_in'].'.txt'.'_withdraw');
							}
							
							if(!empty($withdraw_history)){
								foreach($withdraw_history as $key => $value){
									if(isset($value['coin']) && in_array($value['coin'], $currencies)){
										if(isset($value['txId'])){
											if(!isset($data[$value['coin']])){
												$data[$value['coin']] = array();
											}
											if(!isset($data[$value['coin']][$x])){
												$data[$value['coin']][$x] = array();
											}
											
											$data[$value['coin']][$x]['type'] = 'withdraw';
											$data[$value['coin']][$x]['coin'] = $value['coin'];
											$data[$value['coin']][$x]['id'] = $value['txId'];
											$data[$value['coin']][$x]['amount'] = abs($value['amount']);
											$data[$value['coin']][$x]['time'] = strtotime($value['applyTime']);
											$data[$value['coin']][$x]['fees'] = number_format(((isset($value['transactionFee'])) ? $value['transactionFee'] : 0), 6, '.', '');
											$x++;
										}
									}
								}
							}							
							
							
							
							/*ALL ORDERS*/
							
							foreach($currencies_full as $pair_index => $currency_pair){
								$time_next = strtotime("+60 minutes", $time);
								$post_values = ['timestamp'   => (time() * 1000), 'symbol' => $currency_pair, 'limit' => 800, 'startTime' => intval($time * 1000), 'endTime' => intval($time_next * 1000)];
								$query = http_build_query($post_values);
								$signature = hash_hmac('sha256', $query, $secret_key);
								$post_values['signature'] = $signature;
								$query = http_build_query($post_values);

								$ch = curl_init('https://api.binance.com/api/v3/myTrades?'.$query);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-MBX-APIKEY: ".$api_key));
								$all_orders = curl_exec($ch);
								curl_close($ch);
								$all_orders = json_decode($all_orders, true);
								
								if(!empty($all_orders)){
									foreach($all_orders as $key => $value){	
										if(isset($value['orderId'])){
											$symbol = $currencies[0];
											
											foreach($currencies as $currency_index => $currency){
												if(strpos($currency_pair, $currency) === 0){
													$symbol = $currency;
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
											$data[$symbol][$x]['coin'] = $value['symbol'];
											$data[$symbol][$x]['fees'] = $value['commission'] / $value['price'];
											$data[$symbol][$x]['id'] = $value['orderId'];
											$data[$symbol][$x]['amount'] = $value['qty'];
											$data[$symbol][$x]['price'] = $value['price'];
											$data[$symbol][$x]['time'] = intval($value['time'] / 1000);
											$data[$symbol][$x]['status'] = 'FILLED';
											$data[$symbol][$x]['side'] = ($value['isBuyer']) ? 'BUY' : 'SELL';
											$x++;
										}
									}
								}
							}
							
							$exchange_totals = \Core\Reports\ExchangeTotalService::build($data, $date_collect, $time, 60, $_GET['withdraw'], 'filled');
							$date_collect = $exchange_totals['date_collect'];
							$deposit = $exchange_totals['deposit'];
							$withdraw = $exchange_totals['withdraw'];
							$filled = $exchange_totals['filled'];
							$total = $exchange_totals['total'];
							$account = $exchange_totals['account'];
							$money_in = $exchange_totals['money_in'];
							$last_price_ratio = $exchange_totals['last_price_ratio'];
							$diff = $exchange_totals['diff'];
							$buys = $exchange_totals['buys'];
							$sells = $exchange_totals['sells'];
							$year = $exchange_totals['year'];
							$price = $exchange_totals['price'];
							$x = $exchange_totals['x'];
							$y = $exchange_totals['y'];
							$lines = $exchange_totals['lines'];
							$cur_sym = $exchange_totals['cur_sym'];
							$coin = $exchange_totals['coin'];
							$stats_directory = 'config/stats/' . $_SESSION['user_in'];
							\Core\Reports\ExchangeStatsService::persistCollection(
								$_SESSION['user_in'],
								$date_collect,
								$time,
								$_GET['withdraw'],
								$lines,
								$stats_directory
							);
							\Core\Reports\ExchangeStatsService::processRequestedActions(
								$_SESSION['user_in'],
								$_GET,
								$stats_directory,
								$time
							);
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
									$previous_year = date('Y');
									$previous_year = intval($previous_year) - 1;
									$time_first = strtotime($previous_year.'-01-01');
								
									$last_key = \Core\Reports\ExchangeStatsService::getLastCollectedTimestamp($date_collect, $time_first);
								?>
								<a href="?time=<?php echo $last_key; ?>" class="btn btn-warning">Retake</a>
								<a href="#" class="run btn <?php if(isset($_GET['run'])){ echo "btn-success"; } else { echo "btn-warning"; } ?>">Run</a>
								<a href="#" class="stop btn btn-danger">Stop</a>								
								<?php 
									$previous_year = date('Y');
									$previous_year = intval($previous_year) - 1;
									$time_first = strtotime($previous_year.'-01-01');
									
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
									<input type="text" name="currencies_full" class="form-control" placeholder="E.g.: ETHBUSD,ETHUSDT" value="<?php echo implode(',', $currencies_full); ?>" style="display:inline-block; width: 250px;" />
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
								$account_statement = \Core\Reports\AccountStatementService::build(
									$_SESSION['user_in'],
									$date_collect,
									$time
								);
								foreach($account_statement as $statement_row){
								?>	
									<tr>
										<td><?php echo $statement_row['year']; ?></td>
										<td><?php echo number_format($statement_row['price'], 6, '.', ''); ?></td>
										<td class="text-warning"><?php echo number_format($statement_row['balance'], 6, '.', ''); ?></td>
										<td class="text-warning"><?php echo number_format($statement_row['coins'], 6, '.', ''); echo " ". $statement_row['coin']; ?></td>
										<td><?php echo number_format($statement_row['total'], 6, '.', ''); ?></td>
										<td class="<?php if(strval($statement_row['profit']) > 0){ echo "text-success"; }else{ echo "text-danger"; } ?>"><?php echo number_format($statement_row['profit'], 6, '.', ''); echo " ". $statement_row['coin']; ?></td>
										<td class="<?php if(strval($statement_row['profit_value']) > 0){ echo "text-success"; }else{ echo "text-danger"; } ?>"><?php echo number_format($statement_row['profit_value'], 6, '.', ''); ?></td>
									</tr>
							<?php
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
									foreach($filled as $index => $year_totals){
										foreach($year_totals as $key => $value){
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
									foreach($deposit as $index => $year_totals){
										foreach($year_totals as $key => $value){
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
									foreach($withdraw as $index => $year_totals){
										foreach($year_totals as $key => $value){
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
							<td width="14%">ID</td>
							<td width="11%">Type</td>
							<td width="13%">Time</td>
							<td width="10%">Price</td>
							<td width="10%">Total</td>
							<td width="8%">Status</td>
							<td width="8%">Coin</td>
							<td width="6%">Side</td>
							<td width="10%">Difference</td>
							<td width="10%">Fees</td>
						</tr>
						<?php
							foreach($value as $transaction_index => $transaction){
						?>	
							<tr>
								<td><?php echo $transaction['id']; ?></td>
								<td><?php echo $transaction['type']; ?></td>
								<td><?php echo $transaction['time']; ?></td>
								<td><?php echo number_format($transaction['price'], 6, '.', ''); ?></td>
								<td><?php echo number_format($transaction['total'], 6, '.', ''); ?></td>
								<td><?php echo $transaction['status']; ?></td>
								<td><?php echo $transaction['coin']; ?></td>
								<td><?php echo $transaction['side']; ?></td>
								<td><?php echo number_format($transaction['diff'], 6, '.', ''); ?></td>
								<td><?php echo ($transaction['type'] == 'transaction') ? ($transaction['fees'] * 100).'%' : $transaction['fees']; ?></td>
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
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
	ini_set('display_errors', 1);
?><!DOCTYPE html>
<html>
	<head>
		<title>User Report - Graph</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<link rel="stylesheet" href="assets/bootstrap.min.css" />
		<script src="assets/jquery-3.6.0.min.js"></script>
		<link rel="stylesheet" href="assets/charts.min.css">
		<?php include "includes/header.php"; ?>
	</head>
	<body id="particles-js">
		<?php if (isset($_SESSION['logged_in'])){ ?>
		<div class="content-canvas">
			<div class="container-fluid">
				<div class="row" style="display:none;">
					<div class="col-md-12">
						<h4 align="center" style="text-transform: uppercase; margin-top: 20px;" class="text-success">COINBASE USER REPORT</h4>
						<?php
						if (isset($_SESSION['user_in']) && use_session('config_' . $_SESSION['user_in'] . '.txt_api_key') != '') {
							$current_date = isset($_GET['day']) ? $_GET['day'] : date('Y-m-d');
							$report_data = \Core\Reports\ReportPageService::buildDashboard($_SESSION['user_in'], $current_date, 'id');
							$today_money = $report_data['today_money'];
							$transactions = $report_data['transactions'];
							$difference = $report_data['difference'];
							$total_money_logs = $report_data['total_money_logs'];
							$median_money = $report_data['median_money'];
							$percent = $report_data['percent'];
							$highest = $report_data['highest'];
							$lowest = $report_data['lowest'];
							$files = $report_data['files'];
							$report_days = $report_data['days'];
						?>
							<div style="text-align: center" class="main">
								<h3 class="item_dashboard text-warning price" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The price returned each amount of second before refreshing."></h3>
								<h3 class="item_dashboard text-warning range_stagnant" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The short RSI returned each amount of second before refreshing. Calculated on a period of 5 seconds / 2.5 minutes"></h3>
								<h3 class="item_dashboard text-warning range_stagnant_long" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The long RSI returned each amount of second before refreshing. Calculated on a period of 5 seconds / 10 minutes"></h3>
								<h3 class="item_dashboard text-warning range_stagnant_extra_long" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The extra long RSI returned each amount of second before refreshing. Calculated on a period of 2 seconds / 12 hours"></h3>
								<h3 class="item_dashboard text-warning server_time" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The microseconds took on the price request multiplied by 100 to give server time indicator"></h3>
								<h3 class="item_dashboard text-warning time_lowest_limit" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Time since the last low sell limit was reached. Will return 0H00 if is above the low sell limit."></h3>
								<h3 class="item_dashboard text-warning time_counter" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Time before all of the stats number refresh."></h3>
							</div>
							
							<table width="100%" class="table dashboard">
								<tr>
									<td align="center" class="anti-spy">
										<h5 class="item_dashboard text-info total" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The total amount of money you have in your account combining both the currency of the trading pair.">ACCOUNT: <?php echo $today_money[1]; ?>$</h5>
										<h5 class="item_dashboard text-success price_sell" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The amount in dollars for the crypto coin you are collecting."></h5>
										<h5 class="item_dashboard text-danger price_buy" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The total amount of dollars you have excluding the crypto coins"></h5>
									</td>									
									<td align="center">
										<h5 class="item_dashboard text-warning range_today" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The difference between the highest and lowest price of the day.">TODAY'S RANGE: <?php echo (floatval($highest) - floatval($lowest)); ?>$</h5>
										<h5 class="item_dashboard text-success highest" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The highest price of the day calculated from actual time">TODAY'S HIGHEST: <?php echo floatval($highest); ?>$</h5>
										<h5 class="item_dashboard text-danger lowest" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The lowest price of the day calculated from actual time">TODAY'S LOWEST: <?php echo floatval($lowest); ?>$</h5>
									</td>
									<td align="center" class="anti-spy">
										<h5 class="item_dashboard text-warning estimation" data-container="body" data-toggle="popover" data-placement="bottom" data-content="An estimation of what your coin total would be when it cross the high sell limit.">EST.: 0$</h5>
										<h5 class="item_dashboard text-success limit_high" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The high sell limit used in bullish mode. When lock is enabled, will sell all the coins when crossing the limit and default to detect lowest mode. When lock is not enabled it will not turn to detect lowest. If the sell time is activated, will sell all coins if superior to 0 and after the X amount of seconds for the sell all delay.">HIGH SELL LIMIT: 0$</h5>
										<h5 class="item_dashboard text-danger limit_low" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The low sell limit used in bullish or experimental mode. When lock is enabled, will sell all the coins when crossing the limit and default to detect lowest mode. When lock is not enabled it will not turn to detect lowest. If sell all delay is set to 600 seconds, will wait 600 seconds before starting the sell and restart timer each time it crossed back over.">LOW SELL LIMIT: 0$</h5>
									</td>
									<td align="center">
										<h5 class="item_dashboard text-warning low_detect" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Usually used when the high or low sell limit is enabled and the sell all period is over falling in lock mode. If detect lowest is enabled, will fall back into price detection and wait the amount of time set in seconds before reactivating the buy transaction. When the price goes below the price detection before the amount of time set, will reset the counter to the full time in seconds.">PRICE DETECTION: 0$</h5>
										<h5 class="item_dashboard text-danger price_range" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The range between two price separated by the seconds interval set in the settings."></h5>
										<h5 class="item_dashboard text-danger stagnant" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Based on the short RSI length. If outside of the top or bottom values will return not stagnant and inside will return positive.">STAGNANT PRICE: NO</h5>
									</td>
									<td align="center" class="anti-spy">
										<h5 class="item_dashboard <?php if(strval($difference) < '0'){ ?>text-danger<?php }else{ ?>text-success<?php } ?> difference" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Total of money you spend and calculated from the acount money. Set the total of money you begin with in the settings">TOTAL FROM START: <?php echo $difference ?>$</h5>
										<h5 class="item_dashboard <?php if(strval($total_money_logs) > '0'){ ?>text-success<?php } else { ?>text-danger<?php } ?> in_logs" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Total of money calculated from all the logs files generated from this app">LOGS TOTAL: <?php echo $total_money_logs; ?>$</h5>
										<h5 class="item_dashboard <?php if(strval($median_money) < '0'){ ?>text-danger<?php }else{ ?>text-success<?php } ?> total_median" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Median based on the total of money from all the logs files generated from this app">LOGS MEDIAN: <?php echo $median_money; ?>$</h5>
									</td>
									<td align="center" class="anti-spy">
										<h5 class="item_dashboard text-warning sold_at" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The last moment it went into a sell all period triggered by the high sell limit or low sell limit">LAST SOLD AT: 0$</h5>			
										<h5 class="item_dashboard <?php if(strval($today_money[0]) < '0'){ ?>text-danger<?php }else{ ?>text-success<?php } ?> total_today" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The total of money gain for today's for the moment">TODAY'S TOTAL: <?php echo $today_money[0]; ?>$</h5>
										<h5 class="item_dashboard <?php if(strval($percent) < '0'){ ?>text-danger<?php }else{ ?>text-success<?php } ?> percentage" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The total percentage of money gain based on the current total account money">TODAY'S PERCENT: <?php echo $percent; ?>%</h5>
									</td>
									<td align="center" class="anti-spy">
										<h5 class="item_dashboard text-warning transaction_total" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The number of transactions made today for all mode.">TRANSACTION #:<?php echo $transactions[0]; ?></h5>	
										<h5 class="item_dashboard text-success transaction_fee_gain" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The total of money gain on all successful experimental transaction">EXPERIMENTAL GAIN:<?php echo $transactions[1]; ?></h5>	
										<h5 class="item_dashboard text-danger transaction_fee_lost" data-container="body" data-toggle="popover" data-placement="bottom" data-content="The total of money lost on all failed experimental transaction">EXPERIMENTAL LOSS:<?php echo $transactions[2]; ?></h5>	
									</td>
									<td class="view-33"></td>
									<td class="view-33"></td>
								</tr>
							</table>
							
							<div style="text-align: center">
								<h5 style="text-transform: uppercase; display:inline-block; margin: 5px 5px;" class="text-success possible-rsi" data-container="body" data-toggle="popover" data-placement="bottom" data-content="If the long RSI is below the bottom limit, will allow the buying."></h5>
								<h5 style="text-transform: uppercase; display:inline-block; margin: 5px 5px;" class="text-success possible-price" data-container="body" data-toggle="popover" data-placement="bottom" data-content="If the price is above the low sell limit will allow transactions."></h5>
								<h5 style="text-transform: uppercase; display:inline-block; margin: 5px 5px;" class="text-success possible-level" data-container="body" data-toggle="popover" data-placement="bottom" data-content="If the price is under the top level limit percentage set will allow buying. The top level limit is a percentage based on the highest and lowest price of the day."></h5>
								<h5 style="text-transform: uppercase; display:inline-block; margin: 5px 5px;" class="text-success possible-time" data-container="body" data-toggle="popover" data-placement="bottom" data-content="If the time operate feature is enabled, it will operate between the time limits set in the settings."></h5>
								<h5 style="text-transform: uppercase; display:inline-block; margin: 5px 5px;" class="text-success possible-money" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Will only allow buying the coin if there is enough money in the currency associated with the coin."></h5>
							</div>
							<div>
								<div style="text-transform: uppercase; margin: 5px 5px;" class="anti-spy price_array"></div>							
							</div>
							<div>
								<div style="text-transform: uppercase; margin: 5px 5px; text-align: center;" class="transaction_anchors"></div>							
							</div>
							<div class="days history-hide" style="overflow: hidden;">
								<?php $files = $report_data['files']; ?>
								<div align="center" class="day-container" style="margin: 0px 0px; width: 100%; overflow-x: scroll; white-space: nowrap; padding: 10px 0px;">
									<?php
										$i = 0;
										foreach($files as $key => $file){
											if(isset($_SESSION['user_in']) && strpos($file, $_SESSION['user_in']) !== false){
												$day = substr($file, 0, 10);
												?>
													<button class="day btn <?php if($current_date == $day){ ?>btn-success<?php } else { ?>btn-primary<?php } ?>" day="<?php echo $day ?>" id="<?php echo $day ?>" position="<?php echo ($i * 76.95) ?>"><?php echo $day ?></button>
												<?php
												$i++;
											}
										}
									?>
								</div>
							</div>
						<?php } ?>
						<div style="overflow-y: scroll; height: 500px; margin: 20px 0px; background-color: rgba(0,0,0,0.3);">
						<?php if(isset($_SESSION['user_in']) && isset($current_date)){  ?>
						<table class="table report_table" cellpadding="10">
						<tbody>
						<?php
							$report_entries = \Core\Reports\ReportLogService::getEntries(
								$_SESSION['user_in'],
								$current_date,
								'id',
								use_session('config_'.$_SESSION['user_in'].'.txt_hide') == 'show'
							);
							foreach ($report_entries as $entry) {
								$line_date = $entry['date_parts'];
								if (isset($line_date[1])) {
									$line_date[1] = '<span class="text-info">' . $line_date[1] . '</span>';
								}
								$line_data = array_pad($entry['data_parts'], 10, '');
								if ($entry['is_sentiment']) {
							?>
							<tr>
								<td width="10%"><p><?php echo implode(' <br />', $line_date); ?></p></td>
								<td width="84%" colspan="8"><p class="text-balance"><?php echo $entry['message']; ?></p></td>
								<td width="6%"><p class="text-warning">Amount: <br /> <?php echo $line_data[4]; ?></p></td>
							</tr>
							<?php } else { ?>
							<tr>
								<td width="10%"><p><?php echo implode(' <br />', $line_date); ?></p></td>
								<td width="34%"><p class="<?php echo $entry['message_class']; ?><?php if ($entry['is_transaction']) { ?> transaction_exe<?php } ?>" id="<?php if ($entry['is_transaction']) { echo 'transaction_exe' . $entry['transaction_count']; } ?>"><?php echo $entry['message']; ?></p></td>
								<td width="8%"><p class="<?php if ($line_data[6] == 'past ratio') { if ($line_data[3] == 'buy') { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Past Ratio (1st): <br /><?php echo $line_data[5]; ?></p></td>
								<td width="8%"><p class="<?php if ($line_data[6] == 'past median' && $line_data[8] == 'extra_long') { if ($line_data[3] == 'buy') { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Past Range (2nd): <br /><?php if ($line_data[2] != '0') { $break = explode('*', $line_data[2]); echo $break[2]; } ?></p></td>
								<td width="8%"><p class="<?php if ($line_data[6] == 'past median' && $line_data[8] == 'long') { if ($line_data[3] == 'buy') { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Past Range (3rd): <br /><?php if ($line_data[2] != '0') { $break = explode('*', $line_data[2]); echo $break[1]; } ?></p></td>
								<td width="8%"><p class="<?php if ($line_data[6] == 'past median' && $line_data[8] == 'short') { if ($line_data[3] == 'buy') { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Past Range (4th): <br /><?php if ($line_data[2] != '0') { $break = explode('*', $line_data[2]); echo $break[0]; } ?></p></td>
								<td width="8%"><p class="<?php if ($line_data[6] == 'future median') { if ($line_data[3] == 'buy') { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Future Med (5th): <br /><?php echo $line_data[1]; ?></p></td>
								<td width="8%"><p>RSI Long (6th): <br /><?php echo $entry['rsi']; ?></p></td>
								<td width="2%"><p class="<?php if ($line_data[3] == 'buy') { ?>text-success<?php } elseif ($line_data[3] == 'sell') { ?>text-danger<?php } ?>">Action: <br /><?php echo !empty($line_data[3]) ? $line_data[3] : '0'; ?></p></td>
								<td width="6%"><p class="text-warning">Amount: <br /> <?php echo $line_data[4]; ?></p></td>
							</tr>
							<?php }
							}
						?>
						</tbody>
						</table>
						<?php } else { ?>
							<p style="margin: 10px;">You need to log in with your username to access this page <a href="user_coinbase.php">here</a></p>
						<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<?php if(isset($_SESSION['user_in'])){ ?>
				<div style="text-align: center">
					<p><button class="detect_lowest off btn btn-success">TURN OFF DETECT LOWEST</button> <button class="sell_unlock minus-20 btn btn-success">SET PRICE UNDER (-20%)</button> <button class="sell_unlock minus-10 btn btn-success">SET PRICE UNDER (-10%)</button> <button class="sell_unlock btn btn-success">SET PRICE UNDER</button> <button class="sell_all btn btn-danger">SET PRICE OVER</button> <button class="sell_all plus-10 btn btn-danger">SET PRICE OVER (+10%)</button> <button class="sell_all plus-20 btn btn-danger">SET PRICE OVER (+20%)</button> <button class="detect_lowest on btn btn-danger">TURN ON DETECT LOWEST</button></p>
				</div>
			<?php } ?>
			<?php include "includes/footer.php"; ?>
			<?php if(isset($_SESSION['user_in'])){ ?>
			<p align="center" style="margin: 10px 0px;">
				<form action="json_request/hide.php" method="post" style="text-align:center;">
					<input type="hidden" value="reveal" name="type" class="type" />
					<input type="hidden" name="crypto" value="<?php echo $_SESSION['user_in'] ?>" />
					<button data-type="reveal" type="submit" class="reveal_hide quickUpdate btn btn-warning">SECRET TOGGLE</button>
					<button data-type="history" type="submit" class="toggle_days quickUpdate btn btn-warning">HISTORY TOGGLE</button>
					<button class="scroll_top btn btn-success">SCROLL TO TOP</button>
				</form>
			</p>
			<?php } ?>
		</div>
		<script src="includes/particles.js-master/particles.js"></script>
		<script src="includes/particles.js-master/demo/js/app.js"></script>
		<?php } ?>
	</body>
</html>	
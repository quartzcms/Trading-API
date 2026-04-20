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
						if(isset($_SESSION['user_in']) && use_session('config_'.$_SESSION['user_in'].'.txt'.'_api_key') != ''){						
							function get_total($file, $total_money_withdrew, $total_money_deposited, $filename){
								if(file_exists('config/logs/'.$_SESSION['user_in'].'/'.$file)){
									$get_total = file_get_contents('config/logs/'.$_SESSION['user_in'].'/'.$file);
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
										
										$get_total = floatval($last[1]) - floatval($first[1]);
										$get_total_difference = 0;
										$filename = str_replace('-', '', $filename);
										
										foreach($total_money_withdrew as $key => $value){
											$parts = explode(':', $value);
											$parts_date = explode('-', $parts[0]);
											if(isset($parts_date[2]) && strlen($parts_date[2]) == 4){
												$parts[0] = $parts_date[2].'-'.$parts_date[1].'-'.$parts_date[0];
											}
											$parts[0] = str_replace('-', '', $parts[0]);
											if(intval($parts[0]) == intval($filename)) {
												$get_total_difference = $get_total_difference - floatval($parts[1]);
											}
										}
										
										foreach($total_money_deposited as $key => $value){
											$parts = explode(':', $value);
											$parts_date = explode('-', $parts[0]);
											if(isset($parts_date[2]) && strlen($parts_date[2]) == 4){
												$parts[0] = $parts_date[2].'-'.$parts_date[1].'-'.$parts_date[0];
											}
											$parts[0] = str_replace('-', '', $parts[0]);
											if(intval($parts[0]) == intval($filename)) {
												$get_total_difference = $get_total_difference + floatval($parts[1]);
											}
										}
										
										$get_total =  number_format($get_total, 3, '.', '');
										return array($get_total, $last[1], $get_total_difference);
									}
								}
								return array(0, 0, 0);
							}
							
							function get_transactions($file){
								$count = 0;
								$gain = 0;
								$loss = 0;
								if(file_exists('config/logs/'.$_SESSION['user_in'].'/'.$file)){
									$get_transaction = file_get_contents('config/logs/'.$_SESSION['user_in'].'/'.$file);
									if(!empty($get_transaction)){
										$lines = explode("\n", $get_transaction);
										$already_picked = array();
										foreach($lines as $key => $value){
											if(strpos($value, "\"id\"") !== false){
												$count++;
											}
											if(strpos($value, "This transaction is related to the experimental feature.") !== false){
												$string = explode("=", $value);
												$string = explode("|", $string[1]);
												if(!in_array($string[0], $already_picked)){
													$already_picked[] = $string[0];
													$array_convert = json_decode($string[0], true);
													if(isset($array_convert['result'])){
														$count++;
														$total_transaction = explode(':', $array_convert['result']);
														$total_transaction = str_replace(' ', '', $total_transaction[1]);
														$total_transaction = str_replace('+', '', $total_transaction);
														$total_transaction = floatval($total_transaction);
														
														if(strval($total_transaction) > '0'){
															$gain = $gain + $total_transaction;
														} else {
															$loss = $loss + $total_transaction;
														}
													}
												}
											}
										}
									}
								}
								return array($count, $gain, $loss);
							}
							
							function get_highest($file){
								if(file_exists('config/logs/'.$_SESSION['user_in'].'/'.$file)){
									$get_highest = file_get_contents('config/logs/'.$_SESSION['user_in'].'/'.$file);
									$lines = explode("\n", $get_highest);
									$highest = 0;
									
									foreach($lines as $key => $value){
										if(!empty($value)){
											$first_part = explode('=', $lines[$key]);
											if(isset($first_part[0])){
												$first_part = explode(' ', $first_part[0]);
												if(isset($first_part[2])){
													$first_part = explode('*', $first_part[2]);

													if($key == 0){
														$highest = $first_part[1];
													} else {
														if(isset($first_part[1]) && strval($first_part[1]) > strval($highest)){
															$highest = $first_part[1];
														}
													}
												}
											}
										}
									}
									return $highest;
								}
								return 0;
							}
							
							function get_lowest($file){
								if(file_exists('config/logs/'.$_SESSION['user_in'].'/'.$file)){	
									$get_lowest = file_get_contents('config/logs/'.$_SESSION['user_in'].'/'.$file);
									$lines = explode("\n", $get_lowest);
									$lowest = 0;
									
									foreach($lines as $key => $value){
										if(!empty($value)){
											$first_part = explode('=', $lines[$key]);
											if(isset($first_part[0])){
												$first_part = explode(' ', $first_part[0]);
												if(isset($first_part[2])){
													$first_part = explode('*', $first_part[2]);
													
													if($key == 0){
														$lowest = $first_part[1];
													} else {
														if(isset($first_part[1]) && strval($first_part[1]) < strval($lowest)){
															$lowest = $first_part[1];
														}
													}
												}
											}
										}
									}
									return $lowest;
								}
								return 0;
							}
							
							if(!isset($_GET['day'])){
								$current_date = date('Y-m-d');
							} else {
								$current_date = $_GET['day'];
							}
							
							$total_money_withdrew = use_session('config_'.$_SESSION['user_in'].'.txt'.'_total_money_withdrew');
							$total_money_deposited = use_session('config_'.$_SESSION['user_in'].'.txt'.'_total_money_deposited');
							
							$total_money_withdrew = explode(',', $total_money_withdrew);
							$total_money_deposited = explode(',', $total_money_deposited);
							
							$today_money = get_total($current_date.'_'.$_SESSION['user_in'].'.txt', $total_money_withdrew, $total_money_deposited, $current_date);
							$transactions = get_transactions($current_date.'_'.$_SESSION['user_in'].'.txt');
							
							$array_money_today = array();
							$files = dirToArray('config/logs/'.$_SESSION['user_in']);
							foreach($files as $key => $file){
								$filename = explode('_', $file);
								$filename2 = str_replace('-', '', $filename[0]);
								$filename = intval($filename2);
								$filename_today = str_replace('-', '', $current_date);
								$filename_today = intval($filename_today);
								if($filename <= $filename_today){
									if(strpos($file, $_SESSION['user_in']) !== false){
										$get_total = get_total($file, $total_money_withdrew, $total_money_deposited, $filename2);
										$array_money_today[] = array('amount' => floatval($get_total[0]), 'deposit_withdraw' => floatval($get_total[2]));
									}
								}
							}
							
							$total_money_logs = 0;
							$total_deposit_withdraw = 0;
							foreach($array_money_today as $key => $value){
								$total_money_logs = floatval($total_money_logs) + floatval($value['amount']);
								$total_deposit_withdraw = floatval($total_deposit_withdraw) + floatval($value['deposit_withdraw']);
							}
							
							$difference = (-(floatval(use_session('config_'.$_SESSION['user_in'].'.txt'.'_total_money'))) - $total_deposit_withdraw) + $total_money_logs;
							$difference = number_format($difference, 2, '.', '');
							
							$total_money_logs = $total_money_logs - ($total_deposit_withdraw);
							$total_money_logs = number_format($total_money_logs, 2, '.', '');
							
							$today_money[0] = $today_money[0] - $today_money[2];
							$today_money[0] = number_format($today_money[0], 2, '.', '');
							
							$median_money = number_format(($total_money_logs / count($files)), 2, '.', '');
							
							$percent = ($today_money[1] > 0) ? number_format((floatval($today_money[0]) / floatval($today_money[1])) * 100, 2, '.', '') : 0;
							$highest = get_highest($current_date.'_'.$_SESSION['user_in'].'.txt');
							$lowest = get_lowest($current_date.'_'.$_SESSION['user_in'].'.txt');
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
								<?php $files = dirToArray('config/logs/'.$_SESSION['user_in']); ?>
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
							function convert_array($json_data) {
								$merge_key_values = array();
								foreach($json_data as $key => $value){
									if(!empty($value)){
										if(isset($json_data[$key]) && !is_array($value)){
											$merge_key_values[] = $key.':'.$value;
										} else {
											$merge_key_values[] = convert_array($value);
										}
									}
								} 
								return implode(', ', $merge_key_values);
							}							
						
							$path = 'config/logs/'.$_SESSION['user_in'].'/'.$current_date.'_'.$_SESSION['user_in'].'.txt';
							if(file_exists($path)){						
								$file_content = file_get_contents($path);
								$lines = explode("\n", $file_content);
								arsort($lines);
								$i = 0;
								$hide_reveal = use_session('config_'.$_SESSION['user_in'].'.txt_hide');
								$transaction_count = 0;
								foreach($lines as $line){
									if(!empty($line)){
										$line_parts = explode('=', $line);
										$line_date = explode('*', $line_parts[0]);
										if(isset($line_date[1])){
											$line_date[1] = '<span class="text-info">'.$line_date[1].'</span>';
										}
										$line_data = explode('|', $line_parts[1]);
										$json_data = json_decode($line_data[0], true);
										if(!empty($json_data)){
											if(strpos($line_data[0], 'Sentiment') !== false){
												foreach($json_data as $key0 => $value0){
													$json_data[$key0] = htmlspecialchars_decode($json_data[$key0], ENT_QUOTES);
													$json_data[$key0] = str_replace('88888888888888888888888888888888888', '=', $json_data[$key0]);
													$json_data[$key0] = str_replace('999999999999999999999999999', '"', $json_data[$key0]);
													$json_data[$key0] = str_replace('((^))', '=', $json_data[$key0]);
													$json_data[$key0] = str_replace('(^)', '=', $json_data[$key0]);
													$json_data[$key0] = str_replace('((_))', '*', $json_data[$key0]);
													$json_data[$key0] = str_replace('((-))', '|', $json_data[$key0]);
												}
											?>
											<tr>
												<td width="10%"><p><?php echo implode(' <br />', $line_date); ?></p></td>
												<td width="84%" colspan="8">
													<p class="text-balance"><?php 
														echo implode('<br />', $json_data);
													?></p>
												</td>
												<td width="6%"><p class="text-warning">Amount: <br /> <?php echo $line_data[4] ?></p></td>
											</tr>
											<?php
											} else {
											?>
											<tr>
												<td width="10%"><p><?php echo implode(' <br />', $line_date); ?></p></td>
												<td width="34%">
													<p class="<?php 
													$transaction_exe = 0;
													if(strpos($line_data[0], 'Open Orders Count') !== false){ ?>text-info<?php } 
													elseif(strpos($line_data[0], 'There is not enough money') !== false){ ?>text-balance<?php } 
													elseif(strpos($line_data[0], 'The price is stagnant') !== false){ ?>text-balance<?php } 
													elseif(strpos($line_data[0], 'Buying is blocked') !== false){ ?>text-warning<?php }  
													elseif(strpos($line_data[0], 'End of the selling all') !== false){ ?>text-selling<?php }  
													elseif(strpos($line_data[0], 'Gained') !== false){ ?>text-success<?php } 
													elseif(strpos($line_data[0], 'In the selling all period') !== false){ ?>text-selling<?php }  
													elseif(strpos($line_data[0], 'No transaction has been') !== false){ ?>text-balance<?php } 
													elseif(strpos($line_data[0], 'Price limit is reached') !== false){ ?>text-warning<?php } 
													elseif(strpos($line_data[0], 'Out of the time') !== false){ ?>text-warning<?php } 
													elseif(strpos($line_data[0], 'No triggers were fired') !== false){ ?>text-balance<?php } 
													elseif(strpos($line_data[0], 'No purchase is allowed') !== false){ ?>text-balance<?php } 
													elseif(strpos($line_data[0], 'The today\'s total has reached') !== false){ ?>text-warning<?php } 
													elseif(strpos($line_data[0], "\"id\"") !== false) {
													$transaction_exe = 1; 
													$transaction_count++;
													?>transaction_exe<?php
													}
													?>" id="<?php if($transaction_exe == 1){ echo "transaction_exe". $transaction_count; } ?>"><?php
														if($transaction_exe == 1){
															if($hide_reveal == 'show'){
																echo convert_array($json_data);
															} else {
																echo "Transaction entry. This entry is hidden for security purpose.";
															}
														} else {
															echo convert_array($json_data);
														}
													?></p>
												</td>
												<td width="8%"><p class="<?php if($line_data[6] == 'past ratio'){ if($line_data[3] == 'buy' && isset($line_data[3])) { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Past Ratio (1st): <br /><?php echo $line_data[5] ?></p></td>
												<td width="8%"><p class="<?php if($line_data[6] == 'past median' && $line_data[8] == 'extra_long'){ if($line_data[3] == 'buy' && isset($line_data[3])) { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Past Range (2nd): <br /><?php 
													if($line_data[2] != '0'){
														$break = explode('*', $line_data[2]);
														echo $break[2];
													}
												?></p></td>
												<td width="8%"><p class="<?php if($line_data[6] == 'past median' && $line_data[8] == 'long'){ if($line_data[3] == 'buy' && isset($line_data[3])) { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Past Range (3rd): <br /><?php 
													if($line_data[2] != '0'){
														$break = explode('*', $line_data[2]);
														echo $break[1];
													}
												?></p></td>
												<td width="8%"><p class="<?php if($line_data[6] == 'past median' && $line_data[8] == 'short'){ if($line_data[3] == 'buy' && isset($line_data[3])) { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Past Range (4th): <br /><?php 
													if($line_data[2] != '0'){
														$break = explode('*', $line_data[2]);
														echo $break[0];
													}
												?></p></td>
												<td width="8%"><p class="<?php if($line_data[6] == 'future median'){ if($line_data[3] == 'buy' && isset($line_data[3])) { ?>text-success<?php } else { ?>text-danger<?php } } ?>">Future Med (5th): <br /><?php echo $line_data[1] ?></p></td>
												<td width="8%"><p>RSI Long (6th): <br /><?php if(isset($line_data[9])){ echo $line_data[9]; } else { echo '0 / 0'; } ?></p></td>
												<td width="2%"><p class="<?php if($line_data[3] == 'buy' && isset($line_data[3])) { ?>text-success<?php } elseif($line_data[3] == 'sell' && isset($line_data[3])) { ?>text-danger<?php } ?>">Action: <br /> <?php if(isset($line_data[3]) && !empty($line_data[3])){ echo $line_data[3]; } else { echo '0'; } ?></p></td>
												<td width="6%"><p class="text-warning">Amount: <br /> <?php echo $line_data[4] ?></p></td>
											</tr>
											<?php										
											}
										}
									}
								}
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
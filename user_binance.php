<?php 
	ini_set('display_errors', 1);

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
		<title>User - Graph</title>
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
					<h1 align="center" style="text-transform: uppercase;" class="text-success">BINANCE SCHEDULE CONFIGURATION</h1>
				</div>
			</div>
		</div>
		<?php
			$error = '';
			if (isset($_POST['user_id']) && $_POST['user_id'] !== '') {
				$_SESSION['user_in'] = 'binance' . '_' . $_POST['user_id'];
			}

			if (isset($_POST['user_add'])) {
				$account_result = \Core\Accounts\ExchangeAccountService::create('binance', $_POST['user_add']);
				$error = $account_result['error'];
				if ($account_result['user'] !== null) {
					$_SESSION['user_in'] = $account_result['user'];
				}
			}

			if (isset($_SESSION['user_in'])) {
				if (isset($_POST['api_key'], $_SESSION['user_verified']) && $_POST['api_key'] !== '') {
					\Core\Accounts\ExchangeAccountService::saveSettings($_SESSION['user_in'], $_POST);
				}

				$config_options = \Core\Accounts\ExchangeAccountService::loadSettings($_SESSION['user_in']);
				if (isset($_SESSION['user_verified']) && $_SESSION['user_verified'] == 1) {
			?>
			<div class="container-fluid">
				<div class="row" style="display:none;">
					<div class="col-md-1"></div>
					<div class="col-md-10">
						<form action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" name="user_settings" method="post" class="user_settings">
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>API KEYS</h2>
									<label>API Key</label>
									<p>* The API key found in the Binance API section</p>
									<p><input type="text" name="api_key" class="form-control" value="<?php if(isset($config_options['api_key'])) { echo $config_options['api_key']; } ?>" placeholder="API key" required /></p>
									<br />
									<label>Secret Key</label>
									<p>* The secret key found in the Binance API section</p>
									<p><input type="text" name="secret_key" class="form-control" value="<?php if(isset($config_options['secret_key'])) { echo $config_options['secret_key']; } ?>" placeholder="Secret key" required /></p>
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>Crypto News API</h2>
									<label>API Key</label>
									<p>* The API key found in the Crypto News API</p>
									<p><input type="text" name="crypto_news_api_key" class="form-control" value="<?php if(isset($config_options['crypto_news_api_key'])) { echo $config_options['crypto_news_api_key']; } ?>" placeholder="Crypto News API key"  /></p>
									<br />
									<label>Coin Used</label>
									<p>* The coin used for the Crypto News API. Will be used to update the A.I. ranges settings.</p>
									<p><input type="text" name="coin_value" class="form-control" value="<?php if(isset($config_options['coin_value'])) { echo $config_options['coin_value']; } ?>" placeholder="Coin used. E.g: ETH"  /></p>
									<br />
									<label>Activate News Override</label>
									<p>Make the news API prominent and let it dictate when to buy or sell. This setting is good when using the bullish mode.</p>
									<input type="hidden" name="news_override" value="0" />
									<p><input type="checkbox" name="news_override" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['news_override']) && $config_options['news_override'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Disable News Count</label>
									<p>Disable the news count system that block purchase when there are more bad news than good ones.</p>
									<input type="hidden" name="news_count" value="0" />
									<p><input type="checkbox" name="news_count" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['news_count']) && $config_options['news_count'] == 1) { echo "checked='checked'"; } ?> /></p>
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>Notifications</h2>
									<label>From email</label>
									<p>The email used to send the nofitication message.</p>
									<p><input type="text" name="from_email" class="form-control" value="<?php if(isset($config_options['from_email'])) { echo $config_options['from_email']; } ?>" placeholder="email@example.com" /></p>
									<br />
									<label>To email</label>
									<p>The email who is receiving the notification.</p>
									<p><input type="text" name="to_email" class="form-control" value="<?php if(isset($config_options['to_email'])) { echo $config_options['to_email']; } ?>" placeholder="email@example.com" /></p>
									<br />
									<label>SMTP Host</label>
									<p>The host of the email sender.</p>
									<p><input type="text" name="smtp_host" class="form-control" value="<?php if(isset($config_options['smtp_host'])) { echo $config_options['smtp_host']; } ?>" placeholder="smtp.gmail.com" /></p>
									<br />
									<label>SMTP Username</label>
									<p>The username of the email sender.</p>
									<p><input type="text" name="smtp_username" class="form-control" value="<?php if(isset($config_options['smtp_username'])) { echo $config_options['smtp_username']; } ?>" placeholder="example@gmail.com" /></p>
									<br />
									<label>SMTP Password</label>
									<p>The password of the email sender.</p>
									<p><input type="text" name="smtp_password" class="form-control" value="<?php if(isset($config_options['smtp_password'])) { echo $config_options['smtp_password']; } ?>" placeholder="xxxxxxx" /></p>
									<br />
									<label>SMTP Port (SSL)</label>
									<p>The port of the email host.</p>
									<p><input type="text" name="smtp_port" class="form-control" value="<?php if(isset($config_options['smtp_port'])) { echo $config_options['smtp_port']; } ?>" placeholder="465" /></p>
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>STOCKS</h2>
									<label>Buying + Selling Combined</label>
									<p>* The type of stock you would like to purchase or sell. Purchasing ETH from BUSD would be ETHBUSD. Selling ETH for BUSD is also the same.</p>
									<p><input type="text" name="stock_buy" pattern="([A-Z]{3,12})" class="form-control" value="<?php if(isset($config_options['stock_buy'])) { echo $config_options['stock_buy']; } else { echo 'ETHBUSD'; } ?>" placeholder="What stock to buy or sell, E.g.: ETHBUSD" required /></p>
									<br />
									<label>Selling</label>
									<p>* The funds that are less risky. Have to be the stock that you receive after selling. E.g.: for ETHBUSD it would be BUSD</p>
									<p><input type="text" name="stock_account" pattern="([A-Z]{2,6})" class="form-control" value="<?php if(isset($config_options['stock_account'])) { echo $config_options['stock_account']; }  else { echo 'BUSD'; } ?>" placeholder="What to sell for, E.g.: BUSD" required /></p>
									<br />
									<label>Buying</label>
									<p>* The funds that are more risky. Have to be the stock that you receive after buying. E.g.: for ETHBUSD it would be BUSD</p>
									<p><input type="text" name="sell_coin" pattern="([A-Z]{2,6})" class="form-control" value="<?php if(isset($config_options['sell_coin'])) { echo $config_options['sell_coin']; } else { echo 'ETH'; } ?>" placeholder="What to buy, E.g.: ETH" required /></p>
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>RECOMMENDED MODE</h2>
									<div class="row" style="display:none;">
										<div class="col-md-3">
											<label>Bullish</label>
											<p><input type="radio" name="recommended_mode" class="form-control" style="width: 40px;" value="bullish" />
										</div>
										<div class="col-md-3">
											<label>Detect lowest</label>
											<p><input type="radio" name="recommended_mode" class="form-control" style="width: 40px;" value="detect_lowest" />
										</div>
										<div class="col-md-3">
											<label>Bearish</label>
											<p><input type="radio" name="recommended_mode" class="form-control" style="width: 40px;" value="bearish" />
										</div>
										<div class="col-md-3">
											<label>Experimental</label>
											<p><input type="radio" name="recommended_mode" class="form-control" style="width: 40px;" value="experimental" />
										</div>
									</div>
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-6">
									<h2>BUYING</h2>
									<label>Buying Price</label>
									<p>The price needed for the quantity of the stock you would like to buy. If left empty will equal the minimum price. E.g.: around 3300 for Ethereum.</p>
									<p><input type="number" step="1" name="buy_price" class="form-control" value="<?php if(isset($config_options['buy_price'])) { echo $config_options['buy_price']; } ?>" placeholder="Buy price, leave blank if you aren't sure. E.g: 3300" /></p>
									<br />
									<label>Buying Quantity</label>
									<p>* The quantity base on the stock full price. E.g.: if Ethereum equals 3300 $ then 3300 * 0.0045 = around more than 15 $. 3300 represent the buy price above if set. It will need at least the minimum quantity. The base price for short, ratio and future median indicators excluding the price multiplier section.</p>
									<p><input type="number" step="0.0001" name="buy_quantity" class="form-control" value="<?php if(isset($config_options['buy_quantity'])) { echo $config_options['buy_quantity']; } else { echo '0.0045'; } ?>" placeholder="The buy quantity. E.g: 0.0045" required /></p>
									<br />
									<p>Self-adjust for minimum price</p>
									<input type="hidden" name="self_adjust_minimum_buy" value="0" />
									<p><input type="checkbox" name="self_adjust_minimum_buy" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['self_adjust_minimum_buy']) && $config_options['self_adjust_minimum_buy'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Buying at Future Median</label>
									<p>* This field represent the median range calculated for the number of minute (E.g.: 1,2,3) within the period (E.g: weekly, monthly, yearly), If the next 2 minutes are both 10 of median range on the graph it would equal 20 of median range calculated for a 2 minutes future period. Need to be a negative number.</p>
									<p><input type="number" step="0.001" name="sell_at" class="form-control" value="<?php if(isset($config_options['sell_at'])) { echo $config_options['sell_at']; } else { echo '-0.5'; } ?>" placeholder="Buy at this median range calculated. E.g: -0.5" required /></p>
									<br />
									<label>Buying Fee</label>
									<p>* The fee that is paid for each buying transaction. Will use it only for calculating the experimental mode live result. It should be in percentage. E.g.: 0.001 for 0.1%</p>
									<p><input type="number" step="0.00001" min="0" name="buying_fee" class="form-control" value="<?php if(isset($config_options['buying_fee'])) { echo $config_options['buying_fee']; } else { echo '0.001'; } ?>" placeholder="Buying fee. E.g: 0.001 for 0.1%" required /></p>
									<hr />
								</div>
								<div class="col-md-6">
									<h2>SELLING</h2>
									<label>Selling Price</label>
									<p>The price needed for the quantity of the stock you would like to sell. If left empty will equal the minimum price. E.g.: around 3300 for Ethereum.</p>
									<p><input type="number" step="1" name="sell_price" class="form-control" value="<?php if(isset($config_options['sell_price'])) { echo $config_options['sell_price']; } ?>" placeholder="Sell price, leave blank if you aren't sure. E.g: 3300" /></p>
									<br />
									<label>Selling Quantity</label>
									<p>* The quantity base on the stock full price. E.g.: if Ethereum equals 3300 $ then 3300 * 0.0045 = around more than 15 $. 3300 represent the sell price above if set. It will need at least the minimum quantity. The base price for short, ratio and future median indicators excluding the price multiplier section.</p>
									<p><input type="number" step="0.0001" name="sell_quantity" class="form-control" value="<?php if(isset($config_options['sell_quantity'])) { echo $config_options['sell_quantity']; }  else { echo '0.0045'; } ?>" placeholder="The sell quantity. E.g: 0.0045" required /></p>
									<br />
									<p>Self-adjust for minimum price</p>
									<input type="hidden" name="self_adjust_minimum_sell" value="0" />
									<p><input type="checkbox" name="self_adjust_minimum_sell" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['self_adjust_minimum_sell']) && $config_options['self_adjust_minimum_sell'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Selling at Future Median</label>
									<p>* This field represent the median range calculated for the number of minute (E.g.: 1,2,3) within the period (E.g: weekly, monthly, yearly), If the next 2 minutes are both 10 of median range on the graph it would equal 20 of median range calculated for a 2 minutes future period. Need to be a positive number.</p>
									<p><input type="number" step="0.001" name="buy_at" class="form-control" value="<?php if(isset($config_options['buy_at'])) { echo $config_options['buy_at']; } else { echo '0.5'; } ?>" placeholder="Sell at this median range calculated. E.g: 0.5" required /></p>
									<br />
									<label>Selling Fee</label>
									<p>* The fee that is paid for each selling transaction. Will use it only for calculating the experimental mode live result. It should be in percentage. E.g.: 0.001 for 0.1%</p>
									<p><input type="number" step="0.00001" min="0" name="selling_fee" class="form-control" value="<?php if(isset($config_options['selling_fee'])) { echo $config_options['selling_fee']; } else { echo '0.001'; } ?>" placeholder="Selling fee. E.g: 0.001 for 0.1%" required /></p>
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>FREQUENCE</h2>
									<label>Activate Future Indicator</label>
									<p>Tick this to activate all the future median indicators. This option is useful when the data for the graph don't exist for the coin you are using.</p>
									<input type="hidden" name="future_ind" value="0" />
									<p><input type="checkbox" name="future_ind" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['future_ind']) && $config_options['future_ind'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Crypto used for Graph</label>
									<p>* The crypto folder associated for binding the API to this app graph. ethereum_USD/data_weekly would be used if the period is weekly and for Ethereum.</p>
									<p><select name="crypto" class="form-control" required>
										<?php foreach($stocks_array as $key => $value){ ?> 
											<option value="<?php echo $value ?>" <?php if(isset($config_options['crypto']) && $config_options['crypto'] == $value){ ?>selected="selected"<?php } ?>><?php $symbol = array_keys($stocks_array, $value); echo $symbol[0].' - '.strtoupper(str_replace('_', ' ', $value)); ?></option>
										<?php } ?>
									</select></p>
									<br />
									<label>Period for Future Median</label>
									<p>* The period folder associated for binding the API to this app graph. ethereum_USD/data_weekly would be used if the period is weekly and for Ethereum.</p>
									<p><select name="estimation_folder" class="form-control" required>
										<option value="weekly" <?php if(isset($config_options['estimation_folder']) && $config_options['estimation_folder'] == "weekly"){ ?>selected="selected"<?php } ?>>Weekly</option>
										<option value="monthly" <?php if(isset($config_options['estimation_folder']) && $config_options['estimation_folder'] == "monthly"){ ?>selected="selected"<?php } ?>>Monthly</option>
										<option value="yearly" <?php if(isset($config_options['estimation_folder']) && $config_options['estimation_folder'] == "yearly"){ ?>selected="selected"<?php } ?>>Yearly</option>
									</select></p>
									<br />
									<label>Minimum Days for Calculation</label>
									<p>* The limit of days needed in the graph files to calculate the median minute range. Out of 30 days period there could be only 25 days data. E.g: 25. This field is required.</p>
									<p><input type="number" step="1" name="limit_of_day" class="form-control" value="<?php if(isset($config_options['limit_of_day'])) { echo $config_options['limit_of_day']; } else { echo '25'; } ?>" placeholder="Minimum of days data. E.g.: 25" required /></p>
									<br />
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>Minutes for Calculations</label>
											<p>* Period in minutes to collect data for the future median. The data will be collected each minute.<br />This field is required.</p>
											<p><input type="number" step="1" max="60" min="1" name="minute_amount" class="form-control"  value="<?php if(isset($config_options['minute_amount'])) { echo $config_options['minute_amount']; } else { echo '10'; } ?>" placeholder="Amount of minutes for the range calculation. E.g.: 10" required /></p>
											<br />
											<label>Data Collection Minutes Interval</label>
											<p>* The interval of minutes to capture the data based on the previous data collection minutes period above.<br />This field is required.</p>
											<p><input type="number" step="1" max="60" min="1" name="minute_amount_interval" class="form-control" value="<?php if(isset($config_options['minute_amount_interval'])) { echo $config_options['minute_amount_interval']; } else { echo '5'; } ?>" placeholder="Self-Adjust Minutes Interval. E.g.: 5" required /></p>
											<br />
										</div>
										<div class="col-md-6">
											<label>Data Collection Seconds</label>
											<p>* Period in seconds to collect data for ratio and range. The data will be collected each second.<br />This field is required.</p>
											<p><input type="number" max="60" min="10" step="1" name="self_adjust_minutes" class="form-control" value="<?php if(isset($config_options['self_adjust_minutes'])) { echo $config_options['self_adjust_minutes']; } else { echo '60'; } ?>" placeholder="Self-Adjust Seconds. E.g.: 60" required /></p>
											<br />
											<label>Data Collection Seconds Interval</label>
											<p>* The interval of seconds to capture the data based on the previous data collection seconds period above.<br />This field is required.</p>
											<p><input type="number" max="60" min="5" step="1" name="self_adjust_minutes_interval" class="form-control" value="<?php if(isset($config_options['self_adjust_minutes_interval'])) { echo $config_options['self_adjust_minutes_interval']; } else { echo '5'; } ?>" placeholder="Self-Adjust Seconds Interval. E.g.: 5" required /></p>
											<br />
										</div>
									</div>
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>RSI top (5/150)</label>
											<p>* The top RSI (5 seconds for 150 seconds period) which will calculate the stagnancy of the price</p>
											<p><input type="number" step="1" max="100" min="0" name="rsi_top" class="form-control" value="<?php if(isset($config_options['rsi_top'])) { echo $config_options['rsi_top']; } else { echo '80'; } ?>" placeholder="RSI top. E.g.: 80" required /></p>
											<br />
										</div>
										<div class="col-md-6">
											<label>RSI bottom (5/150)</label>
											<p>* The bottom RSI (5 seconds for 150 seconds period) which will calculate the stagnancy of the price</p>
											<p><input type="number" step="1" max="100" min="0" name="rsi_bottom" class="form-control" value="<?php if(isset($config_options['rsi_bottom'])) { echo $config_options['rsi_bottom']; } else { echo '20'; } ?>" placeholder="RSI bottom. E.g.: 20" required /></p>
											<br />
										</div>
									</div>
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>RSI top long (5/600)</label>
											<p>* The top RSI (5 seconds for 600 seconds period) which will calculate the stagnancy of the price</p>
											<p><input type="number" step="1" max="100" min="0" name="rsi_top_long" class="form-control" value="<?php if(isset($config_options['rsi_top_long'])) { echo $config_options['rsi_top_long']; } else { echo '80'; } ?>" placeholder="RSI top long. E.g.: 80" required /></p>
											<br />
										</div>
										<div class="col-md-6">
											<label>RSI bottom long (5/600)</label>
											<p>* The bottom RSI (5 seconds for 600 seconds period) which will calculate the stagnancy of the price</p>
											<p><input type="number" step="1" max="100" min="0" name="rsi_bottom_long" class="form-control" value="<?php if(isset($config_options['rsi_bottom_long'])) { echo $config_options['rsi_bottom_long']; } else { echo '20'; } ?>" placeholder="RSI bottom long. E.g.: 20" required /></p>
											<br />
										</div>
									</div>
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>RSI Self-Adjust</label>
											<p>Self-Adjust the RSI top and bottom limits.</p>
											<input type="hidden" name="rsi_self_adjust" value="0" />
											<p><input type="checkbox" name="rsi_self_adjust" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['rsi_self_adjust']) && $config_options['rsi_self_adjust'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
										<div class="col-md-6">
											<label>RSI Long Self-Adjust</label>
											<p>Self-Adjust the RSI Long top and bottom limits.</p>
											<input type="hidden" name="rsi_long_self_adjust" value="0" />
											<p><input type="checkbox" name="rsi_long_self_adjust" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['rsi_long_self_adjust']) && $config_options['rsi_long_self_adjust'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
									</div>
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-6">
									<h2>PAST READING BUYING</h2>
									<label>Past Range for Buying</label>
									<p>* The calculated number of past range incremented before buying. Can only be a negative number. Will get triggered when the price is balancing between two price of a short range.</p>
									<p><input type="number" step="0.001" name="past_range_decrease" class="form-control" value="<?php if(isset($config_options['past_range_decrease'])) { echo $config_options['past_range_decrease']; } else { echo '-0.5'; } ?>" placeholder="Past range number. E.g.: -0.5" required /></p>
									<br />
									<label>Long Past Range for Buying</label>
									<p>* The calculated number of long past range incremented before buying. Can only be a negative number. Will get triggered when the price is balancing between two price of a medium range</p>
									<p><input type="number" step="0.001" name="past_range_long_decrease" class="form-control" value="<?php if(isset($config_options['past_range_long_decrease'])) { echo $config_options['past_range_long_decrease']; } else { echo '-0.5'; } ?>" placeholder="Long Past range number. E.g.: -0.5" required /></p>
									<br />
									<label>Extra Long Past Range for Buying</label>
									<p>* The calculated number of extra long past range incremented before buying. Can only be a negative number. Will get triggered when the price is balancing between two price of a long range.</p>
									<p><input type="number" step="0.001" name="past_range_extra_long_decrease" class="form-control" value="<?php if(isset($config_options['past_range_extra_long_decrease'])) { echo $config_options['past_range_extra_long_decrease']; } else { echo '-0.5'; } ?>" placeholder="Extra Long Past range number. E.g.: -0.5" required /></p>
									<br />
									<label>Past Ratio for Buying</label>
									<p>* The calculated ratio of past negative range divided by the past positive range before buying. Can only be a positive decimal between 0.01 and 1. Will get triggered when the price is balancing between two price of a short range.</p>
									<p><input type="number" max="0.999" min="0.001" step="0.001" name="defined_decreasing_ratio" class="form-control" value="<?php if(isset($config_options['defined_decreasing_ratio'])) { echo $config_options['defined_decreasing_ratio']; } else { echo '0.3'; } ?>" placeholder="Past range positive ratio. E.g.: 0.3" required /></p>
									<br />
									<label>Long Range for Buying</label>
									<p>Only use the long range detection for buying stocks. Is useful when you want to minimize the transactions and making sure of the increase before buying.</p>
									<input type="hidden" name="selling_long_range_override" value="0" />
									<p><input type="checkbox" name="selling_long_range_override" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['selling_long_range_override']) && $config_options['selling_long_range_override'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Block Buying Transactions</label>
									<p>Will block all the buying transactions of the system but will exclude the price limit indicator. Is useful when you want to avoid extra fees on the buying if there is any.</p>
									<input type="hidden" name="block_buying" value="0" />
									<p><input type="checkbox" name="block_buying" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['block_buying']) && $config_options['block_buying'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<hr />
								</div>
								<div class="col-md-6">
									<h2>PAST READING SELLING</h2>
									<label>Past Range for Selling</label>
									<p>* The calculated number of past range incremented before selling. Can only be a positive number. Will get triggered when the price is balancing between two price of a short range.</p>
									<p><input type="number" step="0.001" name="past_range_increase" class="form-control" value="<?php if(isset($config_options['past_range_increase'])) { echo $config_options['past_range_increase']; } else { echo '0.5'; } ?>" placeholder="Past range number. E.g.: 0.5" required /></p>
									<br />
									<label>Long Past Range for Selling</label>
									<p>* The calculated number of long past range incremented before selling. Can only be a positive number. Will get triggered when the price is balancing between two price of a medium range.</p>
									<p><input type="number" step="0.001" name="past_range_long_increase" class="form-control" value="<?php if(isset($config_options['past_range_long_increase'])) { echo $config_options['past_range_long_increase']; } else { echo '0.5'; } ?>" placeholder="Long Past range number. E.g.: 0.5" required /></p>
									<br />
									<label>Extra Long Past Range for Selling</label>
									<p>* The calculated number of extra long past range incremented before selling. Can only be a positive number. Will get triggered when the price is balancing between two price of a long range.</p>
									<p><input type="number" step="0.001" name="past_range_extra_long_increase" class="form-control" value="<?php if(isset($config_options['past_range_extra_long_increase'])) { echo $config_options['past_range_extra_long_increase']; } else { echo '0.5'; } ?>" placeholder="Extra Long Past range number. E.g.: 0.5" required /></p>
									<br />
									<label>Past Ratio for Selling</label>
									<p>* The calculated ratio of past positive range divided by the past negative range before selling. Can only be a positive decimal between 0.01 and 1. Will get triggered when the price is balancing between two price of a short range.</p>
									<p><input type="number" max="0.999" min="0.001" step="0.001" name="defined_increasing_ratio" class="form-control" value="<?php if(isset($config_options['defined_increasing_ratio'])) { echo $config_options['defined_increasing_ratio']; } else { echo '0.3'; } ?>" placeholder="Past range negative ratio. E.g.: 0.3" required /></p>
									<br />
									<label>Long Range for Selling</label>
									<p>Only use the long range detection for selling stocks. Is useful when you want to minimize the transactions and making sure of the decrease before selling.</p>
									<input type="hidden" name="buying_long_range_override" value="0" />
									<p><input type="checkbox" name="buying_long_range_override" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['buying_long_range_override']) && $config_options['buying_long_range_override'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Block Selling Transactions</label>
									<p>Will block all the selling transactions of the system but will exclude the price limit indicator. Is useful when you want to avoid extra fees on the selling if there is any.</p>
									<input type="hidden" name="block_selling" value="0" />
									<p><input type="checkbox" name="block_selling" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['block_selling']) && $config_options['block_selling'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-6">
									<h2>PRICE MULTIPLIER BUYING</h2>
									<label>Percentage on Balance for Buying</label>
									<p>* The percentage of the account balance used for the buying price. E.g.: 30% of 200$ = ((60$ / stock price (e.g.: 3300$) for quantity) + regular quantity used above for buying). Is useful to play with bigger amount and act quickly when the price changes.</p>
									<p><input type="number" name="buying_price_multiplier" step="0.1" max="100" min="0" class="form-control" value="<?php if(isset($config_options['buying_price_multiplier'])) { echo $config_options['buying_price_multiplier']; } else { echo '30'; } ?>" placeholder="Buying Price Multiplier. E.g.: 30" required /></p>
									<br />
									<label>Self-Adjust Balance Buying</label>
									<p>Will adjust the balance percentage automatically based on the current balance range between minute and number of buys.</p>
									<input type="hidden" name="self_adjust_balance_buying" value="0" />
									<p><input type="checkbox" name="self_adjust_balance_buying" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['self_adjust_balance_buying']) && $config_options['self_adjust_balance_buying'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Ratio on Balance for Buying</label>
									<p>Use past ratio percentage calculated on the past X minutes set above for multiplying the buying price. E.g.: 30% of 200$ = (60$ / stock price (e.g.: 3300$) = quantity) + regular quantity used above for buying + ((ratio of last 6 minutes (e.g. 0.3) * balance (e.g.: 200$)) / stock price (e.g.: 3300$)) = Buying quantity. Is useful when you want to play accordingly to the price variations.</p>
									<input type="hidden" name="buying_price_ratio_toggle" value="0" />
									<p><input type="checkbox" name="buying_price_ratio_toggle" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['buying_price_ratio_toggle']) && $config_options['buying_price_ratio_toggle'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Price Multiplier Only for Long Range</label>
									<p>Use price multiplier only when its a long range increase for buying. Is useful when you want to separate big transactions for the long period indicator to avoid playing with big amount all the time.</p>
									<input type="hidden" name="buying_price_multiplier_range" value="0" />
									<p><input type="checkbox" name="buying_price_multiplier_range" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['buying_price_multiplier_range']) && $config_options['buying_price_multiplier_range'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
								</div>
								<div class="col-md-6">
									<h2>PRICE MULTIPLIER SELLING</h2>
									<label>Percentage on Balance for Selling</label>
									<p>* The percentage of the account balance used for the selling price. E.g.: 30% of 200$ = ((60$ / stock price (e.g.: 3300$) for quantity) + regular quantity used above for selling). Is useful to play with bigger amount and act quickly when the price changes.</p>
									<p><input type="number" name="selling_price_multiplier" step="0.1" max="100" min="0" class="form-control" value="<?php if(isset($config_options['selling_price_multiplier'])) { echo $config_options['selling_price_multiplier']; } else { echo '30'; } ?>" placeholder="Selling Price Multiplier. E.g.: 30" required /></p>
									<br />
									<label>Self-Adjust Balance Selling</label>
									<p>Will adjust the balance percentage automatically based on the current balance range between minute and number of sellings.</p>
									<input type="hidden" name="self_adjust_balance_selling" value="0" />
									<p><input type="checkbox" name="self_adjust_balance_selling" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['self_adjust_balance_selling']) && $config_options['self_adjust_balance_selling'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Ratio on Balance for Selling</label>
									<p>Use past ratio percentage calculated on the past X minutes set above for multiplying the selling price. E.g.: 30% of 200$ = (60$ / stock price (e.g.: 3300$) = quantity) + regular quantity used above for selling + ((ratio of last 6 minutes (e.g. 0.3) * balance (e.g.: 200$)) / stock price (e.g.: 3300$)) = Selling quantity. Is useful when you want to play accordingly to the price variations.</p>
									<input type="hidden" name="selling_price_ratio_toggle" value="0" />
									<p><input type="checkbox" name="selling_price_ratio_toggle" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['selling_price_ratio_toggle']) && $config_options['selling_price_ratio_toggle'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Price Multiplier Only for Long Range</label>
									<p>Use price multiplier only when its a long range increase for selling. Is useful when you want to separate big transactions for the long period indicator to avoid playing with big amount all the time.</p>
									<input type="hidden" name="selling_price_multiplier_range" value="0" />
									<p><input type="checkbox" name="selling_price_multiplier_range" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['selling_price_multiplier_range']) && $config_options['selling_price_multiplier_range'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<label>Past Ratio Maximum Limit</label>
									<p>* Use past ratio maximum limit on calculating the selling or buying quantity triggered by the ratio fields. E.g.:below 0.5 to modify the quantity from price.</p>
									<p><input type="number" max="0.999" min="0.001" step="0.001" name="past_ratio_multiplier_limit" class="form-control" value="<?php if(isset($config_options['past_ratio_multiplier_limit'])) { echo $config_options['past_ratio_multiplier_limit']; } else { echo '0.5'; } ?>" placeholder="Past Ratio Multiplier Limit. E.g.: 0.5" required /></p>
									<br />
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>SELF ADJUST</h2>
									<label>Self-Adjust A.I.</label>
									<p>Will adjust the previous past reading section, price multipler sections settings and future median based on the amount of minutes for calculation and seconds you set below. Is useful if you don't want to change constantly the indicator numbers.</p>
									<input type="hidden" name="self_adjust" value="0" />
									<p><input type="checkbox" name="self_adjust" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['self_adjust']) && $config_options['self_adjust'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>TIME TO OPERATE</h2>
									<label>Activate</label>
									<p>Enable or disable the use of time to operate.</p>
									<input type="hidden" name="operate_switch" value="0" />
									<p><input type="checkbox" name="operate_switch" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['operate_switch']) && $config_options['operate_switch'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>Start hour - Hours</label>
											<p>Start hour to operate during the day. E.g.: 00:30 would be 0. Is useful if you know that the price generally goes up at this period.</p>
											<p><input type="number" step="1" name="start_hour_hours" max="11" min="0" class="form-control" value="<?php if(isset($config_options['start_hour_hours'])) { echo $config_options['start_hour_hours']; } else { echo '0'; } ?>" placeholder="Start hour. E.g.: 0" required /></p>
											<br />
										</div>
										<div class="col-md-6">
											<label>Start hour - Minutes</label>
											<p>Start hour to operate during the day. E.g.: 00:05 would be 5. Is useful if you know that the price generally goes up at this period.</p>
											<p><input type="number" step="1" name="start_hour_minutes" max="59" min="5" class="form-control" value="<?php if(isset($config_options['start_hour_minutes'])) { echo $config_options['start_hour_minutes']; } else { echo '5'; } ?>" placeholder="Start hour. E.g.: 5" required /></p>
											<br />
										</div>
									</div>
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>End hour - Hours</label>
											<p>End hour to operate during the day. E.g.: 00:30 would be 0. Is useful if you know that the price generally goes down at this period.</p>
											<p><input type="number" step="1" name="end_hour_hours" max="23" min="12" class="form-control" value="<?php if(isset($config_options['end_hour_hours'])) { echo $config_options['end_hour_hours']; } else { echo '0'; } ?>" placeholder="End hour. E.g.: 0" required /></p>
											<br />
										</div>
										<div class="col-md-6">
											<label>End hour - Minutes</label>
											<p>End hour to operate during the day. E.g.: 00:05 would be 5. Is useful if you know that the price generally goes up at this period.</p>
											<p><input type="number" step="1" name="end_hour_minutes" max="59" min="5" class="form-control" value="<?php if(isset($config_options['end_hour_minutes'])) { echo $config_options['end_hour_minutes']; } else { echo '5'; } ?>" placeholder="End hour. E.g.: 5" required /></p>
											<br />
										</div>
									</div>
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>STARTING PRICE</h2>
									<label>Money Start</label>
									<p>The money you added in before any of the investment. E.g.: could be 1000$</p>
									<p><input type="number" name="total_money" min="0" step="0.01" class="form-control" value="<?php if(isset($config_options['total_money'])) { echo $config_options['total_money']; }else { echo '1000'; } ?>" placeholder="Money start: 1000" required /></p>
									<br />
									<label>Money Withdrew or Converted</label>
									<p>The money you withdrew or converted from your current coin to another coin during the investment. E.g.: could be (2022-01-01:1000,2022-02-05:1000)</p>
									<p><input type="text" name="total_money_withdrew" class="form-control" value="<?php if(isset($config_options['total_money_withdrew'])) { echo $config_options['total_money_withdrew']; }else { echo '2022-01-01:0'; } ?>" placeholder="Money withdrew: (E.g.: 2022-01-01:0)" required /></p>
									<br />
									<label>Money Deposited or Converted</label>
									<p>The money you deposited or converted from another coin to your current coin during the investment. E.g.: could be (2022-01-01:1000,2022-02-05:1000)</p>
									<p><input type="text" name="total_money_deposited" class="form-control" value="<?php if(isset($config_options['total_money_deposited'])) { echo $config_options['total_money_deposited']; }else { echo '2022-01-01:0'; } ?>" placeholder="Money deposited: (E.g.: 2022-01-01:0)" required /></p>
									<br />
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>TOP LEVEL</h2>
									<label>Activate</label>
									<p>Enable or disable the use of top level limit. Will block buying transaction when it reaches the percentage below.</p>
									<input type="hidden" name="top_level" value="0" />
									<p><input type="checkbox" name="top_level" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['top_level']) && $config_options['top_level'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Top Level Percentage</label>
									<p>The top level percentage to use for the period of buying transaction. E.g.: 0.75 for 75 % between the highest price and lowest.</p>
									<p><input type="number" name="top_level_percentage" min="0" step="0.01" max="0.99" class="form-control" value="<?php if(isset($config_options['top_level_percentage'])) { echo $config_options['top_level_percentage']; }else { echo '0.75'; } ?>" placeholder="Top Level Percentage E.g.: 0.75" required /></p>
									<br />
									<label>Top Level Range</label>
									<p>The minimum range calculated from highest to lowest price before making any buy transactions.</p>
									<p><input type="number" name="top_level_range" min="0" step="0.01" class="form-control" value="<?php if(isset($config_options['top_level_range'])) { echo $config_options['top_level_range']; }else { echo '100'; } ?>" placeholder="Top Level Range. E.g.: 100" required /></p>
									<br />
									<label>Activate Self-Adjust</label>
									<p>Enable the A.I. to self-adjust the top level limit based on a percentage of yesterday highest price.</p>
									<input type="hidden" name="adjust_top_level" value="0" />
									<p><input type="checkbox" name="adjust_top_level" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['adjust_top_level']) && $config_options['adjust_top_level'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Self-Adjust Percentage</label>
									<p>The self-adjust percentage to calculate the top level range. E.g.: 0.02 for 2% of the total price.</p>
									<p><input type="number" name="adjust_top_level_per" min="0" step="0.0001" class="form-control" value="<?php if(isset($config_options['adjust_top_level_per'])) { echo $config_options['adjust_top_level_per']; }else { echo '0.02'; } ?>" placeholder="Self-Adjust Percentage. E.g.: 0.02" /></p>
									<br />
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>GAIN LIMIT</h2>
									<label>Activate</label>
									<p>Enable the gain limit function to pause the A.I. whenever a specific gain is reached during the day.</p>
									<input type="hidden" name="gain_limit" value="0" />
									<p><input type="checkbox" name="gain_limit" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['gain_limit']) && $config_options['gain_limit'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Gain limit</label>
									<p>Set the gain limit to where the script stop transaction for the day. Will sell at this point and start again the next day. E.g: 10 for 10 $</p>
									<p><input type="number" name="gain_limit_money" min="1" step="0.01" max="100" class="form-control" value="<?php if(isset($config_options['gain_limit_money'])) { echo $config_options['gain_limit_money']; }else { echo '100'; } ?>" placeholder="Gain Limit Money E.g.: 100" required /></p>
									<br />
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<h2>PRICE LIMIT</h2>
									<label>Activate</label>
									<p>Enable or disable the use of price limit.</p>
									<input type="hidden" name="price_switch" value="0" />
									<p><input type="checkbox" name="price_switch" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['price_switch']) && $config_options['price_switch'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>Basic price before selling all</label>
											<p>Price that is reached before selling all and reporting to next day. Is useful when the price mostly toggle in a range and you need to make sure to get the gain before continuing.</p>
											<p><input type="number" name="price_reached" min="0" step="0.01" class="form-control" value="<?php if(isset($config_options['price_reached'])) { echo $config_options['price_reached']; }else { echo '3300'; } ?>" placeholder="Price of the stock before selling all. E.g.: 3300" required /></p>
											<br />
										</div>
										<div class="col-md-6">
											<label>Price Increase Range</label>
											<p>Will add this amount to the basic sell all price and calculate the highest sell all limit. When this limit is reached the coins will be sold until an empty balance and lock the transactions.</p>
											<p><input type="number" name="price_increase" step="1" class="form-control" value="<?php if(isset($config_options['price_increase'])) { echo $config_options['price_increase']; } else { echo '80'; } ?>" placeholder="Additional amount added to price limit. E.g.: 80" required /></p>
											<br />
										</div>
									</div>
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>Self-Adjust Price on Limit Crossing</label>
											<p>Will adjust the low selling price limit when crossing below the low price limit and after 10 buying triggered. Is useful when not much coins are bought and the price is going down.</p>
											<input type="hidden" name="update_low_limit" value="0" />
											<p><input type="checkbox" name="update_low_limit" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['update_low_limit']) && $config_options['update_low_limit'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>	
										<div class="col-md-6">
											<label>Self-Adjust Price</label>
											<p>Will adjust the low selling price limit at the start of operating time with current price. Is useful only when the price keeps going up and you want to sell at a limit to keep your profit.</p>
											<input type="hidden" name="self_adjust_price" value="0" />
											<p><input type="checkbox" name="self_adjust_price" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['self_adjust_price']) && $config_options['self_adjust_price'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
									</div>
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>Detect Lowest</label>
											<p>Detect lowest price each time it goes in selling all mode and at the start of the day. Will block buying before the lowest price is reached. Will also set the low price limit when the current price reaches 20% above current lowest detected price. The delay before the lowest is set is 1 hour. Delay will reset if it goes lower before 1 hour.</p>
											<input type="hidden" name="detect_lowest" value="0" />
											<p><input type="checkbox" name="detect_lowest" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['detect_lowest']) && $config_options['detect_lowest'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>	
										<div class="col-md-6">
											<label>Time Sell</label>
											<p>Will sell after X amount of minutes if the price is over the lower sell limit, if it has been more than the time allocated for sell all delay - high limit and if today's total is above 0$.</p>
											<input type="hidden" name="time_sell" value="0" />
											<p><input type="checkbox" name="time_sell" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['time_sell']) && $config_options['time_sell'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
									</div>
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>Detect Lowest - Time</label>
											<p>The time before detect lowest price get set. It will detect new price if the price goes lower than the actual one before reaching this time limit. E.g.: 3600 seconds for 1 hour.</p>
											<p><input type="number" step="1" name="detect_lowest_time" max="21600" min="1" class="form-control" value="<?php if(isset($config_options['detect_lowest_time'])) { echo $config_options['detect_lowest_time']; } else { echo '3600'; } ?>" placeholder="Delay. E.g.: 3600" required /></p>
											<br />
										</div>
										<div class="col-md-6">
											<label>Detect Lowest Break</label>
											<p>This feature can help break free from the detect lowest mode. If more than 65% of the extra long range is obtained in 2 rows the buying will be reactivated and detect lowest turned off.</p>
											<input type="hidden" name="detect_lowest_break" value="0" />
											<p><input type="checkbox" name="detect_lowest_break" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['detect_lowest_break']) && $config_options['detect_lowest_break'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
									</div>
									<hr />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-6">
									<h2>LOW LIMIT</h2>
									<label>Disable Sell All - Low Limit</label>
									<p>Disable out the sell all period when the price has reached the low limit. Will also never trigger a detect lowest.</p>
									<input type="hidden" name="sell_all_low_cancel" value="0" />
									<p><input type="checkbox" name="sell_all_low_cancel" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['sell_all_low_cancel']) && $config_options['sell_all_low_cancel'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Lock the Sell All - Low Limit</label>
									<p>Lock the Sell All mode when the price crossed back the low limit above. Will get in detect lowest mode if the setting is activated once it have sold all.</p>
									<input type="hidden" name="sell_all_lock_low" value="0" />
									<p><input type="checkbox" name="sell_all_lock_low" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['sell_all_lock_low']) && $config_options['sell_all_lock_low'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Sell All Delay - Low Limit</label>
									<p>Sell all the funds only after a certain delay is accumulated in seconds. E.g.: 3600 seconds for 1 hour. Will reset each time the price cross higher than the low limit.</p>
									<p><input type="number" step="1" name="wait_time_sell_delay_low" max="21600" min="1" class="form-control" value="<?php if(isset($config_options['wait_time_sell_delay_low'])) { echo $config_options['wait_time_sell_delay_low']; } else { echo '900'; } ?>" placeholder="Delay. E.g.: 900" required /></p>
									<br />
								</div>
								<div class="col-md-6">
									<h2>HIGH LIMIT</h2>
									<label>Disable Sell All - High Limit</label>
									<p>Disable out the sell all period when the price has reached the high limit. Will also never trigger a detect lowest.</p>
									<input type="hidden" name="sell_all_high_cancel" value="0" />
									<p><input type="checkbox" name="sell_all_high_cancel" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['sell_all_high_cancel']) && $config_options['sell_all_high_cancel'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Lock the Sell All - High Limit</label>
									<p>Lock the Sell All mode when the price crossed back the high limit below. Will get in detect lowest mode if the setting is activated once it have sold all.</p>
									<input type="hidden" name="sell_all_lock_high" value="0" />
									<p><input type="checkbox" name="sell_all_lock_high" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['sell_all_lock_high']) && $config_options['sell_all_lock_high'] == 1) { echo "checked='checked'"; } ?> /></p>
									<br />
									<label>Sell All Delay - High Limit</label>
									<p>Sell all the funds only after a certain delay is accumulated in seconds. E.g.: 3600 seconds for 1 hour. Will reset each time the price go higher than current and above low limit.</p>
									<p><input type="number" step="1" name="wait_time_sell_delay_high" max="21600" min="1" class="form-control" value="<?php if(isset($config_options['wait_time_sell_delay_high'])) { echo $config_options['wait_time_sell_delay_high']; } else { echo '900'; } ?>" placeholder="Delay. E.g.: 900" required /></p>
									<br />
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<hr />
									<h2>LOW LIMIT ADJUST</h2>
									<div class="row" style="display:none;">
										<div class="col-md-4">
											<label>Sell All Delay - Low Base</label>
											<p>The base used for adjusting the delay with the extra long RSI. Will calculate the new sell all delay using this base amount of seconds and multiplying by the extra long RSI.</p>
											<p><input type="number" step="1" name="wait_time_sell_delay_low_base" max="21600" min="1" class="form-control" value="<?php if(isset($config_options['wait_time_sell_delay_low_base'])) { echo $config_options['wait_time_sell_delay_low_base']; } else { echo '900'; } ?>" placeholder="Delay. E.g.: 900" required /></p>
											<br />
										</div>
										<div class="col-md-4">
											<label>Sell All Delay - Low Adjust</label>
											<p>Adjust the sell all delay in function of the extra long RSI calculated on 5 minutes of half a day minutes. The shorter the RSI and the shorter the sell all low limit.</p>
											<input type="hidden" name="calculate_wait_delay_low" value="0" />
											<p><input type="checkbox" name="calculate_wait_delay_low" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['calculate_wait_delay_low']) && $config_options['calculate_wait_delay_low'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
										<div class="col-md-4">
											<label>Sell All Delay - Stagnancy</label>
											<p>Block the selling when it reaches the sell all delay but the long RSI indicates that the price is stagnant. Will unblock whenever the long RSI goes below the bottom limit.</p>
											<input type="hidden" name="sell_all_delay_stagnant" value="0" />
											<p><input type="checkbox" name="sell_all_delay_stagnant" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['sell_all_delay_stagnant']) && $config_options['sell_all_delay_stagnant'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
									</div>
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-12">
									<hr />
									<h2>EXPERIMENTAL MODE</h2>
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>Activate</label>
											<p>Each purchase will be registered and a selling will be issued whenever the price goes back to the original transaction price. The operation is done each seconds. The price limit has to be activated and selling must be blocked for this to work.</p>
											<input type="hidden" name="precise_selling" value="0" />
											<p><input type="checkbox" name="precise_selling" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['precise_selling']) && $config_options['precise_selling'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<label>Sell All - Extra Long RSI</label>
													<p>Sell all transactions when Extra Long RSI goes below the next input value.</p>
													<input type="hidden" name="extra_long_rsi_selling" value="0" />
													<p><input type="checkbox" name="extra_long_rsi_selling" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['extra_long_rsi_selling']) && $config_options['extra_long_rsi_selling'] == 1) { echo "checked='checked'"; } ?> /></p>
													<br />
												</div>
												<div class="col-md-6">
													<label>Extra Long RSI - Limit</label>
													<p>The limit for the Extra Long RSI to sell all transactions when it's reached.</p>
													<p><input type="number" step="1" max="100" min="0" name="extra_long_rsi_selling_limit" class="form-control" value="<?php if(isset($config_options['extra_long_rsi_selling_limit'])) { echo $config_options['extra_long_rsi_selling_limit']; } else { echo '60'; } ?>" placeholder="Extra Long RSI Limit E.g.: 40" required /></p>
													<br />
												</div>
											</div>
										</div>
									</div>
									<div class="row" style="display:none;">
										<div class="col-md-6">
											<label>Disable Low Limit Selling</label>
											<p>This setting will disable the low limit selling in case you want to play with risk. You can set the extreme bearish peanuts mode to sell when it's above.</p>
											<input type="hidden" name="disable_low_limit" value="0" />
											<p><input type="checkbox" name="disable_low_limit" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['disable_low_limit']) && $config_options['disable_low_limit'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
										<div class="col-md-6">
											<label>Extreme Bearish Peanuts</label>
											<p>Will only sell after each transaction has obtained a few additional dollars. This will gain peanuts over time. Need precise selling enabled to work.</p>
											<input type="hidden" name="extreme_bearish_peanuts" value="0" />
											<p><input type="checkbox" name="extreme_bearish_peanuts" class="form-control" style="width: 40px;" value="1" <?php if(isset($config_options['extreme_bearish_peanuts']) && $config_options['extreme_bearish_peanuts'] == 1) { echo "checked='checked'"; } ?> /></p>
											<br />
										</div>
									</div>
									<div class="row" style="display:none;">
										<div class="col-md-4">
											<label>Desired Amount</label>
											<p>Desired amount for each transaction before selling. This is part of the experimental mode. E.g.: 0.5 for half a dollar</p>
											<p><input type="number" step="0.01" max="20" min="0.01" name="desired_amount" class="form-control" value="<?php if(isset($config_options['desired_amount'])) { echo $config_options['desired_amount']; } else { echo '0.5'; } ?>" placeholder="Desired Gain. E.g.: 0.5" required /></p>
											<br />
										</div>
										<div class="col-md-4">
											<label>Sell Time Delay</label>
											<p>Sell the transaction funds after X amount of seconds when it's below the price of which it was purchased.</p>
											<p><input type="number" step="1" max="1200" min="0" name="desired_sell" class="form-control" value="<?php if(isset($config_options['desired_sell'])) { echo $config_options['desired_sell']; } else { echo '60'; } ?>" placeholder="Sell Time Delay. E.g.: 600" required /></p>
											<br />
										</div>
										<div class="col-md-4">
											<label>Sell after X Non-Buy</label>
											<p>Sell after X amount of non-buy indicator returned with the past short, long and extra long ranges, and ratio.</p>
											<p><input type="number" step="1" max="20" min="0" name="desired_nonbuy" class="form-control" value="<?php if(isset($config_options['desired_nonbuy'])) { echo $config_options['desired_nonbuy']; } else { echo '1'; } ?>" placeholder="Sell after X Non-Buy. E.g.: 1" required /></p>
											<br />
										</div>
									</div>
								</div>
							</div>
							<input type="submit" value="Submit" class="btn btn-success" style="margin: 0px;" />
						</form>
					</div>
					<div class="col-md-1"></div>
				</div>
			</div>
			<?php } else { ?>
			<div class="container-fluid">
				<div class="row" style="display:none;">
					<div class="col-md-1"></div>
					<div class="col-md-10">
						<p class="text-danger">You tried to log in therefore the system will only accept a valid login.</p>
						<form action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" name="user_login" method="post" class="user_login">
							<p>Connect with an existing USER ID to access your property</p>
							<div class="row" style="display:none;">
								<div class="col-md-10"><input type="text" class="form-control" name="user_id" placeholder="USER ID login to update settings" /></div>
								<div class="col-md-2" style="text-align: right;"><input type="submit" value="Submit" class="btn btn-success" style="margin: 0px;"></div>
							</div>
						</form>
					</div>
					<div class="col-md-1"></div>
				</div>
			</div>
			<?php } } else { ?>
			<div class="container-fluid">
				<div class="row" style="display:none;">
					<div class="col-md-1"></div>
					<div class="col-md-10">
						<?php if (isset($error) && !empty($error)){ ?>
							<p class="text-danger"><?php echo $error; ?></p>
						<?php } ?> 
						
						<p>If you choose to log in the system won't bring you back here to create a new user after your first try.</p>
						<form action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" name="user_login" method="post" class="user_login">
							<p>Connect with an existing USER ID to access your property</p>
							<div class="row" style="display:none;">
								<div class="col-md-10"><input type="text" class="form-control" name="user_id" placeholder="USER ID login to update settings" /></div>
								<div class="col-md-2" style="text-align: right;"><input type="submit" value="Submit" class="btn btn-success" style="margin: 0px;" /></div>
							</div>
						</form>
			
						<h3>OR</h3>
						
						<form action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" name="user_add" method="post" class="user_add">
							<p>The username must only contains letters and digits. Please choose something hard to guess.</p>
							<div class="row" style="display:none;">
								<div class="col-md-10"><input type="text" class="form-control" name="user_add" placeholder="Create USER ID login to add new property" /></div>
								<div class="col-md-2" style="text-align: right;"><input type="submit" value="Submit" class="btn btn-success" style="margin: 0px;" /></div>
							</div>
						</form>
					</div>
					<div class="col-md-1"></div>
				</div>
			</div>
			<?php } ?>
			<br />
			<br />
			<br />
			<?php include "includes/footer.php"; ?>
			<br />
		<?php } ?>
	</body>
</html>	
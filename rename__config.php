<?php
	// Rename rename__config.php file to config.php
	// Move file up -> sessions/data/move_out -> sessions/data
	// Move file up -> crypto/move_out -> crypto
	
	date_default_timezone_set('America/New_York');

	$default_stock = 'bitcoin_USD';
	$connect = '';
	
	$stocks_array = array(
		'BTC' => 'bitcoin_USD', 
		'ETH' => 'etherum_USD', 
		'COMP' => 'compound_USD',
		'GOOG' => 'alphabet_USD'
	);
	
	$stocks_function = array(
		'BTC' => 'CRYPTO_INTRADAY', 
		'ETH' => 'CRYPTO_INTRADAY', 
		'COMP' => 'CRYPTO_INTRADAY',
		'GOOG' => 'TIME_SERIES_INTRADAY'
	);
	
	$data_key = array(
		'BTC' => 'Time Series Crypto (1min)', 
		'ETH' => 'Time Series Crypto (1min)', 
		'COMP' => 'Time Series Crypto (1min)',
		'GOOG' => 'Time Series (1min)'
	);
	
	$column_multiply = array(
		'bitcoin_USD' => array(
			'yearly_days.php' => 15,
			'monthly_days.php' => 1,
			'weekly_days.php' => 1,
			'yearly.php' => 4,
			'monthly.php' => 4,
			'weekly.php' =>	4,
			'daily.php' =>	1
		),
		'etherum_USD' => array(
			'yearly_days.php' => 90,
			'monthly_days.php' => 3,
			'weekly_days.php' => 3,
			'yearly.php' => 40,
			'monthly.php' => 40,
			'weekly.php' =>	40,
			'daily.php' =>	15	
		),
		'compound_USD' => array(
			'yearly_days.php' => 40,
			'monthly_days.php' => 4,
			'weekly_days.php' => 6,
			'yearly.php' => 40,
			'monthly.php' => 40,
			'weekly.php' =>	40,
			'daily.php' => 15	
		),
		'alphabet_USD' => array(
			'yearly_days.php' => 60,
			'monthly_days.php' => 8,
			'weekly_days.php' => 10,
			'yearly.php' => 20,
			'monthly.php' => 20,
			'weekly.php' =>	20,
			'daily.php' => 20			
		)
	);
	
	$api_key = ""; // Alpha-Vantage API key
	$login = 'login';
	$password = 'password';
	$document_root = '/var/www/example.com/public_html/';
	
	//YWxleC9eKHF1YXJ0emNtcykkLw==
	$access_code = isset($_GET['access_code']) ? $_GET['access_code'] : 0;
	if(strval($access_code) == strval(base64_encode($login.$password))){
		$_SESSION['logged_in'] = 1;
	}
?>
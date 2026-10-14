<?php

$path = "../";
include $path."config.php";
$path = $document_root;
require '../vendor/autoload.php';

ini_set('display_errors', 1);

$crypto = array_keys($stocks_array, $default_stock);
$crypto_folder = $default_stock;

if(isset($_POST['crypto'])){
	$crypto = array_keys($stocks_array, $_POST['crypto']);
	$crypto_folder = $_POST['crypto'];
}
$crypto = $crypto[0];

include('../sessions/save_sessions.php');
include('../sessions/use_sessions.php');
$default_timezone = 'America/New_York';

function increase_risk($connect, $crypto, $folder, $date_object){
	$date_addition = $date_object->add(new DateInterval('PT1M'));
	$current_date = $date_addition->format('Hi');
	$file_content = file_get_contents($folder.'data_btc.txt');
	$lines = explode("\n", $file_content);
	if(count($lines) > 0){
		foreach($lines as $key => $line){
			$line_parts = explode(':', $line);
			if(isset($line_parts[0]) && isset($line_parts[1]) && isset($line_parts[2]) && $line_parts[0] == $current_date.'00') {
				write_session('base.txt_next_up'.$crypto, $line_parts[0].':'.$line_parts[1].':'.$line_parts[2]);
				break;
			}
		}
	}
}

$current_date = new DateTime();
$current_date->setTimezone(new DateTimeZone($default_timezone));
$current_date->sub(new DateInterval('PT1M'));
$date_formatted = $current_date->format('Hi');
$date_formatted = $date_formatted.'00';

$current_price = 0;
$file_content = file_get_contents('../crypto/'.$stocks_array[$crypto].'/data_daily/data_btc.txt');
$lines = explode("\n", $file_content);
foreach($lines as $line){
	$line_parts = explode(':', $line);
	if(isset($line_parts[0]) && isset($line_parts[4]) && $line_parts[0] == $date_formatted){
		$current_price = floatval($line_parts[4]);
		break;
	}
}

$timeframe = array();

if(use_session('base.txt_next_up'.$crypto) == ''){
	write_session('base.txt_next_up'.$crypto, 0);
}
if(use_session('base.txt_current'.$crypto) == ''){
	write_session('base.txt_current'.$crypto, $current_price);
} else {
	write_session('base.txt_old'.$crypto, use_session('base.txt_current'.$crypto));
	write_session('base.txt_current'.$crypto, $current_price);
	$timeframe[$date_formatted] = use_session('base.txt_current'.$crypto) - use_session('base.txt_old'.$crypto);
	
	increase_risk($connect, $crypto, $path.'crypto/'.$crypto_folder.'/data_daily/', $current_date);
}

if(use_session('base.txt_next_up'.$crypto) != 0){
	$next_up_parts = explode(':', use_session('base.txt_next_up'.$crypto));
	if(isset($next_up_parts[0]) && isset($next_up_parts[1]) && isset($next_up_parts[2])){
		$next_up_time = $next_up_parts[0];
		$next_up_risk = $next_up_parts[1];
		$next_up_amount = $next_up_parts[2];
	}
}

if(isset($_POST['crypto'])){
	if(!empty($next_up_time)){
		echo json_encode(array('current' => $current_price, 'timemark' => $date_formatted, 'next_up0' => $next_up_time, 'next_up1' => $next_up_risk, 'next_up2' => $next_up_amount));
	} else {
		echo json_encode(array('current' => $current_price, 'timemark' => $date_formatted, 'next_up0' => '0', 'next_up1' => '0', 'next_up2' => '0'));
	}
}

exit;
?>
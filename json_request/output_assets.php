<?php
ini_set('display_errors', 1);

function dirToArray($dir) {
	$result = array();
	$cdir = scandir($dir);
	foreach ($cdir as $key => $value){
		if(!in_array($value,array(".",".."))){
			if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
				$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
			} else {
				$result[] = $value;
			}
		}
	}
	return $result;
}

function sell_buy($connect, $options_data){
	$api_key = use_session('config_'.$_POST['password'].'.txt_api_key');
	if($api_key == ''){
		return false;
	}
	$coin_array = use_session($_POST['password'].'.txt'.'_coin_array');
	$price_array_list = use_session($_POST['password'].'.txt'.'_price_array_list');
	$build_value = array();
	
	if(!empty($coin_array) && !empty($price_array_list)){
		foreach($coin_array as $key => $value){
			$price_array_name = $coin_array[$key]['name'];
			if(isset($price_array_list[$price_array_name])){
				$actual_price = $price_array_list[$price_array_name];
				$decimal = explode('.', $actual_price);
				$rounded_price = (strval($actual_price) < '0') ? round($actual_price, intval(strlen($decimal[1]) - 1)) : round($actual_price, -(intval(strlen($decimal[0])) - 1));
			
				$build_value[$coin_array[$key]['name']]['value'] = (strval($rounded_price) > '0') ? (($coin_array[$key]['value']) / $rounded_price) : (($coin_array[$key]['value']) / 1);
				$build_value[$coin_array[$key]['name']]['label'] =  "Current: ".number_format(($coin_array[$key]['value'] * $price_array_list[$price_array_name]), 2, '.', ''). "<br />Rounded Price: ".$rounded_price;
			}
		}
	}

	return array('build_value' => $build_value);
}

include('../sessions/save_sessions.php');
include('../sessions/use_sessions.php');
include('../config.php');
$path = $document_root;

$folder_config = dirToArray($path.'config');
asort($folder_config);
foreach($folder_config as $key2 => $value2){
	if(!is_array($value2) && $value2 != '.htaccess'){
		$get_price = sell_buy($connect, array());
		echo json_encode($get_price);
		break;
	}
}

?>
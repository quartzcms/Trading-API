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
	$buy_price = use_session('config_'.$_POST['password'].'.txt_buy_price');
	$total_money = use_session('config_'.$_POST['password'].'.txt_total_money');
	$trigger_lowest_price = use_session($_POST['password'].'.txt_trigger_lowest_price');
	$price_total_median = use_session($_POST['password'].'.txt_price_total_median');
	$percent_stagnant = use_session('config_'.$_POST['password'].'.txt_percent_stagnant');
	$selling_fee = use_session('config_'.$_POST['password'].'.txt_selling_fee');
	$buying_fee = use_session('config_'.$_POST['password'].'.txt_buying_fee');
	$desired_amount_extra = use_session('config_'.$_POST['password'].'.txt_desired_amount');

	if($api_key != ''){
		$buy_balance = 1;
		$sell_balance = 1;
		$buy_price = use_session($_POST['password'].'.txt'.'_current_price_value');
		$buy_balance = use_session($_POST['password'].'.txt_buy_balance');
		$buy_balance = ($buy_balance != '') ? $buy_balance : 1;
		$sell_balance = use_session($_POST['password'].'.txt_sell_balance');
		$get_total = floatval(use_session($_POST['password'].'.txt_price_today'));
		$total_money_logs = floatval(use_session($_POST['password'].'.txt_price_total_from_today'));
		$total_money_difference = floatval(use_session($_POST['password'].'.txt_total_money_difference'));
		$sold_at = use_session($_POST['password'].'.txt_price_value');
		$sold_at = ($sold_at != '') ? $sold_at : 0;
		$highest = use_session($_POST['password'].'.txt_highest_today');
		$highest = ($highest != '') ? $highest : 0;
		$lowest = use_session($_POST['password'].'.txt_lowest_today');
		$lowest = ($lowest != '') ? $lowest : 0;
		$today_range = $highest - $lowest;
		$price_increase = use_session('config_'.$_POST['password'].'.txt_price_increase');
		$rsi_top = use_session('config_'.$_POST['password'].'.txt_rsi_top');
		$rsi_bottom = use_session('config_'.$_POST['password'].'.txt_rsi_bottom');
		$rsi_bottom_long = use_session('config_'.$_POST['password'].'.txt_rsi_bottom_long');
		$price_reached = use_session('config_'.$_POST['password'].'.txt_price_reached');
		$price_reached_added = $price_reached + $price_increase;
		$top_level_trigger = use_session($_POST['password'].'.txt_top_level_trigger');
		$operate = use_session($_POST['password'].'.txt_operate');
		$enough_money = use_session($_POST['password'].'.txt_enough_money');
		$time_lowest_limit = use_session($_POST['password'].'.txt_time_lowest_limit');
		
		$price_sell_all = '';
		if($_POST['detect_lowest'] == 1){
			if($_POST['detect_lowest_switch'] == 'off'){
				write_session($_POST['password'].'.txt_price_reached_block_buy_start', 0);
				write_session($_POST['password'].'.txt_highest_today_sell', $buy_price);
			} elseif($_POST['detect_lowest_switch'] == 'on') {
				write_session($_POST['password'].'.txt_price_reached_block_buy_start', 1);
				write_session($_POST['password'].'.txt_highest_today_sell', $buy_price);
			}
			write_session($_POST['password'].'.txt_trigger_lowest_price', 0);
			write_session($_POST['password'].'.txt_time', time());
		}
		
		$toggle_price_reached_sell_all = use_session($_POST['password'].'.txt_toggle_price_reached_sell_all');
		if($toggle_price_reached_sell_all == ''){
			$toggle_price_reached_sell_all = 0;
		}
		
		if($_POST['sell_unlock'] == 1){
			$remove_to_price = ($_POST['sell_unlock_minus_10'] == 1) ? 0.10 * $price_increase : 0;
			$remove_to_price = ($_POST['sell_unlock_minus_20'] == 1) ? 0.20 * $price_increase : $remove_to_price;
			write_session('config_'.$_POST['password'].'.txt_price_reached', ($buy_price + $remove_to_price));
			$toggle_price_reached_sell_all = 0;
		}
		
		if($_POST['sell_all'] == 1 && $toggle_price_reached_sell_all == 0){
			$add_to_price = ($_POST['sell_all_plus_10'] == 1) ? 0.10 * $price_increase : 0;
			$add_to_price = ($_POST['sell_all_plus_20'] == 1) ? 0.20 * $price_increase : $add_to_price;
			write_session('config_'.$_POST['password'].'.txt_price_reached', ($buy_price - $price_increase) - $add_to_price);
			$toggle_price_reached_sell_all = 1;
			write_session($_POST['password'].'.txt_price_position', 'none');
		}
		
		$ratio = number_format($sell_balance / $buy_balance, 1, '.', '');
		if(((use_session($_POST['password'].'.txt_price_position') == '' || use_session($_POST['password'].'.txt_price_operate') == 1) && 
		$toggle_price_reached_sell_all == 1) ||
		use_session('config_'.$_POST['password'].'.txt_sell_all_lock_high') == 0 ||
		use_session('config_'.$_POST['password'].'.txt_price_switch') == 0){
			$toggle_price_reached_sell_all = 0;
			$price_sell_all = 'done';
		}
		write_session($_POST['password'].'.txt_toggle_price_reached_sell_all', $toggle_price_reached_sell_all);
		
		$self_adjust_minutes = use_session('config_'.$_POST['password'].'.txt_self_adjust_minutes');
		$self_adjust_minutes = ($self_adjust_minutes != '') ? $self_adjust_minutes : 10;
		
		$step_one_extra_long = use_session($_POST['password'].'.txt_rsi_extra_long');
		$step_one_extra_long = ($step_one_extra_long == '') ? 50 : $step_one_extra_long;
		$step_one_long = use_session($_POST['password'].'.txt_rsi_long');
		$step_one_long = ($step_one_long == '') ? 50 : $step_one_long;
		$step_one = use_session($_POST['password'].'.txt_rsi');
		$step_one = ($step_one == '') ? 50 : $step_one;
		$detect_count = 'no';
		if(strval($step_one) < strval($rsi_top) && strval($step_one) > strval($rsi_bottom)){
			$detect_count = 'yes';
		}
		
		$range_stagnant_extra_long = number_format($step_one_extra_long, 2, '.', '');
		$range_stagnant_long = number_format($step_one_long, 2, '.', '');
		$range_stagnant = number_format($step_one, 2, '.', '');
		
		if(strval(use_session($_POST['password'].'.txt_price_ai')) > '0' && strval($buy_price) > '0'){
			$current_range_ai = $buy_price - use_session($_POST['password'].'.txt_price_ai');
		}
		
		$total_money = (-($total_money) - $total_money_difference) + $total_money_logs;
		$total_money_logs = $total_money_logs - $total_money_difference;
		$percent = ($get_total / ($buy_balance + $sell_balance)) * 100;
		
		$estimation = number_format((($sell_balance / $buy_price) * $price_reached_added), 2, '.', '');
		
		$array_server_time = use_session($_POST['password'].'.txt_server_time_average');
		$array_server_time = !empty($array_server_time) ? $array_server_time : array(1);
		$server_time_average = 0;
		if(!empty($array_server_time)){
			foreach($array_server_time as $key => $value){
				$server_time_average = $server_time_average + $value;
			}
			$server_time_average = $server_time_average / count($array_server_time);
		}
		$server_time = number_format($server_time_average, 2, '.', '');
		
		$transactions = array();
		$space = '';
		$align = 'center';
		$col_md = 'inline-block';
		$price_array = use_session($_POST['password'].'.txt_price_array');
		
		if(!empty($price_array) && is_array($price_array)){
			$i = 0;			
			if(count($price_array) > 1){
				$col_md = 'col-md-6';
			}
			if(count($price_array) > 2){
				$col_md = 'col-md-4';
			}
			if(count($price_array) > 3){
				$col_md = 'col-md-3';
			}
			
			foreach($price_array as $key => $value){
				$array_sell = array(
					'binance_' => 'quantity',
					'coinbase_' => 'size',
					'kraken_' => 'volume'
				);
				foreach($array_sell as $key2 => $value2){
					if(strpos($_POST['password'], $key2) !== false){
						$desired_amount = ((($price_array[$key]['query'][$value2])) * $price_array[$key]['price']) + $desired_amount_extra;
						$starting_amount = (($price_array[$key]['query'][$value2]) * $price_array[$key]['price']);
						$current_amount = ($price_array[$key]['query'][$value2] * $buy_price);
						$total_tx = number_format(((($current_amount - $starting_amount) - ($current_amount * $selling_fee)) - ($starting_amount * $buying_fee)), 2, '.', '');
						$selling_price = ((($desired_amount + ($desired_amount * $selling_fee)) + ($starting_amount * $buying_fee)) / ($starting_amount)) * $price_array[$key]['price'];
						$transactions[$total_tx.$i] = '<div align="'.$align.'" class="'.$col_md.'"><p><span class=\'text-success\'>ID: '.$price_array[$key]['txid'].'</span> <span class=\'text-warning\'>INI: '.number_format($price_array[$key]['price'], 2, '.', '').'</span> <span class=\'text-success\'>QTY: '.number_format($starting_amount, 2, '.', ''). '</span> <span class=\'text-info\'>RES: '.(strval($total_tx) > '0' ? '+'.$total_tx : $total_tx).'</span> <span class=\'text-danger\'>SELL: '.number_format($selling_price, 2, '.', '').'</span></p></div>';
					}
				}
				
				$i++;
			}
		}
		
		if(!empty($transactions)){
			krsort($transactions);
		}
		
		$array_possible = array();
		$array_possible['rsi'] = 'no';
		$array_possible['price'] = 'no';
		$array_possible['level'] = 'no';
		$array_possible['time'] = 'no';
		$array_possible['money'] = 'no';
		
		if(strval($step_one_long) < strval($rsi_bottom_long)){
			$array_possible['rsi'] = 'yes';
		}
		if(strval($price_reached) < strval($buy_price)){
			$array_possible['price'] = 'yes';
		}
		if($top_level_trigger == 'yes'){
			$array_possible['level'] = 'yes';
		}
		if($operate == 0){
			$array_possible['time'] = 'yes';
		}
		if($enough_money == 1){
			$array_possible['money'] = 'yes';
		}		

		return array('price' => $buy_price, 'price_sell' => number_format($sell_balance, 2, '.', ''), 'price_buy' => number_format($buy_balance, 2, '.', ''), 'total' => number_format($buy_balance + $sell_balance, 2, '.', ''), 'difference' => number_format($total_money, 2, '.', ''), 'total_today' => number_format($get_total, 2, '.', ''), 'price_sell_all' => $price_sell_all, 'sold_at' => number_format($sold_at, 2, '.', ''), 'limit_high' => number_format($price_reached_added, 2, '.', ''), 'limit_low' => number_format($price_reached, 2, '.', ''), 'stagnant' => $detect_count, 'highest' => number_format($highest, 2, '.', ''), 'lowest' => number_format($lowest, 2, '.', ''), 'low_detect' => number_format($trigger_lowest_price, 2, '.', ''), 'in_logs' => number_format($total_money_logs, 2, '.', ''), 'range' => number_format($current_range_ai, 2, '.', ''), 'percent' => number_format($percent, 2, '.', ''), 'median' => number_format($price_total_median, 2, '.', ''), 'range_today' => number_format($today_range, 2, '.', ''), 'estimation' => $estimation, 'stats' => '<div class="row" align="'.$align.'">'.implode($space, $transactions).'</div>', 'range_stagnant' => $range_stagnant, 'range_stagnant_long' => $range_stagnant_long, 'range_stagnant_extra_long' => $range_stagnant_extra_long, 'server_time' => $server_time, 'possible' => $array_possible, 'time_lowest_limit' => $time_lowest_limit);
	}
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
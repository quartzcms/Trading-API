<?php

ini_set('display_errors', 1);

$path = "../";
include $path."config.php";
$path = $document_root;

$crypto2 = array();
$stocks = $stocks_array;

foreach($stocks as $key => $value){
	if(!is_dir($path.'crypto/'.$value)){
		mkdir($path.'crypto/'.$value);
	}
	if(!is_dir($path.'crypto/'.$value.'/data_daily')){
		mkdir($path.'crypto/'.$value.'/data_daily');
	}
	if(!is_dir($path.'crypto/'.$value.'/data_monthly')){
		mkdir($path.'crypto/'.$value.'/data_monthly');
	}
	if(!is_dir($path.'crypto/'.$value.'/data_weekly')){
		mkdir($path.'crypto/'.$value.'/data_weekly');
	}
	if(!is_dir($path.'crypto/'.$value.'/data_yearly')){
		mkdir($path.'crypto/'.$value.'/data_yearly');
	}
}

foreach($stocks as $key => $value){
	if(isset($_POST['crypto'])){
		if($_POST['crypto'] == $value){
			$crypto2[$key] = $value;
			break;
		}
	} else {
		$crypto2[$key] = $value;
	}
}

include('../sessions/save_sessions.php');
include('../sessions/use_sessions.php');
$default_timezone = 'America/New_York';

function dirToArray($dir) {
   $result = array();
   $cdir = scandir($dir);
   foreach ($cdir as $key => $value){
	  if (!in_array($value,array(".",".."))){
		 if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
			$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
		 }else{
			$result[] = $value;
		 }
	  }
   }
   return $result;
}

function create_files($connect, $key1, $folder, $data, $content, $data_type, $date_object, $current, $current_open, $high, $low, $volume){
	if(!file_exists($folder.'data_btc.txt')){
		$myfile = fopen($folder."data_btc.txt", "w") or die("Unable to open file! 123");
		if($content == ''){
			fwrite($myfile, "\n");
		} else {
			if(isset($data[$data_type]) && is_array($data[$data_type]) && !empty($data[$data_type])){
				foreach($data[$data_type] as $key => $value){
					$time = explode(' ', $key);
					if(isset($time[1])){
						$time_output = str_replace(':', '', $time[1]);
						if(isset($data[$data_type]) && isset($data[$data_type][$key - 1]) && is_array($data[$data_type][$key - 1]) && isset($data[$data_type][$key - 1]['4. close'])) {
							$total = floatval($value['4. close']) - floatval($data[$data_type][$key - 1]['4. close']);
						} else {
							$total = 0;
						}
						$line_insert = $time_output.":1:".$total.":".$current_open.":".$current.":".$high.":".$low.":".$volume."\n";
						fwrite($myfile, $line_insert);
					}
				}
			}
		}
		fclose($myfile);
	}
}

function insert_line($connect, $key1, $folder, $data, $date, $timeframe, $date_object, $current, $current_open, $high, $low, $volume){
	$data_output = file_get_contents($folder.'data_btc.txt');
	if($data_output != '' && strpos($data_output, $date.'00') === false && isset($timeframe[$date.'00']) && strval($timeframe[$date.'00']) < '500' && strval($timeframe[$date.'00']) > '-500'){
		if(!file_exists($folder.'data_btc.txt')){
			$myfile = fopen($folder."data_btc.txt", "w") or die("Unable to open file! 456");
		} else {
			$myfile = fopen($folder."data_btc.txt", "a") or die("Unable to open file! 789");
		}
		$txt = $date.'00'.":1:".$timeframe[$date.'00'].":".$current_open.":".$current.":".$high.":".$low.":".$volume."\n";
		fwrite($myfile, $txt);
		fclose($myfile);
	}
}

function increase_risk($connect, $key1, $date, $folder, $timeframe, $date_object, $current, $current_open, $high, $low, $volume){
	$date2 = $date_object->add(new DateInterval('PT1M'));
	$date2 = $date2->format('Hi');
	$data_output = file_get_contents($folder.'data_btc.txt');
	$lines = explode("\n", $data_output);
	if(is_array($lines) && count($lines) > 0){
		foreach($lines as $key => $value){
			$get_hour = explode(':', $value);
			if(isset($get_hour[0]) && isset($get_hour[1]) && isset($get_hour[2]) && $get_hour[0] == $date2.'00') {
				write_session('base.txt_next_up'.$key1, $get_hour[0].':'.$get_hour[1].':'.$get_hour[2]);
				break;
			}
		}
	}
	if(use_session('base.txt_next_up'.$key1)){
		$data_output = file_get_contents($folder.'data_btc.txt');
		$get_hour2 = explode(':', use_session('base.txt_next_up'.$key1));
		
		if(isset($timeframe[$date.'00'])){
			if(strval($timeframe[$date.'00']) < '500' && strval($timeframe[$date.'00']) > '-500'){
				$pattern = '/('.$date.'00):(\d):(.*)/i';
				$replacement = '${1}:${2}:'.$timeframe[$date.'00'].":".$current_open.":".$current.":".$high.":".$low.":".$volume;
				$data_output = preg_replace($pattern, $replacement, $data_output);
			}
			
			if(strval($timeframe[$date.'00']) > strval($get_hour2[2])) {
				if(strpos($data_output, $date.'00:3') !== false){
					$pattern = '/('.$date.'00):(\d):(.*)/i';
					$replacement = '${1}:1:${3}';
					$data_output = preg_replace($pattern, $replacement, $data_output);
				} 
				if(strpos($data_output, $date.'00:2') !== false){
					$pattern = '/('.$date.'00):(\d):(.*)/i';
					$replacement = '${1}:3:${3}';
					$data_output = preg_replace($pattern, $replacement, $data_output);
				}
				if(strpos($data_output, $date.'00:1') !== false){
					$pattern = '/('.$date.'00):(\d):(.*)/i';
					$replacement = '${1}:2:${3}';
					$data_output = preg_replace($pattern, $replacement, $data_output);
				}
			}
		}
		
		if(!empty($data_output)){
			$myfile = fopen($folder."data_btc.txt", "w") or die("Unable to open file! 999");
			fwrite($myfile, $data_output);
			fclose($myfile);
		}
	}
}

$current_array = array();
$date_array = array();
$nextup0_array = array();
$nextup1_array = array();
$nextup2_array = array();
$get_price = 0;

foreach($crypto2 as $key1 => $value1){
	$current_time = date('Hi');
	$current_time = intval(ltrim($current_time, '0'));
	if($current_time >= 800 && $current_time <= 1600){
		$crypto = $value1;
		$json = file_get_contents('https://www.alphavantage.co/query?function='.$stocks_function[$key1].'&symbol='.$key1.'&market=USD&interval=1min&apikey='.$api_key);
		$data = json_decode($json,true);
		
		if(isset($data['Meta Data']) && isset($data_key[$key1]) && isset($data[$data_key[$key1]])){
			$time_zone = 'America/New_York';
			
			if(is_array($data['Meta Data']) && !empty($data['Meta Data'])){
				foreach($data['Meta Data'] as $key3 => $value3){
					if($key3 != '' && strpos($key3, 'Time Zone') !== false){
						$time_zone = $value3;
						break;
					}
				}
			}
			
			$min = date('i');
			if(isset($data_key[$key1]) && isset($data[$data_key[$key1]]) && is_array($data[$data_key[$key1]])){
				foreach($data[$data_key[$key1]] as $key2 => $value2){
					$date_now = new DateTime($key2, new DateTimeZone($time_zone));
					$min_now = $date_now->format('i');
					if(intval($min - 1) == intval($min_now) && isset($value2['4. close']) && isset($value2['1. open'])){
						$current = floatval($value2['4. close']);
						$current_open = floatval($value2['1. open']);
						$high = floatval($value2['2. high']);
						$low = floatval($value2['3. low']);
						$volume = floatval($value2['5. volume']);
						$date_now->setTimezone(new DateTimeZone($default_timezone));
						break;
					}
				}
			}

			$files = dirToArray($path.'crypto/'.$crypto.'/data_weekly');
			$count_file = 0;
			asort($files);
			if(count($files) > 0){
				foreach($files as $key => $value){
					$count_file++;
				}
				if($count_file > 14){
					unlink($path.'crypto/'.$crypto.'/data_weekly/'.$files[0]);
				}
			}

			$files = dirToArray($path.'crypto/'.$crypto.'/data_monthly');
			asort($files);
			$count_file = 0;
			if(count($files) > 0){
				foreach($files as $key => $value){
					$count_file++;
				}
				if($count_file > 30){
					unlink($path.'crypto/'.$crypto.'/data_monthly/'.$files[0]);
				}
			}

			$files = dirToArray($path.'crypto/'.$crypto.'/data_yearly');
			asort($files);
			$count_file = 0;
			if(count($files) > 0){
				foreach($files as $key => $value){
					$count_file++;
				}
				if($count_file > 365){
					unlink($path.'crypto/'.$crypto.'/data_yearly/'.$files[0]);
				}
			}
			
			if(isset($current) && isset($current_open) && isset($high) && isset($low) && isset($volume) && isset($data_key[$key1])){
				$file_date = $date_now->format('Y-m-d');
				
				create_files($connect, $key1, $path.'crypto/'.$crypto.'/data_daily/', $data, '', $data_key[$key1], $date_now, $current, $current_open, $high, $low, $volume);
				create_files($connect, $key1, $path.'crypto/'.$crypto.'/data_weekly/_'.$file_date.'_', $data, '', $data_key[$key1], $date_now, $current, $current_open, $high, $low, $volume);
				create_files($connect, $key1, $path.'crypto/'.$crypto.'/data_monthly/_'.$file_date.'_', $data, '', $data_key[$key1], $date_now, $current, $current_open, $high, $low, $volume);
				create_files($connect, $key1, $path.'crypto/'.$crypto.'/data_yearly/_'.$file_date.'_', $data, '', $data_key[$key1], $date_now, $current, $current_open, $high, $low, $volume);
				$date = $date_now->format('Hi');

				$timeframe = array();
				
				if(!use_session('base.txt_next_up'.$key1)){
					write_session('base.txt_next_up'.$key1, 0);
				}
				
				if(!use_session('base.txt_current'.$key1)){
					$date3 = $date_now->sub(new DateInterval('PT1M'));
					$date3 = $date3->format('Hi');
					$data_output = file_get_contents($path.'crypto/'.$crypto.'/data_daily/data_btc.txt');
					$lines = explode("\n", $data_output);
					if(count($lines) > 0){
						foreach($lines as $key => $value){
							$get_current = explode(':', $value);
							if(isset($get_current[0]) && isset($get_current[2]) && $get_current[0] == $date3.'00') {
								$old = $get_current[2];
								break;
							}
						}
					}
					write_session('base.txt_old'.$key1, $old);
					write_session('base.txt_current'.$key1, $current);
				} else {
					write_session('base.txt_old'.$key1, use_session('base.txt_current'.$key1));
					write_session('base.txt_current'.$key1, $current);
				}
				
				$timeframe[$date.'00'] = use_session('base.txt_current'.$key1) - use_session('base.txt_old'.$key1);
				
				insert_line($connect, $key1, $path.'crypto/'.$crypto.'/data_daily/', $data, $date, $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
				insert_line($connect, $key1, $path.'crypto/'.$crypto.'/data_weekly/_'.$file_date.'_', $data, $date, $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
				insert_line($connect, $key1, $path.'crypto/'.$crypto.'/data_monthly/_'.$file_date.'_', $data, $date, $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
				insert_line($connect, $key1, $path.'crypto/'.$crypto.'/data_yearly/_'.$file_date.'_', $data, $date, $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
				increase_risk($connect, $key1, $date, $path.'crypto/'.$crypto.'/data_daily/', $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
				increase_risk($connect, $key1, $date, $path.'crypto/'.$crypto.'/data_weekly/_'.$file_date.'_', $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
				increase_risk($connect, $key1, $date, $path.'crypto/'.$crypto.'/data_monthly/_'.$file_date.'_', $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
				increase_risk($connect, $key1, $date, $path.'crypto/'.$crypto.'/data_yearly/_'.$file_date.'_', $timeframe, $date_now, $current, $current_open, $high, $low, $volume);
			}
		}
	}
}

include $path."json_request/output_transaction.php";
?>
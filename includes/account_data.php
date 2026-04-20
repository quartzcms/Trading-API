<?php 
ini_set('display_errors', 1);

function account_data($connect, $username, $folder_config, $date_object, $options_data, $path){
	$files = dirToArray('../config/logs/'.$_SESSION['user_in']);
	$total_money_withdrew = use_session('config_'.$_SESSION['user_in'].'.txt'.'_total_money_withdrew');
	$total_money_deposited = use_session('config_'.$_SESSION['user_in'].'.txt'.'_total_money_deposited');
	
	$total_money_withdrew = explode(',', $total_money_withdrew);
	$total_money_deposited = explode(',', $total_money_deposited);
	$build = [];
	$total_from_start = 0;
	
	foreach($files as $key => $file){
		$name = explode('_', $file);
		$get_total = file_get_contents($folder_config."/logs/".$_SESSION['user_in'].'/'.$file);
		
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
			$get_total_difference2 = 0;
			$filename = str_replace('-', '', $name[0]);
			
			foreach($total_money_withdrew as $key => $value){
				$parts = explode(':', $value);
				$parts_date = explode('-', $parts[0]);
				if(isset($parts_date[2]) && strlen($parts_date[2]) == 4){
					$parts[0] = $parts_date[2].'-'.$parts_date[1].'-'.$parts_date[0];
				}
				$parts[0] = str_replace('-', '', $parts[0]);
				if(intval($parts[0]) <= intval($filename)) {
					$get_total_difference = $get_total_difference - floatval($parts[1]);
				}
				if(intval($parts[0]) == intval($filename)) {
					$get_total_difference2 = $get_total_difference2 - floatval($parts[1]);
				}
			}
			
			foreach($total_money_deposited as $key => $value){
				$parts = explode(':', $value);
				$parts_date = explode('-', $parts[0]);
				if(isset($parts_date[2]) && strlen($parts_date[2]) == 4){
					$parts[0] = $parts_date[2].'-'.$parts_date[1].'-'.$parts_date[0];
				}
				$parts[0] = str_replace('-', '', $parts[0]);
				if(intval($parts[0]) <= intval($filename)) {
					$get_total_difference = $get_total_difference + floatval($parts[1]);
				}
				if(intval($parts[0]) == intval($filename)) {
					$get_total_difference2 = $get_total_difference2 + floatval($parts[1]);
				}
			}
			
			$total_from_start = $total_from_start + $get_total;
			
			$get_total =  number_format($get_total, 3, '.', '');
			$build[] = ['date' => $name[0], 'today' => ($get_total - $get_total_difference2), 'account' => floatval($total_from_start - $get_total_difference)];
		}
		
		
		if(!empty($get_total)){
			$lines = explode("\n", $get_total);
			$first_key = 0;
			$last_key = count($lines) - 2;
			$first = explode('=', $lines[$first_key]);
			$first = explode('|',$first[1]);
			$first = explode('/',$first[4]);
			$last = explode('=', $lines[$last_key]);
			$last = explode('|',$last[1]);
			$last = explode('/',$last[4]);
			
			$get_total =  floatval($last[1]) - floatval($first[1]);					
			$get_total =  number_format($get_total, 2, '.', '');
		}
		
	}
	echo json_encode($build);
	exit;
}

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

include('../sessions/save_sessions.php');
include('../sessions/use_sessions.php');
include('../config.php');
$path = $document_root;
$default_timezone = 'America/New_York';
session_start();

if(isset($_SESSION['user_in'])){
	$folder_config = dirToArray($path.'config');
	asort($folder_config);
	foreach($folder_config as $key2 => $value2){
		$date_now2 = new DateTime();
		$date_now2->setTimezone(new DateTimeZone($default_timezone));
		if(!is_array($value2) && $value2 != '.htaccess'){			
			account_data($connect, $value2, $path.'config', $date_now2, array(), $path);
		}
	}
}
?>
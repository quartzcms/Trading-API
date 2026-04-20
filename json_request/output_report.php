<?php
	session_start();

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
	
	$current_date = date('Y-m-d');
	$current_time = date('Hi');
	$current_second = date('s');
	
	if(isset($_SESSION['user_in'])){
		$file_content = file_get_contents('../config/logs/'.$_SESSION['user_in'].'/'.$current_date.'_'.$_SESSION['user_in'].'.txt');
		
		if(empty($file_content)){
			$refresh = true;
		} else {
			$refresh = false;
		}
		
		$lines = explode("\n", $file_content);
		asort($lines);
		$encode = array();
		$encode_none = array('value0' => array("today" => $current_date, "refresh" => $refresh, "date" => "", "price" => "", "data" => "", "future" => "", "columns" => "", "ratio" => "", "method" => "", "past" => "", "action" => "", "sub_method" => "", "amount" => "", "stagnant" => ""));
		$x = 0;
		foreach($lines as $key => $value){
			if(!empty($value)){
				$line_parts = explode('=', $value);
				$line_date = isset($line_parts[0]) ? explode('*', $line_parts[0]) : array();
				$date_time = isset($line_date[0]) ? explode(' ', $line_date[0]) : array();
				$time = isset($date_time[1]) ? explode(':', $date_time[1]) : array();

				if(isset($date_time[2]) && isset($line_date[0]) && isset($line_date[1]) && isset($time[0]) && isset($time[1]) && isset($time[2]) && (!isset($_SESSION['date_time']) && intval($current_time) == intval($time[0].$time[1])) || (isset($_SESSION['date_time']) && strval($_SESSION['date_time']) < strval($time[0].$time[1].$time[2].$date_time[2]))){
					$line_data = isset($line_parts[1]) ? explode('|', $line_parts[1]) : array();
					$json_transaction = isset($line_data[0]) ? $line_data[0] : 0;
					$median = isset($line_data[1]) ? $line_data[1] : 0;
					$past_range = isset($line_data[2]) ? $line_data[2] : 0;
					$action = isset($line_data[3]) ? $line_data[3] : 0;
					$amount = isset($line_data[4]) ? $line_data[4] : 0;
					$ratio = isset($line_data[5]) ? $line_data[5] : 0;
					$method = isset($line_data[6]) ? $line_data[6] : 0;
					$columns = isset($line_data[7]) ? $line_data[7] : 0;
					$sub_method = isset($line_data[8]) ? $line_data[8] : 0;
					$stagnant = isset($line_data[9]) ? $line_data[9] : 0;
					$encode['value'.$x] = array("today" => $current_date, "refresh" => $refresh, "date" => $line_date[0], "price" => $line_date[1], "data" => $json_transaction, "future" => $median, "columns" => $columns, "ratio" => $ratio, "method" => $method, "past" => $past_range, "action" => $action, "sub_method" => $sub_method, "amount" => $amount, "stagnant" => $stagnant);
					$x++;
					
					$_SESSION['date_time'] = $time[0].$time[1].$time[2].$date_time[2];
				}
			}
		}
		
		if(empty($encode)){
			echo json_encode(array("count" => $encode_none));
		} else {
			echo json_encode(array("count" => $encode));
		}
	} else {
		$refresh = false;
		$encode_none = array('value0' => array("today" => $current_date, "refresh" => $refresh, "date" => "", "price" => "", "data" => "", "future" => "", "columns" => "", "ratio" => "", "method" => "", "past" => "", "action" => "", "sub_method" => "", "amount" => "", "stagnant" => ""));
		echo json_encode(array("count" => $encode_none));
	}
?>
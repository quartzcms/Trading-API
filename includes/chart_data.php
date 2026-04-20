<?php

session_start();

include "../config.php";
include "../initialize.php";

function dirToArray($dir) {
	$result = array();
	if(is_dir($dir)){
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
	}
	return $result;
}

$files = dirToArray('../crypto/'.$crypto_folder.'/data_yearly');
$build = [];
foreach($files as $key => $file){
	$file_content = file_get_contents('../crypto/'.$crypto_folder.'/data_yearly/'.$file);
	$lines = explode("\n", $file_content);
	asort($lines);
	$date = explode('_', $file);
	$time = [];
	
	foreach($lines as $line){
		$line_parts = explode(':', $line);
		$hour = substr($line_parts[0], 0, 2);
		$minute = substr($line_parts[0], 2, 2);
		$second = substr($line_parts[0], 4, 6);
	
		$time['open'] = $line_parts[3];
		$time['close'] = $line_parts[4];
		$time['high'] = isset($line_parts[5]) ? $line_parts[5] : $line_parts[3];
		$time['low'] = isset($line_parts[6]) ? $line_parts[6] : $line_parts[4];
		$time['volume'] = isset($line_parts[7]) ? $line_parts[7] : 0;
		$time['date'] = $date[1].' '. $hour.':'.$minute.':'.$second;
	}
	$build[] = $time;
}
usort($build, function($y, $x){
	return $y['date'] > $x['date'];
});
echo json_encode($build);
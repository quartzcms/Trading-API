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
asort($files);
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
		$time['date'] = $date.' '. $hour.':'.$minute.':'.$second;
	}
	$build[] = $time;
}
echo json_encode($build);
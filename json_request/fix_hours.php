<?php

$path = "../";
include $path."config.php";
$path = $document_root;

//$stock = 'etherum_USD';

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

function folder($folder, $path, $stock){
	$files = dirToArray('../crypto/'.$stock.'/data_'.$folder);
	foreach($files as $key => $value){
		$data_output = file_get_contents($path.'crypto/'.$stock.'/data_'.$folder.'/'.$value);
		$value_explode = explode("\n", $data_output);
		$array_values = array();

		foreach($value_explode as $value2){
			if(!empty($value2)){
				$get_hour = explode(':', $value2);
				
				$hour = substr($get_hour[0], 0, 2);
				$minute = substr($get_hour[0], 2, 2);
				$second = substr($get_hour[0], 4, 6);
				
				$new_time = date("Hi", strtotime(date('Y-m-d')." ".$hour.":".$minute.":".$second. " - 240 minute")).'00';
				$array_values[] = $new_time.':'.$get_hour[1].':'.$get_hour[2].':'.$get_hour[3].':'.$get_hour[4];
			}
		}
		$myfile = fopen($path.'crypto/'.$stock.'/data_'.$folder.'/'.$value, "w") or die("Unable to open file! 456");
		fwrite($myfile, implode("\n", $array_values));
		fclose($myfile);
	}
}
folder('daily', $path, $stock);
folder('weekly', $path, $stock);
folder('monthly', $path, $stock);
folder('yearly', $path, $stock);

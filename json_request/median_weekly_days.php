<?php
	if(isset($_POST['crypto'])){
		$crypto = $_POST['crypto'];
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
		
		$current_date = date('Ymd');
		$amount = 0;
		$count = 0;
		$files = dirToArray('../crypto/'.$crypto.'/data_weekly');
		if(count($files) > 0){
			foreach($files as $key => $file){
				$date_from_name = str_replace('-', '', substr($file, 1, 10));
				if($date_from_name != '' && $current_date == $date_from_name){
					$file_content = file_get_contents('../crypto/'.$crypto.'/data_weekly/'.$file);
					$lines = explode("\n", $file_content);
					if(count($lines) > 0){
						foreach($lines as $line){
							$line_parts = explode(':', $line);
							if(isset($line_parts[2])){
								$amount = floatval($amount) + floatval($line_parts[2]);
							}
						}
					}
					$count++;
				}
			}
		}
		
		if(!is_numeric($amount)){
			$amount = 0;
		} elseif(is_nan($amount)){
			$amount = 0;
		}
		
		if(is_numeric($amount)){
			$amount = number_format($amount, 2, '.', '');
		}
		
		echo json_encode(array("current_date" => $current_date, "date" => $current_date, "amount" => $amount, "size" => $amount, 'files' => $count));
	}
?>
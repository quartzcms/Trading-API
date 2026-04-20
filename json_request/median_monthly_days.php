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
		
		$data = array();
		$count_days = array();
		$starting_date = array();
		$today = date('Ymd');
		
		$files = dirToArray('../crypto/'.$crypto.'/data_monthly');
		asort($files);
		$z = 1;
		$w = 1;
		if(count($files) > 0){
			foreach($files as $key => $value){
				$file_content = file_get_contents('../crypto/'.$crypto.'/data_monthly/'.$value);
				$date_current = str_replace('-','', substr($value, 1, 10));
				
				if($date_current != '' && intval($date_current) <= intval($today)){
					$lines = explode("\n", $file_content);
					asort($lines);
					if(count($lines) > 0){
						foreach($lines as $line){
							$line_keys = explode(':', $line);
							if(!empty($line) && isset($line_keys[2])){
								if(isset($data[$w])){
									$data[$w] = floatval($data[$w]) + floatval($line_keys[2]);
								} else {
									$data[$w] = floatval($line_keys[2]);
								}
							}
						}
					}
					
					if($date_current != ''){
						$starting_date[$w][] = $date_current;
					}
					if(isset($count_days[$w])){
						$count_days[$w]++;
					} else {
						$count_days[$w] = 1;
					}
					
					if($z == 7){
						$z = 0;
						$w++;
					}
					$z++;
				}
			}
			
			if(count($data) > 0){ 
				$amount = end($data);
			}
			$current_date = end($starting_date);
		}
		
		if(!is_numeric($amount)){
			$amount = 0;
		} elseif(is_nan($amount)){
			$amount = 0;
		}
		
		if(is_numeric($amount)){
			$amount = number_format($amount, 2, '.', '');
		}
		
		echo json_encode(array("current_date" => $current_date[0], "date" => $current_date[0], "amount" => $amount, "size" => $amount, 'files' => end($count_days)));
	}
?>
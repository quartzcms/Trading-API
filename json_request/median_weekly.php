<?php
	if(isset($_POST['crypto'])){
		$crypto = $_POST['crypto'];
		$date = date('Hi', strtotime('-1 minutes')).'00';
		$current_date = date('Hi').'00';
		
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
		
		$amount = 0;
		$count = 0;
		$files = dirToArray('../crypto/'.$crypto.'/data_weekly');
		if(count($files) > 0){
			foreach($files as $key => $file){
				$file_content = file_get_contents('../crypto/'.$crypto.'/data_weekly/'.$file);
				$lines = explode("\n", $file_content);
				if(count($lines) > 0){
					foreach($lines as $line){
						$line_parts = explode(':', $line);
						if(isset($line_parts[0]) && isset($line_parts[2]) && $line_parts[0] == $date){
							$amount = $amount + floatval($line_parts[2]);
							$count++;
							break;
						}
					}
				}
			}
			
			if($count != 0){
				$amout = floatval($amount) / $count;
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
		echo json_encode(array("current_date" => $current_date, "date" => $date, "amount" => $amount, "size" => $amount, 'files' => $count));
	}
?>
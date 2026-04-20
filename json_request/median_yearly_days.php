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
		
		$current_month = date('m');
		$amount = 0;
		$count = 0;
		$files = dirToArray('../crypto/'.$crypto.'/data_yearly');
		foreach($files as $key => $file){
			$month = substr($file, 6, 2);
			$month = intval($month);
			if($month != 0 && $current_month == $month){
				$file_content = file_get_contents('../crypto/'.$crypto.'/data_yearly/'.$file);
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
		
		if(!is_numeric($amount)){
			$amount = 0;
		} elseif(is_nan($amount)){
			$amount = 0;
		}
		
		$amount = number_format($amount, 2, '.', '');
		
		echo json_encode(array("current_date" => intval($current_month), "date" => intval($current_month), "amount" => $amount, "size" => $amount, 'files' => $count));
	}
?>
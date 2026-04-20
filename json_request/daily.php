<?php
	if(isset($_POST['crypto'])){
		$crypto = $_POST['crypto'];
		$date = date('Hi', strtotime('-1 minutes')).'00';
		$current_date = date('Hi').'00';
		
		$amount = 0;
		$file_content = file_get_contents('../crypto/'.$crypto.'/data_daily/data_btc.txt');
		$lines = explode("\n", $file_content);
		foreach($lines as $line){
			$line_parts = explode(':', $line);
			if(isset($line_parts[0]) && isset($line_parts[2]) && $line_parts[0] == $date){
				$amount = floatval($line_parts[2]);
				break;
			}
		}
		
		if(!is_numeric($amount)){
			$amount = 0;
		} elseif(is_nan($amount)){
			$amount = 0;
		}
		
		$amount = number_format($amount, 2, '.', '');
		
		echo json_encode(array("current_date" => $current_date, "date" => $date, "amount" => $amount, "size" => abs($amount)));
	}
?>
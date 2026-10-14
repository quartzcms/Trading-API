<?php
	require_once dirname(__DIR__) . '/core/bootstrap.php';

	if(isset($_POST['crypto'])){
		$crypto = $_POST['crypto'];
		$date = date('Hi', strtotime('-1 minutes')).'00';
		$current_date = date('Hi').'00';
		
		$summary = \Core\Market\MarketSummaryService::summarizeChangeAtTime('../crypto/'.$crypto.'/data_monthly', $date, true);
		
		echo json_encode(array("current_date" => $current_date, "date" => $date, "amount" => $summary['amount'], "size" => $summary['amount'], 'files' => $summary['files']));
	}
?>
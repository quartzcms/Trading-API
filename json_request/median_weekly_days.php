<?php
	require_once dirname(__DIR__) . '/core/bootstrap.php';

	if(isset($_POST['crypto'])){
		$crypto = $_POST['crypto'];
		
		$current_date = date('Ymd');
		$summary = \Core\Market\MarketSummaryService::summarizeFilesForDate('../crypto/'.$crypto.'/data_weekly', $current_date);
		echo json_encode(array("current_date" => $current_date, "date" => $current_date, "amount" => $summary['amount'], "size" => $summary['amount'], 'files' => $summary['files']));
	}
?>
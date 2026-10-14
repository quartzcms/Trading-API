<?php
	require_once dirname(__DIR__) . '/core/bootstrap.php';

	if(isset($_POST['crypto'])){
		$crypto = $_POST['crypto'];
		
		$current_month = date('m');
		$summary = \Core\Market\MarketSummaryService::summarizeFilesForMonth('../crypto/'.$crypto.'/data_yearly', $current_month);
		
		echo json_encode(array("current_date" => intval($current_month), "date" => intval($current_month), "amount" => $summary['amount'], "size" => $summary['amount'], 'files' => $summary['files']));
	}
?>
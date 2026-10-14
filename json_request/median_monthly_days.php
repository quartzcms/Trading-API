<?php
	require_once dirname(__DIR__) . '/core/bootstrap.php';

	if(isset($_POST['crypto'])){
		$crypto = $_POST['crypto'];
		
		$today = date('Ymd');
		$summary = \Core\Market\MarketSummaryService::summarizeGroupedFiles('../crypto/'.$crypto.'/data_monthly', $today, 7);
		echo json_encode(array("current_date" => $summary['date'], "date" => $summary['date'], "amount" => $summary['amount'], "size" => $summary['amount'], 'files' => $summary['files']));
	}
?>
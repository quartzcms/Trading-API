<?php

require_once dirname(__DIR__) . '/core/bootstrap.php';

ini_set('display_errors', 1);

include '../sessions/save_sessions.php';
include '../sessions/use_sessions.php';
include '../config.php';

if (\Core\Accounts\ConfigurationFileService::hasAccountFiles($document_root . 'config')) {
	$result = \Core\Market\PriceSummaryService::build($_POST['password'], $_POST);
	echo json_encode($result);
}

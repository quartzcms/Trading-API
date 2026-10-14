<?php
require_once dirname(__DIR__) . '/core/bootstrap.php';
ini_set('display_errors', 1);

include('../sessions/save_sessions.php');
include('../sessions/use_sessions.php');
include('../config.php');

if (\Core\Accounts\ConfigurationFileService::hasAccountFiles($document_root . 'config')) {
	$get_price = \Core\Market\AssetValuationService::build($_POST['password']);
	echo json_encode($get_price);
}

?>
<?php

ini_set('display_errors', '1');
session_start();

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../config.php';

$account_data = array();
if (isset($_SESSION['user_in'])) {
	$config_directory = rtrim($document_root, '/\\') . DIRECTORY_SEPARATOR . 'config';
	$account_data = \Core\Accounts\AccountDataService::build($_SESSION['user_in'], $config_directory);
}

echo json_encode($account_data);

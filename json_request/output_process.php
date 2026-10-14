<?php

require_once dirname(__DIR__) . '/core/bootstrap.php';
ini_set('display_errors', 1);

include '../sessions/save_sessions.php';
include '../sessions/use_sessions.php';
include '../config.php';

\Core\Market\MarketDataUpdateService::run(
	$connect,
	$document_root,
	$stocks_array,
	$stocks_function,
	$data_key,
	$api_key,
	'America/New_York',
	isset($_POST['crypto']) ? $_POST['crypto'] : null
);

require_once dirname(__DIR__) . '/vendor/autoload.php';
\Core\Trading\TransactionProcessor::run($connect, $document_root, 'America/New_York');

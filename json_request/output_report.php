<?php
require_once dirname(__DIR__) . '/core/bootstrap.php';
session_start();

$current_date = date('Y-m-d');
$current_time = date('Hi');
$has_session_date_time = isset($_SESSION['date_time']);
$session_date_time = $has_session_date_time ? $_SESSION['date_time'] : null;
$result = \Core\Reports\TradingLogResponseService::buildForUser(
	isset($_SESSION['user_in']) ? $_SESSION['user_in'] : null,
	$current_date,
	$current_time,
	$has_session_date_time,
	$session_date_time
);
if ($result['has_session_date_time']) {
	$_SESSION['date_time'] = $result['session_date_time'];
}

echo json_encode($result['response']);
?>
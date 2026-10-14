<?php

require_once dirname(__DIR__) . '/core/bootstrap.php';
ini_set('display_errors', 1);

include '../sessions/save_sessions.php';
include '../sessions/use_sessions.php';
include '../config.php';

\Core\Trading\AiTradingService::run($connect, $document_root, 'America/New_York');

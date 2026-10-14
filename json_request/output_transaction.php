<?php

require_once dirname(__DIR__) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

\Core\Trading\TransactionProcessor::run($connect, $path, $default_timezone);

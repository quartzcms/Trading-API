<?php

session_start();

include "../config.php";
include "../initialize.php";

$data = \Core\Market\ChartDataService::build('../crypto/' . $crypto_folder . '/data_yearly', false);
echo json_encode($data);
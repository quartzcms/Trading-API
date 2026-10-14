	<?php
	require_once __DIR__ . '/core/bootstrap.php';
	include "config.php";

	$initialization = \Core\Site\Initializer::initialize($stocks_array, $default_stock);
	$crypto_folder = $initialization['crypto_folder'];
	$clean_uri = $initialization['clean_uri'];
?>
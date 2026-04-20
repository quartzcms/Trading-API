<?php 
	include('sessions/save_sessions.php');
	include('sessions/use_sessions.php');	
	
	include "config.php";

	foreach($stocks_array as $key => $value){
		if(!is_dir('crypto/'.$value)){
			mkdir('crypto/'.$value);
		}
		if(!is_dir('crypto/'.$value.'/data_daily')){
			mkdir('crypto/'.$value.'/data_daily');
		}
		if(!is_dir('crypto/'.$value.'/data_monthly')){
			mkdir('crypto/'.$value.'/data_monthly');
		}
		if(!is_dir('crypto/'.$value.'/data_weekly')){
			mkdir('crypto/'.$value.'/data_weekly');
		}
		if(!is_dir('crypto/'.$value.'/data_yearly')){
			mkdir('crypto/'.$value.'/data_yearly');
		}
	}

	if(isset($_GET['crypto'])){
		$_SESSION['crypto'] = $_GET['crypto'];
	} 

	if(!isset($_SESSION['crypto'])){
		$_SESSION['crypto'] = $default_stock;
	}

	$crypto_folder = $_SESSION['crypto'];

	$clean_uri = explode('?', basename($_SERVER['REQUEST_URI']));
	$clean_uri = $clean_uri[0];

?>
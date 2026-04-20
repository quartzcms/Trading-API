<?php 

include('../sessions/save_sessions.php');
include('../sessions/use_sessions.php');

$type = $_POST['type'];
$username = $_POST['crypto'];

if($type == 'reveal'){
	if(use_session('config_'.$username.'.txt_hide') == 'hide'){
		write_session('config_'.$username.'.txt_hide', 'show');
	} else {
		write_session('config_'.$username.'.txt_hide', 'hide');
	}
} else {
	if(use_session('config_'.$username.'.txt_history') == 'hide'){
		write_session('config_'.$username.'.txt_history', 'show');
	} else {
		write_session('config_'.$username.'.txt_history', 'hide');
	}
}

header('Location: '.$_SERVER['HTTP_REFERER']);

?>
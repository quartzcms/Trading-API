<?php 
	function use_session($session_name) {
		$filename = explode('.txt_', $session_name);
		$var = @file_get_contents(__DIR__.'/data/'.$filename[0].'.txt/'.$filename[1].'.txt');
		$var = json_decode($var, true);
		$var = (is_numeric($var)) ? ((floor($var) != $var) ? (float)$var : (int)$var) : $var;
		return $var;
	}
?>
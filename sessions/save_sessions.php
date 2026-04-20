<?php
	function write_session($session_name, $session_content) {
		$encoded = json_encode($session_content);
		$filename = explode('.txt_', $session_name);
		$filepath = __DIR__.'/data/'.$filename[0].'.txt/'.$filename[1].'.txt';
		
		$content = @file_get_contents($filepath);
		if($content != $encoded){
			$write = fopen($filepath, 'w');
			if(flock($write, LOCK_EX)) {
				fwrite($write, $encoded);
				fflush($write);
				flock($write, LOCK_UN);
			}
			fclose($write);
		}
	}
?>
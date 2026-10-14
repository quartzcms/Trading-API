<?php

if (!function_exists('dirToArray')) {
	function dirToArray($directory)
	{
		return \Core\Support\Filesystem::directoryToArray($directory);
	}
}

if (!function_exists('use_session')) {
	function use_session($session_name)
	{
		return \Core\Support\SessionStore::read($session_name);
	}
}

if (!function_exists('write_session')) {
	function write_session($session_name, $session_content)
	{
		\Core\Support\SessionStore::write($session_name, $session_content);
	}
}

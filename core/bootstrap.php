<?php

spl_autoload_register(static function ($class_name) {
	$namespace_prefix = 'Core\\';
	if (strncmp($class_name, $namespace_prefix, strlen($namespace_prefix)) !== 0) {
		return;
	}

	$class_path = substr($class_name, strlen($namespace_prefix));
	$class_file = __DIR__ . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $class_path) . '.php';
	if (is_file($class_file)) {
		require_once $class_file;
	}
});

require_once __DIR__ . '/functions.php';

<?php

namespace Core\Site;

class Initializer
{
	public static function initialize($stocks_array, $default_stock)
	{
		// Anchored to the project root so scripts in includes/ resolve the same crypto folder.
		$crypto_root = dirname(__DIR__, 2) . '/crypto/';
		foreach ($stocks_array as $stock) {
			$stock_directory = $crypto_root . $stock;
			self::ensureDirectory($stock_directory);

			foreach (array('data_daily', 'data_monthly', 'data_weekly', 'data_yearly') as $data_directory) {
				self::ensureDirectory($stock_directory . '/' . $data_directory);
			}
		}

		if (isset($_GET['crypto'])) {
			$_SESSION['crypto'] = $_GET['crypto'];
		}

		if (!isset($_SESSION['crypto'])) {
			$_SESSION['crypto'] = $default_stock;
		}

		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$clean_uri = explode('?', basename($request_uri));

		return array(
			'crypto_folder' => $_SESSION['crypto'],
			'clean_uri' => $clean_uri[0]
		);
	}

	private static function ensureDirectory($directory)
	{
		if (is_dir($directory)) {
			return;
		}

		if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
			throw new \RuntimeException('Unable to create directory: ' . $directory);
		}
	}
}

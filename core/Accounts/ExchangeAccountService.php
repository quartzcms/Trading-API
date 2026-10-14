<?php

namespace Core\Accounts;

use Core\Support\Filesystem;
use Core\Support\SessionStore;

class ExchangeAccountService
{
	public static function create($exchange, $user_id)
	{
		if (!is_string($user_id) || $user_id === '' || preg_match('/[^A-Za-z0-9]/', $user_id)) {
			return array(
				'user' => null,
				'error' => 'The username is not valid or contains bad characters.'
			);
		}

		$username = $exchange . '_' . $user_id;
		$config_file = 'config/' . $username . '.txt';
		if (file_exists($config_file)) {
			return array('user' => null, 'error' => 'Your username is invalid.');
		}

		$file = fopen($config_file, 'w');
		if ($file === false) {
			throw new \RuntimeException('Unable to create the exchange account configuration file.');
		}

		try {
			if (fwrite($file, ' ') !== 1 || !fflush($file)) {
				throw new \RuntimeException('Unable to initialize the exchange account configuration file.');
			}
		} finally {
			fclose($file);
		}

		foreach (array(
			SessionStore::getDataDirectory() . DIRECTORY_SEPARATOR . 'config_' . $username . '.txt',
			'config/logs/' . $username,
			'config/stats/' . $username
		) as $directory) {
			if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
				throw new \RuntimeException('Unable to create the exchange account directory: ' . $directory);
			}
		}

		return array('user' => $username, 'error' => '');
	}

	public static function saveSettings($username, $post_data)
	{
		if (!isset($post_data['api_key']) || $post_data['api_key'] === '') {
			return;
		}

		$recommended_mode = isset($post_data['recommended_mode']) ? $post_data['recommended_mode'] : '';
		$recommended_files = $recommended_mode !== ''
			? Filesystem::directoryToArray(SessionStore::getDataDirectory() . DIRECTORY_SEPARATOR . 'config_' . $recommended_mode . '.txt')
			: array();

		foreach ($post_data as $key => $value) {
			if ($recommended_mode !== '' && in_array($key . '.txt', $recommended_files, true)) {
				continue;
			}

			SessionStore::write('config_' . $username . '.txt_' . $key, $value);
		}

		if ($recommended_mode === '') {
			return;
		}

		foreach ($recommended_files as $filename) {
			if (!is_string($filename)) {
				continue;
			}

			$setting_name = str_replace('.txt', '', $filename);
			$setting_value = SessionStore::read('config_' . $recommended_mode . '.txt_' . $setting_name);
			SessionStore::write('config_' . $username . '.txt_' . $setting_name, $setting_value);
		}
	}

	public static function loadSettings($username)
	{
		if (file_exists('config/' . $username . '.txt')) {
			$_SESSION['user_verified'] = 1;
		}

		$settings_directory = SessionStore::getDataDirectory() . DIRECTORY_SEPARATOR . 'config_' . $username . '.txt';
		$settings = array();
		foreach (Filesystem::directoryToArray($settings_directory) as $filename) {
			if (!is_string($filename)) {
				continue;
			}

			$setting_name = str_replace('.txt', '', $filename);
			$settings[$setting_name] = SessionStore::read('config_' . $username . '.txt_' . $setting_name);
		}

		return $settings;
	}
}

<?php

namespace Core\Trading;

class LogWriter
{
	public static function append($path, $line)
	{
		$bytes_written = file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
		if ($bytes_written === false || $bytes_written !== strlen($line)) {
			throw new \RuntimeException('Unable to append to the trading log: ' . $path);
		}
	}
}

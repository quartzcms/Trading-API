<?php
ini_set('display_errors', 1);

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();

$crypto = 'etherum_USD';

$spreadsheet = $reader->load($crypto."/import.xls");
$worksheet = $spreadsheet->getActiveSheet();

$table = array();

$j = 0;
foreach ($worksheet->getRowIterator() as $row) {
	$cellIterator = $row->getCellIterator();
	$cellIterator->setIterateOnlyExistingCells(FALSE);
	$i = 0;
	if($j > 0){
		foreach ($cellIterator as $cell) {
			if($i == 0){
				$date = new DateTime($cell->getValue());
				$stringDate = $date->getTimestamp();
				$file = "_".date("Y-m-d", $stringDate).'_data_btc.txt';
				$current_time = date("His", $stringDate);
				$table[$j][$i] = $current_time;
				$i++;
				$table[$j][$i] = $file;
			} else {
				$table[$j][$i] = $cell->getValue();
			}
			$i++;
		}
	}
	$j++;
}

$array_values = array();
$i = 0;
foreach($table as $key => $value){
	if(isset($table[$key][5]) && isset($table[$key - 1][5])){
		$range = floatval($table[$key][5]) - floatval($table[$key - 1][5]);
		$array_values[$table[$key][1].'_'.$i] = $table[$key][0].':1:'.$range.':'.$table[$key][2].':'.$table[$key][5];
	} else {
		$array_values[$table[$key][1].'_'.$i] = $table[$key][0].':1:0'.':'.$table[$key][2].':'.$table[$key][5];
	}
	$i++;
}

function create_entries($array_values, $crypto, $folder){
	foreach($array_values as $key => $value){
		$filename = substr($key, 1, 10);
		if(!file_exists('../crypto/'.$crypto.'/data_'.$folder.'/_'.$filename.'_data_btc.txt')){
			$myfile = fopen('../crypto/'.$crypto.'/data_'.$folder.'/_'.$filename.'_data_btc.txt', "w") or die("Unable to open file! 456");
		} else {
			$myfile = fopen('../crypto/'.$crypto.'/data_'.$folder.'/_'.$filename.'_data_btc.txt', "a") or die("Unable to open file! 789");
		}
		$txt = $value."\n";
		fwrite($myfile, $txt);
		fclose($myfile);
	}
}

create_entries($array_values, $crypto, 'weekly');
create_entries($array_values, $crypto, 'monthly');
create_entries($array_values, $crypto, 'yearly');

echo 'done';

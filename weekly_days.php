<?php 
	session_start();
	
	include "config.php";
	include "initialize.php";
	
	if(!isset($_SESSION['logged_in'])){
		header('Location: weekly.php');
	}
	
	if(isset($_POST['login']) && isset($_POST['password'])){
		if($_POST['login'] == $login && $_POST['password'] == $password){
			$_SESSION['logged_in'] = 1;
			header('Location: weekly.php');
		}
	}
?><!DOCTYPE html>
<html>
	<head>
		<title><?php $symbol = array_keys($stocks_array, $crypto_folder); echo $symbol[0].' - '.strtoupper(str_replace('_', ' ', $_SESSION['crypto'])); ?> - Graph</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<link rel="stylesheet" href="assets/bootstrap.min.css" />
		<script src="assets/jquery-3.6.0.min.js"></script>
		<link rel="stylesheet" href="assets/charts.min.css">
		<?php include "includes/header.php"; ?>
	</head>
	<body>
		<?php if (isset($_SESSION['logged_in'])){ ?>
			<div class="container-fluid">
				<div class="row" style="display:none;">
					<div class="col-md-12">
						<h1 align="center" style="text-transform: uppercase;" class="text-success">Weekly Days Graph</h1>
					</div>
				</div>
				<div class="row" style="display:none;">
					<div class="col-md-2">
						<div style="padding: 10px;" align="center">
							<h3>Miscellaneous</h3>
							<div class="buttons">
								<?php $current_date = date('Ymd'); ?>
								<span id="<?php echo $current_date; ?>" class="current-date btn btn-primary">Scroll to current</span>
							</div>
							<h4 align="center"><?php $files = dirToArray('crypto/'.$crypto_folder.'/data_weekly'); echo count($files); ?> day collected</h4>
							<h4 class="text-warning range-between">Est.: <span></span></h4>
						</div>
					</div>
					<div class="col-md-8">
						<?php include "includes/main.php"; ?>
					</div>
					<div class="col-md-2">
						<div style="padding: 10px;" align="center">
							<h3>Gold Period</h2>
							<p>Click on the gold labels to <br />scroll on the timeline.</p>
							<div class="periods">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div style="overflow-x:scroll; height: 390px; margin-top: 20px;" class="scroll">
				<table width="100%">
					<tr>
						<td>
							<table id="my-chart" class="charts-css column show-labels">
								 <tbody>
									<?php
										$data = array();
										$count = array();
										
										$files = dirToArray('crypto/'.$crypto_folder.'/data_weekly');
										asort($files);
										foreach($files as $key => $file){
											$file_content = file_get_contents('crypto/'.$crypto_folder.'/data_weekly/'.$file);
											$lines = explode("\n", $file_content);
											asort($lines);
											foreach($lines as $line){
												$line_parts = explode(':', $line);
												if(!empty($line)){
													if(isset($data[$file])){
														$data[$file] = floatval($data[$file]) + floatval($line_parts[2]);
													} else {
														$data[$file] = floatval($line_parts[2]);
													}
												}
											}
											if(isset($count[$file])){
												$count[$file]++;
											} else {
												$count[$file] = 1;
											}
										}
										
										$i = 1;
										$current_date = date('Ymd');
										ksort($data);
										foreach($data as $filename => $amount){
											
											if(!is_numeric($amount)){
												$amount = 0;
											} elseif(is_nan($amount)){
												$amount = 0;
											}
											
											$date_compressed = str_replace('-', '', substr($filename, 1, 10));
											$date_from_name = substr($filename, 1, 10);
											
											$amount = number_format($amount, 2, '.', '');
									?>
										<tr class="<?php if($i == 1){ echo "range-start"; } ?> <?php if(strval($amount) < '0'){ ?>green<?php } else { ?>red<?php } ?><?php if($date_compressed == $current_date){ ?> now<?php } ?>">
											<th scope="row" class="value" align="center"> 
												Day: <span class="time" time="<?php echo $date_compressed; ?>"><?php echo $date_from_name; ?></span>
												<?php if($amount >= 0){ ?>
												<span class="label label-danger range-tag range<?php echo $date_compressed; ?>" style="font-size: 1em;">
												<?php } else { ?>
												<span class="label label-success range-tag range<?php echo $date_compressed; ?>" style="font-size: 1em;">
												<?php } ?>
												Range: <strong><?php if(is_nan($amount)) { echo "0"; } else { echo $amount; } ?></strong></span>
												<span class="files<?php echo $date_compressed; ?>">Files #: <?php echo (isset($count[$filename]) ? $count[$filename] : 0); ?></span>
											</th> 
											<td position="<?php  echo ($i * 78) - 78; ?>" id="b<?php echo $date_compressed; ?>"  class="<?php if(strval($amount) > '0'){ ?>grey<?php } ?>" style="height:<?php echo ((abs($amount) / 14) * $column_multiply[$crypto_folder][$clean_uri]); ?>%;" percentage="<?php echo ($amount) ?>">
												<span class="line-text range-line<?php echo $date_compressed; ?>"><?php echo $amount; ?></span>
												<span class="line-up"></span>
												<span class="line-green"></span>
												<span class="line-sell"></span>
												<span class="line-buy"></span>
											</td>
										</tr>
									<?php 
											$i++;
										}
									?>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<br />
			<?php include "includes/footer.php"; ?>
			<br />
		<?php } ?>
	</body>
</html>	
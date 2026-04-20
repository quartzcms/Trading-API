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
						<h1 align="center" style="text-transform: uppercase;" class="text-success">Daily Graph</h1>
					</div>
				</div>
				<div class="row" style="display:none;">
					<div class="col-md-2">
						<div style="padding: 10px;" align="center">
							<h3>Miscellaneous</h3>
							<div class="buttons">
								<?php $current_date = date('Hi'); ?>
								<span id="<?php echo $current_date.'00'; ?>" class="current-date btn btn-primary">Scroll to current</span>
							</div>
							<h4 align="center">1 day collected</h4>
							<h4 class="text-warning range-between">Est.: <span></span></h4>
						</div>
					</div>
					<div class="col-md-8">	
						<?php include "includes/main_daily.php"; ?>
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
										$hours = range(0, 23);
										$minutes = range(0, 59);
										foreach ($hours as $h_index => $hour){
											foreach ($minutes as $m_index => $minute){
												$hour = sprintf("%02d", $hour);
												$minute = sprintf("%02d", $minute);
												$data[$hour.$minute.'00'] = 0;
											}
										}
										
										$file_content = file_get_contents('crypto/'.$crypto_folder.'/data_daily/data_btc.txt');
										$lines = explode("\n", $file_content);
										asort($lines);
										foreach($lines as $line){
											$line_parts = explode(':', $line);
											if(!empty($line)){
												$data[$line_parts[0]] = $line;
											}
										}
										$i = 1;
										
										$current_date = date('Hi');
										ksort($data);
										foreach($data as $time => $line_data){
											$line_keys = explode(':', $line_data);
											$hour = substr($time, 0, 2);
											$minute = substr($time, 2, 2);
											$second = '00';
											$risk = 0;
											$amount = 0;
											if(isset($line_keys[1])){
												$risk = $line_keys[1];
											}
											if(isset($line_keys[2])){
												$amount = $line_keys[2];
											}
											
											if(!is_numeric($amount)){
												$amount = 0;
											} elseif(is_nan($amount)){
												$amount = 0;
											}
											
											$amount = number_format($amount, 2, '.', '');
												?>
												<tr class="<?php if($i == 1){ echo "range-start"; } ?> <?php if(strval($amount) < '0'){ ?>green<?php } else { ?>red<?php } ?><?php if($hour.$minute.$second == $current_date.'00'){ ?> now<?php } ?>">
													<th scope="row" class="value"> 
														Min: <span class="time" time="<?php echo $hour.$minute.$second; ?>"><?php echo $hour.':'.$minute.':'.$second; ?></span>
														<?php if($amount >= 0){ ?>
														<span class="label label-danger range-tag range<?php echo $time; ?>" style="font-size: 1em;">
														<?php } else { ?>
														<span class="label label-success range-tag range<?php echo $time; ?>" style="font-size: 1em;">
														<?php } ?>
														Range: <strong><?php if(is_nan($amount)) { echo "0"; } else { echo $amount; } ?></strong></span>
														<?php if($risk == '3' || $risk == '2'){ ?><span class="label label-success gold range-tag" style="margin-top: 2px; font-size: 0.95em;">
														<?php } else { ?>
														<span><?php } ?>
														Risk: <?php echo $risk; ?> &#8594;</span> 
													</th> 
													<td position="<?php  echo ($i * 78) - 78; ?>" id="b<?php echo $time; ?>"  class="<?php if(strval($amount) > '0'){ ?>grey<?php } ?>" style="height:<?php echo (abs($amount) * $column_multiply[$crypto_folder][$clean_uri]); ?>%;" percentage="<?php echo $amount ?>">
														<span class="line-text range-line<?php echo $time; ?>"><?php echo $amount; ?></span>
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
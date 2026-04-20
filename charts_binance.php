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
		<link rel="stylesheet" href="assets/bootstrap.min.css">
		<!--<script src="assets/jquery-3.6.0.min.js"></script>-->
		<link rel="stylesheet" href="assets/charts.min.css">
		
		<script src="includes/anychart/node_modules/anychart/dist/js/anychart-base.min.js"></script>
		<script src="includes/anychart/node_modules/anychart/dist/js/anychart-stock.min.js"></script>
		<script src="includes/anychart/node_modules/anychart/dist/js/anychart-ui.min.js"></script>
		<script src="includes/anychart/node_modules/anychart/dist/js/anychart-exports.min.js"></script>
		<script src="includes/anychart/node_modules/jquery/dist/jquery.min.js"></script>
		<script src="includes/anychart/dist/anychart-jquery.min.js"></script>
		<link rel="stylesheet" href="includes/anychart/node_modules/anychart/dist/css/anychart-ui.min.css" />
		<link rel="stylesheet" href="includes/anychart/node_modules/anychart/dist/fonts/css/anychart-font.min.css" />
		<script type="text/javascript">
			var chart;
			anychart.onDocumentReady(function () {
				var chart = anychart.stock();
				
				var dataPoints = [];
				var dataTable = anychart.data.table();

				$.getJSON("includes/chart_data.php", function(data) {  
					for(var i = 0; i < data.length; i++){
						dataPoints.push([String(data[i].date), Number(data[i].open), Number(data[i].close), Number(data[i].high), Number(data[i].low)]);
					}
					
					dataTable.addData(dataPoints);

					var mapping = dataTable.mapAs();
					mapping.addField('open', 1);
					mapping.addField('close', 2);
					mapping.addField('high', 3);
					mapping.addField('low', 4);
					
					var grouping = chart.grouping();

					// set the levels of grouping
					grouping.levels([
						{unit: 'minute', count: 1}
					]);

					// create a plot and an two column series
					chart.plot(0).ohlc(mapping).name('<?php echo $symbol[0]; ?>');
					
					var selectable = mapping.createSelectable();
					selectable.select('2020-01-01', parseInt(new Date().getFullYear() + 1) + '-01' + '-01');
					
					chart.background().fill("rgba(0,0,0, 0.2)");
					chart.title('<?php echo $symbol[0]; ?> PRICE');
					chart.container('containerChart');
					chart.draw();
				});
				
				var chart2 = anychart.line();
				var dataPoints3 = [];
				var dataPoints2 = [];
				
				$.getJSON("includes/account_data.php", function(data) {  
					for(var i = 0; i < data.length; i++){
						dataPoints3.push([String(data[i].date), Number(data[i].today)]);
						dataPoints2.push([String(data[i].date), Number(data[i].account)]);
					}

					// create a plot and an two column series
					chart2.title('BINANCE INCOME');
					chart2.line(dataPoints3).name('Today\'s income');
					chart2.line(dataPoints2).name('Absolute income');
					chart2.background().fill("rgba(0,0,0, 0.2)");
					chart2.container('containerAccount');
					chart2.draw();
				});							
			});
		</script>
		<script src="assets/iframeResizer.contentWindow.min.js"></script>
		<?php include "includes/header.php"; ?>
	</head>
	<body>
		<?php if (isset($_SESSION['logged_in'])){ ?>
			<div class="container-fluid">
				<div class="row" style="display:none;">
					<div class="col-md-12">
						<h1 align="center" style="text-transform: uppercase;" class="text-success">Detailed Graphs</h1>
					</div>
				</div>
				<div class="row" style="display:none;">
					<div class="col-md-2">
						<div style="padding: 10px;" align="center">
							<h3>Miscellaneous</h3>
							<div class="buttons" style="display:none">
								<?php $current_date = date('Hi'); ?>
								<span id="<?php echo $current_date.'00'; ?>" class="current-date btn btn-primary">Scroll to current</span>
							</div>
							<h4 align="center"><?php $files = dirToArray('crypto/'.$crypto_folder.'/data_yearly'); echo count($files); ?> day collected</h4>
							<h4 class="text-warning range-between" style="display:none">Est.: <span></span></h4>
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
			
			<div class="container-fluid">
				<div class="row" style="display:none;">
					<div class="col-md-12">
						<div id="containerChart" style="height: 450px; width: 100%; margin: 20px 0px; border:1px solid #555555;"></div>
						<div id="containerAccount" style="height: 450px; width: 100%; margin: 20px 0px; border:1px solid #555555;"></div>
						<?php
							$coin_array = use_session($_SESSION['user_in'].'.txt'.'_coin_array');
							$price_array_list = use_session($_SESSION['user_in'].'.txt'.'_price_array_list');
							if(!empty($coin_array) && !empty($price_array_list)){
								foreach($coin_array as $key => $value){
									if(isset($price_array_list[$coin_array[$key]['name']])){
										$actual_price = $price_array_list[$coin_array[$key]['name']];
										$decimal = explode('.', $actual_price);
										$rounded_price = (strval($actual_price) < '0') ? round($actual_price, intval(strlen($decimal[1]) - 1)) : round($actual_price, -(intval(strlen($decimal[0])) - 1));
						?>
									<h3><?php echo $coin_array[$key]['name']; ?></h3>
									<div class="progress" style="height: 60px; background-color: #444444; border-radius: 0px;">
									<div id="<?php echo $coin_array[$key]['name']; ?>" class="progress-bar " role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="min-width: 150px; padding-left: 10px; padding-top: 10px; text-transform: uppercase; text-align:left; width: <?php echo (strval($rounded_price) > '0') ? (($coin_array[$key]['value']) / $rounded_price) : (($coin_array[$key]['value']) / 1); ?>%"><?php echo "Current : ".number_format(($coin_array[$key]['value'] * $price_array_list[$coin_array[$key]['name']]), 2, '.', ''); echo "<br />Rounded Price: ".$rounded_price; ?></div>
									</div>
						<?php
									}
								}
							}
						?>
					</div>
				</div>
			</div>
			<br />
			<?php include "includes/footer.php"; ?>
			<br />
		<?php } ?>
	</body>
</html>	
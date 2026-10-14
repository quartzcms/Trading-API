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
			anychart.onDocumentReady(function () {
				var stockChart = anychart.stock();
				var priceData = [];
				var dataTable = anychart.data.table();

				$.getJSON("includes/chart_data.php", function (chartData) {
					for (var index = 0; index < chartData.length; index++) {
						var point = chartData[index];
						priceData.push([
							String(point.date),
							Number(point.open),
							Number(point.close),
							Number(point.high),
							Number(point.low)
						]);
					}

					dataTable.addData(priceData);

					var mapping = dataTable.mapAs();
					mapping.addField('open', 1);
					mapping.addField('close', 2);
					mapping.addField('high', 3);
					mapping.addField('low', 4);

					stockChart.grouping().levels([
						{unit: 'minute', count: 1}
					]);

					stockChart.plot(0).ohlc(mapping).name('<?php echo $symbol[0]; ?>');

					var selectable = mapping.createSelectable();
					var endYear = new Date().getFullYear() + 1;
					selectable.select('2020-01-01', endYear + '-01-01');

					stockChart.background().fill("rgba(0,0,0, 0.2)");
					stockChart.title('<?php echo $symbol[0]; ?> PRICE');
					stockChart.container('containerChart');
					stockChart.draw();
				});

				var incomeChart = anychart.line();

				$.getJSON("includes/account_data.php", function (accountData) {
					var dailyIncomePoints = [];
					var accountIncomePoints = [];

					for (var index = 0; index < accountData.length; index++) {
						var point = accountData[index];
						dailyIncomePoints.push([String(point.date), Number(point.today)]);
						accountIncomePoints.push([String(point.date), Number(point.account)]);
					}

					incomeChart.title('KRAKEN INCOME');
					incomeChart.line(dailyIncomePoints).name('Today\'s income');
					incomeChart.line(accountIncomePoints).name('Absolute income');
					incomeChart.background().fill("rgba(0,0,0, 0.2)");
					incomeChart.container('containerAccount');
					incomeChart.draw();
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
							$portfolio_progress = (!empty($coin_array) && !empty($price_array_list))
								? \Core\Market\PortfolioProgressService::build($coin_array, $price_array_list)
								: array();
							foreach ($portfolio_progress as $coin_progress) {
						?>
									<h3><?php echo $coin_progress['name']; ?></h3>
									<div class="progress" style="height: 60px; background-color: #444444; border-radius: 0px;">
									<div id="<?php echo $coin_progress['name']; ?>" class="progress-bar " role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="min-width: 150px; padding-left: 10px; padding-top: 10px; text-transform: uppercase; text-align:left; width: <?php echo $coin_progress['width']; ?>%"><?php echo "Current : ".$coin_progress['current_value']; echo "<br />Rounded Price: ".$coin_progress['rounded_price']; ?></div>
									</div>
						<?php
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
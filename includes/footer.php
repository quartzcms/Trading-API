<?php
	$clean_uri = explode('?', basename($_SERVER['REQUEST_URI']));
	$clean_uri = $clean_uri[0];
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12" align="center">
			<p>
				<?php if(!isset($_SESSION['user_in'])){ ?>
				<a href="user_binance.php" class="btn <?php if($clean_uri == 'user_binance.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Binance API</a>
				<?php } ?>
				<?php if(isset($_SESSION['user_in'])){ ?>
					<?php if(strpos($_SESSION['user_in'],'binance') !== false){ ?>
						<a href="user_binance.php" class="btn <?php if($clean_uri == 'user_binance.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Binance API</a>
						<a href="user_report_binance.php" class="btn <?php if($clean_uri == 'user_report_binance.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Binance Report</a>
						<a href="charts_binance.php" class="btn <?php if($clean_uri == 'charts_binance.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Binance Charts</a>
						<a href="total_binance.php" class="btn <?php if($clean_uri == 'total_binance.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Binance Statement</a>
					<?php } ?>
				<?php } ?>
				<?php if(!isset($_SESSION['user_in'])){ ?>
				<a href="user_coinbase.php" class="btn <?php if($clean_uri == 'user_coinbase.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Coinbase API</a>
				<?php } ?>
				<?php if(isset($_SESSION['user_in'])){ ?>
					<?php if(strpos($_SESSION['user_in'],'coinbase') !== false){ ?>
						<a href="user_coinbase.php" class="btn <?php if($clean_uri == 'user_coinbase.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Coinbase API</a>
						<a href="user_report_coinbase.php" class="btn <?php if($clean_uri == 'user_report_coinbase.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Coinbase Report</a>
						<a href="charts_coinbase.php" class="btn <?php if($clean_uri == 'charts_coinbase.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Coinbase Charts</a>
						<a href="total_coinbase.php" class="btn <?php if($clean_uri == 'total_coinbase.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Coinbase Statement</a>
					<?php } ?>
				<?php } ?>
				<?php if(!isset($_SESSION['user_in'])){ ?>
				<a href="user_kraken.php" class="btn <?php if($clean_uri == 'user_kraken.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Kraken API</a>
				<?php } ?>
				<?php if(isset($_SESSION['user_in'])){ ?>
					<?php if(strpos($_SESSION['user_in'],'kraken') !== false){ ?>
						<a href="user_kraken.php" class="btn <?php if($clean_uri == 'user_kraken.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Kraken API</a>
						<a href="user_report_kraken.php" class="btn <?php if($clean_uri == 'user_report_kraken.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Kraken Report</a>
						<a href="charts_kraken.php" class="btn <?php if($clean_uri == 'charts_kraken.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Kraken Charts</a>
						<a href="total_kraken.php" class="btn <?php if($clean_uri == 'total_kraken.php'){ ?>btn-success<?php } else { ?>btn-danger<?php } ?>">Kraken Statement</a>
					<?php } ?>
				<?php } ?>
				<a href="daily.php" class="btn <?php if($clean_uri == 'daily.php'){ ?>btn-success<?php } else { ?>btn-primary<?php } ?>">Daily Risk</a>
				<a href="weekly.php" class="btn <?php if($clean_uri == 'weekly.php'){ ?>btn-success<?php } else { ?>btn-primary<?php } ?>">Weekly Median</a>
				<a href="monthly.php" class="btn <?php if($clean_uri == 'monthly.php'){ ?>btn-success<?php } else { ?>btn-primary<?php } ?>">Monthly Median</a>
				<a href="yearly.php" class="btn <?php if($clean_uri == 'yearly.php'){ ?>btn-success<?php } else { ?>btn-primary<?php } ?>">Yearly Median</a>
				<a href="weekly_days.php" class="btn <?php if($clean_uri == 'weekly_days.php'){ ?>btn-success<?php } else { ?>btn-primary<?php } ?>">Weekly Days</a>
				<a href="monthly_days.php" class="btn <?php if($clean_uri == 'monthly_days.php'){ ?>btn-success<?php } else { ?>btn-primary<?php } ?>">Monthly Weeks</a>
				<a href="yearly_days.php" class="btn <?php if($clean_uri == 'yearly_days.php'){ ?>btn-success<?php } else { ?>btn-primary<?php } ?>">Yearly Months</a>
				<a href="logout.php" class="btn btn-danger">Logout</a>
			</p>
		</div>
	</div>
</div>
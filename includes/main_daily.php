<?php 
	include "config.php";
	
	if(!isset($_SESSION['crypto'])){
		$crypto_folder = $default_stock;
	} else {
		$crypto_folder = $_SESSION['crypto'];
	}
	
	$currency = explode('_', $crypto_folder);
?>
<div class="row">
	<div class="col-md-3 col-sm-6 col-xs-6">
		<div style="padding: 10px 0px;" align="center">
			<h3 class="BTC text-success">Last <?php echo strtoupper($currency[1]); ?>: <span>0</span> $</h2>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-6">
		<div style="padding: 10px 0px;" align="center">
			<h3 class="next_up0" time="">Cur. Min.: <span>0</span></h2>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-6">
		<div style="padding: 10px 0px;" align="center">
			<h3 class="next_up1 text-warning">Cur. Risk: <span>0</span></h2>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-6">
		<div style="padding: 10px 0px;" align="center">
			<h3 class="next_up2 text-danger">Cur. Range: <span>0</span></h2>
		</div>
	</div>
</div>
<p align="center">This graph indicate the difference between each current and old minute. It also calculate the prediction. Each time the current minute difference is bigger than the next one the prediction increase of 1. The difference number is replaces it reaches 3.</p>
<p align="center">This page retrieve the result from the API with a background scheduled task and saves them into a text file each minute.</p>
<p align="center" class="text-success">This graph additionally calculate the median for the time for each day file collected. Look at the gold range indicators for the stable period.</p>
<div class="row">
	<div class="col-md-8 col-sm-6">
		<select name="crypto" class="crypto form-control">
			<?php foreach($stocks_array as $key => $value){ ?> 
				<option value="<?php echo $value ?>" <?php if($crypto_folder == $value){ ?>selected="selected"<?php } ?>><?php $symbol = array_keys($stocks_array, $value); echo $symbol[0].' - '.strtoupper(str_replace('_', ' ', $value)); ?></option>
			<?php } ?>
		</select>
	</div>
	<div class="col-md-4 col-sm-6">
		<select name="timezone" class="timezone form-control">
			<option value="New/York">Timezone - America/New_York</option>
		</select>
	</div>
</div>
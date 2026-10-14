<?php
ini_set('display_errors', 1);

	if(!isset($_SESSION['crypto'])){
		$crypto_folder = $default_stock;
	} else {
		$crypto_folder = $_SESSION['crypto'];
	}
	
	$clean_uri = explode('?', basename($_SERVER['REQUEST_URI']));
	$clean_uri = $clean_uri[0];
	
	$config_api_data = array();
	$api_data = array();
	$api_median_trigger = 0;
	if(isset($_SESSION['user_in'])){
		$session_data_directory = \Core\Support\SessionStore::getDataDirectory();
		$files = \Core\Support\Filesystem::directoryToArray($session_data_directory . DIRECTORY_SEPARATOR . 'config_' . $_SESSION['user_in'] . '.txt');
		$user_session_files = \Core\Support\Filesystem::directoryToArray($session_data_directory . DIRECTORY_SEPARATOR . $_SESSION['user_in'] . '.txt');
		
		if(use_session($_SESSION['user_in'].'.txt_hide') == ''){
			write_session($_SESSION['user_in'].'.txt_hide', 'hide');
		}
		foreach($files as $key => $value){
			$name = str_replace('.txt', '', $value);
			$config_api_data[$name] = use_session('config_'.$_SESSION['user_in'].'.txt_'.$name);
		}
		foreach($user_session_files as $key => $value){
			$name = str_replace('.txt', '', $value);
			$api_data[$name] = use_session($_SESSION['user_in'].'.txt_'.$name);
		}
	}
	?>
	<link rel="apple-touch-icon" sizes="180x180" href="images/favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="images/favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="images/favicon/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<script src="includes/bootstrap.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('[data-toggle="popover"]').popover();
			
			function pad(n) {
				 return (n < 10) ? '0' + n : n;
			}
			<?php if($clean_uri != 'user_report_binance.php' && $clean_uri != 'user_binance.php' &&
			$clean_uri != 'user_report_coinbase.php' && $clean_uri != 'user_coinbase.php' &&
			$clean_uri != 'user_report_kraken.php' && $clean_uri != 'user_kraken.php'){ ?>
			function reset_lines(){
				var i = 'start';
				
				$("table#my-chart tr").each(function(index, element) {
					$(this).find('.line-green').removeClass('start');
					$(this).find('.line-green').removeClass('end');
					$(this).find('.line-buy').removeClass('start_buy');
					$(this).find('.line-sell').removeClass('start_sell');
					$(this).find('.range-tag').removeClass('gold');
				});
				$('.periods').html('');
				
				var j = 0;
				var date = new Date();
				$("table#my-chart tr").each(function(index, element) {
					if($(this).find('.time').attr('time') != ''){
						<?php if($clean_uri != 'weekly_days.php' && $clean_uri != 'monthly_days.php' && $clean_uri != 'yearly_days.php'){ ?>
						var time = String($(this).find('.time').attr('time'));
						var time_parts = time.match(/.{1,2}/g);
						var full_date = new Date(date.getFullYear(), date.getMonth(), date.getDate(), parseInt(time_parts[0]), parseInt(time_parts[1]), parseInt(time_parts[2]));
						<?php } elseif($clean_uri == 'weekly_days.php' || $clean_uri == 'monthly_days.php') { ?>
						var time = String($(this).find('.time').attr('time'));
						var year = time.match(/.{1,4}/g);
						var month_day = year[1].match(/.{1,2}/g);
						var full_date = new Date(year[0], (parseInt(month_day[0], 10) - 1), parseInt(month_day[1], 10), date.getHours(), date.getMinutes(), date.getSeconds());
						<?php } elseif($clean_uri == 'yearly_days.php'){ ?>
						var time = String($(this).find('.time').attr('time'));
						var full_date = new Date(date.getFullYear(), (parseInt(time) - 1), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds());
						<?php } ?>
					}
				
					if($(this).hasClass('red') && i == 'start' &&
					$(this).next().hasClass('red') &&
					$(this).next().next().hasClass('red')){
						$(this).find('.line-green').addClass('start');
						i = 'end';
					} else if (i == 'end') {
						if($(this).next().hasClass('green')){
							$(this).find('.line-green').addClass('end');
							i = 'start';
						}
					}
					<?php if($api_median_trigger == 1 && isset($config_api_data['minute_amount']) && isset($config_api_data['buy_at']) && isset($config_api_data['sell_at'])) { ?>
						<?php
							$condition = array();
							for($i = 0; $i < $config_api_data['minute_amount']; $i++){ 
								$condition[$i] = "parseFloat($(this)";
								
								if($i > 1){
									$condition[$i] .= str_repeat(".next()", $i);
								}
								if($i == 1){
									$condition[$i] .= ".next()";
								}
								$condition[$i] .= ".find('td').attr('percentage'))"; 
							} 
							
							if(!empty($condition)){
								$condition_buy = "((".implode(' + ', $condition).")) > ".$config_api_data['buy_at'];
								$condition_sell = "((".implode(' + ', $condition).")) < ".$config_api_data['sell_at'];
						?>						
							if(<?php echo $condition_buy; ?>){
								$(this).find('.line-buy').addClass('start_buy');
							}
							
							if(<?php echo $condition_sell; ?>){
								$(this).find('.line-sell').addClass('start_sell');
							}
					<?php 
							}
						} 
					?>
					
					<?php if($clean_uri != 'weekly_days.php' && $clean_uri != 'monthly_days.php' && $clean_uri != 'yearly_days.php'){ ?>
					if(date < full_date){
					<?php } elseif($clean_uri == 'weekly_days.php' || $clean_uri == 'monthly_days.php' || $clean_uri == 'yearly_days.php') { ?>
					if(date > full_date){
					<?php } ?>
						if($(this).hasClass('red') && $(this).next().hasClass('red')){
							var column_amount = $(this).find('td').attr("percentage"); 
							var next_column_amount = $(this).next().find('td').attr("percentage");
							var difference = parseFloat(next_column_amount) - parseFloat(column_amount);
							<?php if($clean_uri != 'weekly_days.php' && $clean_uri != 'monthly_days.php' && $clean_uri != 'yearly_days.php'){ ?>
							if(difference > 15 && j < 10){
							<?php } elseif($clean_uri == 'weekly_days.php' || $clean_uri == 'monthly_days.php' || $clean_uri == 'yearly_days.php') { ?>
							if(difference > 100 && j < 10){
							<?php } ?>
								$(this).find('.range-tag').addClass('gold');
								$(this).next().find('.range-tag').addClass('gold');
								$(".periods").append("<span class='label label-success gold' style='display:inline-block; margin-right: 5px;' id='" + $(this).find('.time').attr('time') + "'>" + $(this).find('.time').text() + "</span>");
								j++;
							}
						}
					}
				});
			}
			
			
			$(".periods").delegate("span", "click", function() {
				$('.scroll').animate({
					scrollLeft: $("#b" + $(this).attr('id')).attr('position')
				  }, 800, function(){
				});
			});
			
			$(".buttons").delegate("span", "click", function() {
				$('.scroll').animate({
					scrollLeft: $("#b" + $(this).attr('id')).attr('position')
				  }, 800, function(){
				});
			});
			
			function get_median(next_price){
				var post_data = {crypto: '<?php echo $crypto_folder; ?>'};
				$.ajax({
					dataType: "json",
					type: "POST",
					<?php if($clean_uri == 'weekly.php'){ ?>
					url: "json_request/median_weekly.php",
					<?php } elseif($clean_uri == 'monthly.php'){ ?>
					url: "json_request/median_monthly.php",
					<?php } elseif($clean_uri == 'yearly.php'){ ?>
					url: "json_request/median_yearly.php",
					<?php } elseif($clean_uri == 'weekly_days.php'){ ?>
					url: "json_request/median_weekly_days.php",
					<?php } elseif($clean_uri == 'monthly_days.php'){ ?>
					url: "json_request/median_monthly_days.php",
					<?php } elseif($clean_uri == 'yearly_days.php'){ ?>
					url: "json_request/median_yearly_days.php",
					<?php } elseif($clean_uri == 'daily.php'){ ?>
					url: "json_request/daily.php",
					<?php } ?>
					data: post_data,
					success: function (data){
						<?php if($clean_uri != 'daily.php'){ ?>
						$('.files'+ data.date).text("Files #: " + data.files);
						<?php } ?>
						
						$('.range'+ data.date).html("Range: <strong>" + data.amount + "</strong>");
						$('.range-line'+ data.date).text(data.amount);
						$('.next_up2 span').text($('.range' + data.current_date + ' strong').text());
						$('.current-date').attr('id', data.current_date);
						
						if(parseFloat($('.range' + data.current_date + ' strong').text()) < 0){
							$('.next_up2').removeClass('text-success');
							$('.next_up2').addClass('text-danger');
						} else {
							$('.next_up2').removeClass('text-danger');
							$('.next_up2').addClass('text-success');
						}
						
						$('.scroll').animate({
							scrollLeft: $("#b" + data.current_date).attr('position')
						  }, 800, function(){
						});	
						
						if(data.amount < 0){
							$('#b'+ data.date).removeClass('grey');
							$('.range'+ data.date).removeClass('label-danger');
							$('.range'+ data.date).addClass('label-success');
							$('#b'+ data.date).closest('tr').removeClass('red');
							$('#b'+ data.date).closest('tr').addClass('green');
						} else {
							$('#b'+ data.date).addClass('grey');
							$('.range'+ data.date).removeClass('label-success');
							$('.range'+ data.date).addClass('label-danger');
							$('#b'+ data.date).closest('tr').removeClass('green');
							$('#b'+ data.date).closest('tr').addClass('red');
						}
						
						$('#b'+ data.date).animate({
							<?php if($clean_uri == 'weekly_days.php'){ ?>
							height: ((Math.abs(data.size) / 14) * <?php echo (isset($column_multiply[$crypto_folder][$clean_uri]) ? $column_multiply[$crypto_folder][$clean_uri] : 1); ?>) + '%'
							<?php } elseif($clean_uri == 'monthly_days.php'){ ?>
							height: ((Math.abs(data.size) / 30) * <?php echo (isset($column_multiply[$crypto_folder][$clean_uri]) ? $column_multiply[$crypto_folder][$clean_uri] : 1); ?>) + '%'
							<?php } elseif($clean_uri == 'yearly_days.php'){ ?>
							height: ((Math.abs(data.size) / 365) * <?php echo (isset($column_multiply[$crypto_folder][$clean_uri]) ? $column_multiply[$crypto_folder][$clean_uri] : 1); ?>) + '%'
							<?php } else { ?>
							height: (Math.abs(data.size) * <?php echo (isset($column_multiply[$crypto_folder][$clean_uri]) ? $column_multiply[$crypto_folder][$clean_uri] : 1); ?>) + '%'
							<?php } ?>
						  }, 800, function(){
						});
						
						$('#b'+ data.date).attr("percentage", data.size);
						reset_lines();
						
						clearInterval(intervalId);
						intervalId = window.setInterval(function(){
							$('.range-between span').text(next_price.toFixed(2));
							<?php if($clean_uri == 'weekly_days.php'){ ?>
							next_price = next_price + (data.amount / ((60 * 60) * 24));
							<?php } elseif($clean_uri == 'monthly_days.php'){ ?>
							next_price = next_price + (data.amount / (((60 * 60) * 24) * 7));
							<?php } elseif($clean_uri == 'yearly_days.php'){ ?>
							next_price = next_price + (data.amount / (((60 * 60) * 24) * 30));
							<?php } else { ?>
							next_price = next_price + (data.amount / 60);
							<?php } ?>
						}, 1000);
					}
				});
			}
			<?php } ?>
			
			function convert_array(transaction){
				const transaction_string = [];
				var i = 0;
				for (property in transaction) {
					if(transaction[property] !== null && transaction[property] !== ''){
						if(transaction[property] instanceof Array || transaction[property] instanceof Object){
							if(transaction[property].length !== 0){
								transaction_string[i] = convert_array(transaction[property]);
							}
						} else {
							transaction_string[i] = property + ':' + transaction[property];
						}
						i++;
					}
				}
				return transaction_string.join(', ')
			}
			<?php if((isset($_SESSION['user_in']) && $clean_uri != 'charts_binance.php' && $clean_uri != 'charts_coinbase.php' && $clean_uri != 'charts_kraken.php' && $clean_uri != 'total_binance.php' && $clean_uri != 'total_coinbase.php' && $clean_uri != 'total_kraken.php') && ($clean_uri == 'user_report_binance.php' || $clean_uri == 'user_report_coinbase.php' || $clean_uri == 'user_report_kraken.php')){ ?>
			$(".days").delegate("button", "click", function() {
				location.href= "?day=" + $(this).attr('day');
			});
				<?php $day = isset($_GET['day']) ? $_GET['day'] : '';
					if($day != ''){
				?>
					$('.day-container').animate({
						scrollLeft: $("#<?php echo $_GET['day']; ?>").attr('position')
					  }, 800, function(){
					});	
				<?php		
					}
				?>
				
			<?php } ?>
			
			function main(){
				<?php if((isset($_SESSION['user_in']) && 
				$clean_uri != 'charts_binance.php' && $clean_uri != 'charts_coinbase.php' && $clean_uri != 'charts_kraken.php' && 
				$clean_uri != 'total_binance.php' && $clean_uri != 'total_coinbase.php' && $clean_uri != 'total_kraken.php') && 
				($clean_uri == 'user_report_binance.php' || $clean_uri == 'user_report_coinbase.php' || $clean_uri == 'user_report_kraken.php')){ ?>
				$.ajax({
					dataType: "json",
					type: "POST",
					url: "json_request/output_report.php",
					data: {},
					success: function (data){
						for(pro_data in data.count){
							if(data.count[pro_data]['data'] != "" && data.count[pro_data]['today'] != ""){
								const transaction = JSON.parse(data.count[pro_data]['data']);
								var current_date = new Date();
								var date_formatted = pad(current_date.getFullYear()) + '-' + pad(current_date.getMonth() + 1) + '-' + pad(current_date.getDate());
								var class_transaction = '';
								var news_indicator = 0;
								if(data.count[pro_data]['data'].match(/Open Orders Count/g)){
									class_transaction = 'text-info';
								} else if(data.count[pro_data]['data'].match(/There is not enough money/g)){
									class_transaction = 'text-balance';
								} else if(data.count[pro_data]['data'].match(/The price is stagnant/g)){
									class_transaction = 'text-balance';
								} else if(data.count[pro_data]['data'].match(/Sentiment/g)){
									class_transaction = 'text-balance';
									news_indicator = 1;
								} else if(data.count[pro_data]['data'].match(/Buying is blocked/g)){
									class_transaction = 'text-warning';
								} else if(data.count[pro_data]['data'].match(/End of the selling all/g)){
									class_transaction = 'text-selling';
								} else if(data.count[pro_data]['data'].match(/Gained/g)){
									class_transaction = 'text-success';
								} else if(data.count[pro_data]['data'].match(/In the selling all period/g)){
									class_transaction = 'text-selling';
								} else if(data.count[pro_data]['data'].match(/No transaction has been/g)){
									class_transaction = 'text-balance';
								} else if(data.count[pro_data]['data'].match(/Price limit is reached/g)){
									class_transaction = 'text-warning';
								} else if(data.count[pro_data]['data'].match(/Out of the time/g)){
									class_transaction = 'text-warning';
								} else if(data.count[pro_data]['data'].match(/No triggers were fired/g)){
									class_transaction = 'text-balance';
								} else if(data.count[pro_data]['data'].match(/No purchase is allowed/g)){
									class_transaction = 'text-balance';
								} else if(data.count[pro_data]['data'].match(/The today\'s total has reached/g)){
									class_transaction = 'text-warning';
								}
								var transaction_string;
								var break_past_median = data.count[pro_data]['past'].split('*');
								var today_day = $('.days .day.btn-success').attr('day');
								if(news_indicator ==  1){
									var news_string = JSON.parse(data.count[pro_data]['data']);
									for(var key in news_string){
										news_string[key] = $("<div/>").html(news_string[key]).text();
										news_string[key] = news_string[key].replace(/\(\(\^\)\)/g, '=');
										news_string[key] = news_string[key].replace(/\(\(\_\)\)/g, '*');
										news_string[key] = news_string[key].replace(/\(\(\-\)\)/g, '|');
									}
									transaction_string = news_string.join('<br />');
									if(date_formatted == today_day){
										$('.report_table tbody').prepend($("<tr><td width='10%'><p>" + data.count[pro_data]['date'] + " <br /><span class=\"text-info\">" + data.count[pro_data]['price'] + "</span></p></td><td width='84%' colspan='8'><p class=\"" + class_transaction + "\">" + transaction_string + "</p></td><td width='6%'><p class='text-warning'>Amount: <br />" + data.count[pro_data]['amount'] + "</p></td></tr>"));
									}
								} else {
									transaction_string = convert_array(transaction);
									if(date_formatted == today_day){
										$('.report_table tbody').prepend($("<tr><td width='10%'><p>" + data.count[pro_data]['date'] + " <br /><span class=\"text-info\">" + data.count[pro_data]['price'] + "</span></p></td><td width='34%'><p class=\"" + class_transaction + "\">" + transaction_string + "</p></td><td width='8%'><p class='"+ (data.count[pro_data]['method'] == 'past ratio' ? (data.count[pro_data]['action'] == 'buy' ? 'text-success' : 'text-danger'): '') +"'>Past Ratio (1st): <br />" + data.count[pro_data]['ratio'] + "</p></td><td width='8%'><p class='"+ (data.count[pro_data]['method'] == 'past median' && data.count[pro_data]['sub_method'] == 'extra_long' ? (data.count[pro_data]['action'] == 'buy' ? 'text-success' : 'text-danger'): '') +"'>Past Range (2nd): <br />" + break_past_median[2] + "</p></td><td width='8%'><p class='"+ (data.count[pro_data]['method'] == 'past median' && data.count[pro_data]['sub_method'] == 'long' ? (data.count[pro_data]['action'] == 'buy' ? 'text-success' : 'text-danger'): '') +"'>Past Range (3rd): <br />" + break_past_median[1] + "</p></td><td width='8%'><p class='"+ (data.count[pro_data]['method'] == 'past median' && data.count[pro_data]['sub_method'] == 'short' ? (data.count[pro_data]['action'] == 'buy' ? 'text-success' : 'text-danger'): '') +"'>Past Range (4th): <br />" + break_past_median[0] + "</p></td><td width='8%'><p class='"+ (data.count[pro_data]['method'] == 'future median' ? (data.count[pro_data]['action'] == 'buy' ? 'text-success' : 'text-danger'): '') +"'>Future Med (5th): <br />" + data.count[pro_data]['future'] + "</p></td><td width='8%'><p>RSI Long (6th): <br />" + data.count[pro_data]['stagnant'] + "</p></td><td width='2%'><p class='"+ (data.count[pro_data]['action'] == 'buy' ? "text-success": (data.count[pro_data]['action'] == 'sell' ? "text-danger": "")) +"'>Action: <br />" + (data.count[pro_data]['action'] != '' ? data.count[pro_data]['action'] : '0') + "</p></td><td width='6%'><p class='text-warning'>Amount: <br />" + data.count[pro_data]['amount'] + "</p></td></tr>"));
									}
								}
								
								
								
								if(date_formatted != data.count[pro_data]['today'] || data.count[pro_data]['refresh'] == true){
									location.href= "?day=" + data.count[pro_data]['today'];
								}
							}
						}
					}
				});
				<?php } ?>
				
				<?php if($clean_uri != 'user_report_binance.php' && $clean_uri != 'user_binance.php' &&
				$clean_uri != 'user_report_coinbase.php' && $clean_uri != 'user_coinbase.php' &&
				$clean_uri != 'user_report_kraken.php' && $clean_uri != 'user_kraken.php'){ ?>
				var post_data = {crypto: '<?php echo $crypto_folder; ?>'};
				$.ajax({
					dataType: "json",
					type: "POST",
					url: "json_request/output.php",
					data: post_data,
					success: function (data){
						$('.BTC span').text(data.current);
						var next_date = data.next_up0;
						var next_date_parts = next_date.match(/.{1,2}/g);
						next_date_parts = next_date_parts.join(':');
						$('.next_up0 span').text(next_date_parts);
						$('.next_up0').attr('time', data.next_up0);
						$('.next_up1 span').text(data.next_up1);
						
						get_median(data.current);
					}
				});
				<?php } ?>
			}
			
			var intervalId;
			
			function timeout() {
				main();
				setTimeout(timeout, 60000);
			}
			function flat_trigger(){
				var date_trigger = new Date();
				if(date_trigger.getSeconds() == '10'){
					timeout();
				} else {
					setTimeout(flat_trigger, 1000);
				}
			}
			main();
			flat_trigger();
			
			$('.crypto').change(function() {
				var url = window.location.href;
				var clean_uri = url.split('?');
				$(location).attr("href", clean_uri[0] + "?crypto=" + $(this).val());
			});
			
			<?php if(($clean_uri == 'user_report_binance.php' || $clean_uri == 'user_report_coinbase.php' || $clean_uri == 'user_report_kraken.php') && isset($_SESSION['user_in']) && 
			$clean_uri != 'charts_binance.php' && $clean_uri != 'charts_coinbase.php' && $clean_uri != 'charts_kraken.php' &&
			$clean_uri != 'total_binance.php' && $clean_uri != 'total_coinbase.php' && $clean_uri != 'total_kraken.php'){ ?>
			
			var sell_all_toggle = 0;
			var detect_lowest_toggle = 0;
			var detect_lowest_switch_toggle = 0;
			var sell_all_plus_20_toggle = 0;
			var sell_all_plus_10_toggle = 0;
			var sell_unlock_minus_20_toggle = 0;
			var sell_unlock_minus_10_toggle = 0;
			$('.sell_all').click(function() {
				$('.sell_all').prop("disabled", true);
				$('.sell_unlock').prop("disabled", true);
				sell_all_toggle = 1;
			});
			$('.detect_lowest').click(function() {
				detect_lowest_toggle = 1;
			});
			$('.detect_lowest.on').click(function() {
				detect_lowest_switch_toggle = 'on';
			});
			$('.detect_lowest.off').click(function() {
				detect_lowest_switch_toggle = 'off';
			});
			$('.sell_all.plus-20').click(function() {
				sell_all_plus_20_toggle = 1;
			});
			$('.sell_all.plus-10').click(function() {
				sell_all_plus_10_toggle = 1;
			});
			$('.sell_unlock.minus-20').click(function() {
				sell_unlock_minus_20_toggle = 1;
			});
			$('.sell_unlock.minus-10').click(function() {
				sell_unlock_minus_10_toggle = 1;
			});
			var sell_unlock_toggle = 0;
			$('.sell_unlock').click(function() {
				sell_unlock_toggle = 1;
			});
			
			$('.quickUpdate').on('mouseover', function(e) {
				$(this).parent().find('.type').val($(this).attr('data-type'));
			});
			
			$('.scroll_top').click(function(e) {
				e.preventDefault();
				$('.report_table').parent().animate({ 
					scrollTop: $('.report_table').offset().top
				}, 1000);
				return false;
			});
			
			$('.scroll_transaction').each(function(index, element){
				$(element).click(function(e) {
					e.preventDefault();
					$('.report_table').parent().animate({ 
						scrollTop: $($(element).attr('href')).offset().top
					}, 1000);
					return false;
				});
			});
			
			$('.transaction_exe').each(function(index, element){
				$('.transaction_anchors').append('<a class="scroll_transaction btn btn-success" style="margin:0px 2px;" href="#' + $(element).attr('id') + '">TX #' + index + '</a>');
			})
			
			<?php } ?>
			<?php if(isset($_SESSION['user_in']) && isset($api_data['toggle_price_reached_sell_all']) && $api_data['toggle_price_reached_sell_all'] == 1){ ?>
				$('.sell_all').prop("disabled", true);
			<?php } ?>
			<?php if(($clean_uri == 'user_report_binance.php' || $clean_uri == 'user_report_coinbase.php' || $clean_uri == 'user_report_kraken.php') && isset($_SESSION['user_in']) &&
			$clean_uri != 'charts_binance.php' && $clean_uri != 'charts_coinbase.php' && $clean_uri != 'charts_kraken.php' &&
			$clean_uri != 'total_binance.php' && $clean_uri != 'total_coinbase.php' && $clean_uri != 'total_kraken.php'){ ?>
				function price(){
					$.ajax({
						dataType: "json",
						type: "POST",
						url: "json_request/output_price.php",
						data: {password:'<?php echo $_SESSION['user_in'] ?>', sell_all: sell_all_toggle, sell_unlock: sell_unlock_toggle, sell_all_plus_20: sell_all_plus_20_toggle, sell_all_plus_10: sell_all_plus_10_toggle, sell_unlock_minus_20: sell_unlock_minus_20_toggle, sell_unlock_minus_10: sell_unlock_minus_10_toggle, detect_lowest: detect_lowest_toggle, detect_lowest_switch: detect_lowest_switch_toggle},
						success: function (data){
							if (data !== null) {
								<?php if(isset($config_api_data['hide']) && $config_api_data['hide'] != 'hide' && 
								isset($config_api_data['stock_account']) && isset($config_api_data['sell_coin'])){ ?>
								$('.price_buy').text('<?php echo $config_api_data['stock_account']; ?>: '+ data.price_buy + '$');
								$('.price_sell').text('<?php echo $config_api_data['sell_coin']; ?>: '+ data.price_sell + '$');
								<?php } ?>
								$('.price').text('PRICE: '+ data.price + '$');
								
								if(data.range > 0){
									$('.price_range').text('TIME RANGE: +'+ data.range + '$');
								} else {
									$('.price_range').text('TIME RANGE: '+ data.range + '$');
								}
								
								for(item in data.possible){
									var name = '';
									if(item == 'rsi'){
										name = 'BELOW LONG RSI';
									} else if(item == 'price'){
										name = 'PRICE ABOVE LOW LIMIT';
									} else if(item == 'level'){
										name = 'PRICE BELOW TOP LEVEL';
									} else if(item == 'time'){
										name = 'DURING OPERATE TIME';
									} else if(item == 'money'){
										name = 'ENOUGH MONEY';
									}
									$('.possible-' + item).text(name + ': '+ data.possible[item]);
									if(data.possible[item] == 'no'){
										$('.possible-' + item).addClass('text-danger');
										$('.possible-' + item).removeClass('text-success');
									} else {
										$('.possible-' + item).removeClass('text-danger');
										$('.possible-' + item).addClass('text-success');
									}
								}
								
								$('.range_stagnant').text('RSI S: '+ data.range_stagnant);
								$('.range_stagnant_long').text('RSI L: '+ data.range_stagnant_long);
								$('.range_stagnant_extra_long').text('RSI XL: '+ data.range_stagnant_extra_long);
								$('.server_time').text('SERVER: '+ data.server_time);
								$('.price_array').html(data.stats);
								$('.time_lowest_limit').html('LOW LIMIT: '+ data.time_lowest_limit);
								
								if(data.range > 0){
									$('.price_range').removeClass('text-danger');
									$('.price_range').addClass('text-success');
								} else {
									$('.price_range').addClass('text-danger');
									$('.price_range').removeClass('text-success');
								}
								
								var today_day = $('.days .day.btn-success').attr('day');
								var today_date = '<?php echo date('Y-m-d'); ?>';
								$('.time_counter').text('Refresh: 3');
								$('.time_counter').attr('counter', '3');

								$('.stagnant').text('STAGNANT PRICE: '+ data.stagnant);
								
								if(data.stagnant == 'yes'){
									$('.stagnant').removeClass('text-danger');
									$('.stagnant').addClass('text-success');
								} else {
									$('.stagnant').addClass('text-danger');
									$('.stagnant').removeClass('text-success');
								}
								
								<?php if(isset($config_api_data['hide']) && $config_api_data['hide'] != 'hide'){ ?>
									$('.sold_at').text('LAST SOLD AT: '+ data.sold_at + '$');
									$('.limit_low').text('LOW SELL LIMIT: '+ data.limit_low + '$');
									$('.limit_high').text('HIGH SELL LIMIT: '+ data.limit_high + '$');
									$('.estimation').text('ESTIMATION: '+ data.estimation + '$');
									$('.low_detect').text('PRICE DETECTION: '+ data.low_detect + '$');
								<?php } ?>

								if(today_day == today_date){
									$('.range_today').text('TODAY\'S RANGE: '+ data.range_today + '$');
									<?php if(isset($config_api_data['hide']) && $config_api_data['hide'] != 'hide'){ ?>
									$('.total').text('ACCOUNT: '+ data.total + '$');
									$('.difference').text('TOTAL FROM START: '+ data.difference + '$');
									$('.total_today').text('TODAY\'S TOTAL: '+ data.total_today + '$');
									$('.in_logs').text('LOGS TOTAL: '+ data.in_logs + '$');
									$('.percentage').text('TODAY\'S PERCENT: '+ data.percent + '%');
									$('.total_median').text('LOGS MEDIAN: '+ data.median + '$');
									<?php } ?>
									$('.highest').text('TODAY\'S HIGHEST: '+ data.highest + '$');
									$('.lowest').text('TODAY\'S LOWEST: '+ data.lowest + '$');
									
									
									if(data.median > 0){
										$('.total_median').removeClass('text-danger');
										$('.total_median').addClass('text-success');
									} else {
										$('.total_median').addClass('text-danger');
										$('.total_median').removeClass('text-success');
									}
									
									if(data.percent > 0){
										$('.percentage').removeClass('text-danger');
										$('.percentage').addClass('text-success');
									} else {
										$('.percentage').addClass('text-danger');
										$('.percentage').removeClass('text-success');
									}
									
									if(data.difference > 0){
										$('.difference').removeClass('text-danger');
										$('.difference').addClass('text-success');
									} else {
										$('.difference').addClass('text-danger');
										$('.difference').removeClass('text-success');
									}
								
									if(data.total_today > 0){
										$('.total_today').removeClass('text-danger');
										$('.total_today').addClass('text-success');
									} else {
										$('.total_today').addClass('text-danger');
										$('.total_today').removeClass('text-success');
									}
								}
								if(data.price_sell_all == 'done'){
									sell_all_toggle = 0;
									sell_all_plus_10_toggle = 0;
									sell_all_plus_20_toggle = 0;
									$('.sell_all').prop("disabled", false);
									$('.sell_unlock').prop("disabled", false);
								}
								sell_unlock_toggle = 0;
								sell_unlock_minus_20_toggle = 0;
								sell_unlock_minus_10_toggle = 0;
								detect_lowest_switch_toggle = '';
								detect_lowest_toggle = 0;
							}
						}
					});
				}
				
				function counter_time() {
					var value = $('.time_counter').attr('counter');
					$('.time_counter').text('Refresh: ' + (value));
					$('.time_counter').attr('counter', (value - 1));
					setTimeout(counter_time, 1000);
				}
				
				counter_time();
				
				function timeout_price() {
					price();
					setTimeout(timeout_price, 3000);
				}
				timeout_price();
			<?php } ?>
			
			<?php if(isset($_SESSION['user_in']) && ($clean_uri == 'charts_binance.php' || $clean_uri == 'charts_coinbase.php' || $clean_uri == 'charts_kraken.php')){ ?>
				function price_assets(){
					$.ajax({
						dataType: "json",
						type: "POST",
						url: "json_request/output_assets.php",
						data: {password:'<?php echo $_SESSION['user_in'] ?>'},
						success: function (data){
							if (data !== null) {
								for(item in data.build_value){
									$('#' + item).css('width', data.build_value[item]['value'] + '%');
									$('#' + item).html(data.build_value[item]['label']);
								}
							}
							$('.time_counter').text('Refresh: 9');
							$('.time_counter').attr('counter', '10');
						}
					});
				}
				
				function counter_time_assets() {
					var value = $('.time_counter').attr('counter');
					$('.time_counter').text('Refresh: ' + (value - 1));
					$('.time_counter').attr('counter', (value - 1));
					setTimeout(counter_time_assets, 1000);
				}
				
				counter_time_assets();
				
				function timeout_price_assets() {
					price_assets();
					setTimeout(timeout_price_assets, 9000);
				}
				timeout_price_assets();
			<?php } ?>
			
			<?php if(isset($_SESSION['user_in']) && ($clean_uri == 'total_binance.php' || $clean_uri == 'total_coinbase.php' || $clean_uri == 'total_kraken.php')){ ?>
				<?php if(isset($_GET['run'])){ ?>
					var run_compile = 1;
				<?php } else { ?>
					var run_compile = 0;
				<?php } ?>
				
				$('.run').click(function(e) {
					timeout_price_assets();
					e.preventDefault();
					run_compile = 1;
					return false;
				});
				
				$('.stop').click(function(e) {
					e.preventDefault();
					run_compile = 0;
					return false;
				});
				
				function price_assets(){
					if(run_compile == 1){
						if($('.next-time').length > 0){
							window.location = $('.next-time').attr('href') + "&run=1";
						}
					}
				}
				
				function counter_time_assets() {
					var value = $('.time_counter').attr('counter');
					if(value > 0){
						$('.time_counter').text('Refresh: ' + (value - 1));
						$('.time_counter').attr('counter', (value - 1));
						setTimeout(counter_time_assets, 1000);
					}
				}
				
				$('.time_counter').text('Refresh: 20');	
				$('.time_counter').attr('counter', '21');
				counter_time_assets();
				
				function timeout_price_assets() {
					setTimeout(price_assets, 20000);
				}
				timeout_price_assets();
			<?php } ?>

			setTimeout(function(){
				$('.particles-js-canvas-el').animate({
					opacity: 0
				}, 3000, function() {
				// Animation complete.
				});
			}, 1000)
			
			$('body').css('visibility', 'visible');
			$('.row').css('display', 'block');
		});
	</script>
	<?php if (isset($_SESSION['logged_in'])){ ?>
	<style type="text/css">
		@import url('https://fonts.googleapis.com/css?family=Open+Sans:300');
		
		body{
			visibility:hidden;
			margin: 0px;
			padding: 0px;
			width: 100%;
			background-color: #0f0f0f;
			background-image: url('images/prism.png');
			font-family:'Open Sans',sans-serif;
		}
		
		body * {
			font-weight: lighter !important;
		}
		
		.reveal_hide,
		.toggle_days,
		.scroll_top {
			width: auto !important;
			display: inline-block !important;
			height: auto !important;
		}
		
		.content-canvas {
		}
		
		.inline-block {
			display:inline-block;
		}
		
		.particles-js-canvas-el {
			position: absolute;
			top: 0px;
			z-index: -1;
		}
		
		label {
			color: #FFD700;
			
		}
		
		body p,
		.label *,
		.label,
		body th *,
		body th {
			color: #ffffff;
		}
		
		body h1,
		body h2,
		body h3,
		body h4 {
			color: #aacee3;
		}
		
		body .btn {
			font-weight: lighter !important;
			padding: 6px 8px !important;
			margin-top: 5px;
			line-height: 1em;
			opacity: 0.8;
		}
		
		body .btn:hover {
			opacity: 1;
		}
		
		body form .btn {
			height: 46px;
			padding: 12px 16px;
			width: 100%;
			background-color: #79a736 !important;
		}
		
		.btn-success {
			background-color: #69951a;
		}
		
		.transaction_anchors .btn {
			margin-bottom: 5px !important;
		}
		
		.stats tr td {
			color: #dddddd;
			border-top: 1px solid #444444 !important;
			text-transform: uppercase;
		}
		
		.stats tr:nth-child(1) {
			background-color: rgba(80,80,80,0.2);
		}
		
		.stats tr:nth-child(1) td {
			padding-top: 5px;
			padding-bottom: 5px;
			border: none !important;
		}
		
		.stats tr:nth-child(2) td {
			border: none !important;
		}
		
		.stats tr td.text-warning {
			color:#FFD700;
		}
		
		.stats tr td.text-success {
			color:#93c54b;
		}
		
		.stats tr td.text-danger {
			color:#d9534f;
		}
		
		.box-stat {
			padding: 10px;
		}
		
		.price_array {
			line-height: 1.2em;
			color: #ffffff;
		}
		.price_array p {
			margin: 0px;
			font-size: 12px;
		}
		
		.text-disable,
		.text-selling {
			color:#d9534f;
		}
		
		.text-balance {
			color:#8e8e8e;
		}
		
		.text-empty	{
			color:#29abe0;
		}
		
		.text-warning {
			color:#FFD700;
		}
		
		.report_table tr td:nth-child(2) p{
			text-transform: uppercase !important;
			opacity: 0.8;
		}
		
		[data-toggle="popover"]{
			cursor: pointer;
		}
		
		.popover {
			background-color: transparent;
			border: none !important;
		}
		
		.popover-content {
			border-radius: 10px;
			background-color: rgba(40,40,40,0.8);
			color: #cccccc !important;
			border: 1px dashed rgba(150,150,150,0.4);
			font-family: Open Sans, sans-serif;
			text-align: center;
		}
		
		.popover.bottom > .arrow::after {
			border-bottom-color: rgba(0,0,0,0.2);
		}
		
		* {
		  scrollbar-width: thin;
		  scrollbar-color: #666666 #222222;
		}

		*::-webkit-scrollbar {
		  width: 12px;
		  background-color: #222222;
		}

		*::-webkit-scrollbar-track {
		  background: #666666;
		}

		*::-webkit-scrollbar-thumb {
		  background-color: #222222;
		  border-radius: 20px;
		  border: 3px solid #666666;
		}
		
		select,
		input {
			background-color: #444444 !important;
			color: #ffffff !important;
			opacity: 0.8;
		}
		
		.scroll {
			overflow-y: hidden;
			height: 400px !important;
			background-color: rgba(0,0,0, 0.2);
		}
		
		.scroll > table {
			margin-top: 20px;
		}

		#my-chart.column {
			padding-top: 50px;
			height: 300px;
			margin: 0 auto;
			margin-top: 10px;
		}
		
		#my-chart.column td,
		#my-chart.column th	{
			width: 78px !important;
		}
		
		#my-chart.column td {
			border-left: 1px solid #222222;
			border-right: 1px solid #222222;
		}
		
		#my-chart.column th	{
			width: 100% !important;
		}
		
		.label-success {
			background-color: #bf3b3b !important;
		}
		
		.label-danger {
			background-color: #4a914a !important;
		}
		
		.charts-css.column tbody tr.now td:after {
			position: absolute;
			bottom: 0px;
			content:'';
			height: 80px;
			display:block;
			left: 0px;
			width: 2px;
			margin: 0 auto;
			background-color: #ffffff;
		}
		
		.charts-css.column tbody tr td {
			background-color: #bf3b3b !important;
			text-align: center;
			max-height: 100% !important;
		}
		
		.charts-css.column tbody tr td .line-text {
			color: #ffffff;
			bottom: 0px;
			position: absolute;
			width: 60px;
			left: -30px;
			height: 290px;
		}
		
		.charts-css.column tbody tr td .line-up {
			border-left: 1px dotted #ffffff;
			width: 1px;
			height: 300px;
			bottom: 0px;
			margin: 0 auto;
			position: absolute;
			left: 30px;
		}
		
		.charts-css.column tbody tr td .line-up:before {
			content: '';
			border-top: 1px dotted #ffffff;
			height: 1px;
			width: 60px;
			top: 0px;
			left: -60px;
			position: absolute;
		}
		
		.charts-css.column tbody tr td .line-up:after {
			content: '';
			border-left: 1px dotted #ffffff;
			width: 1px;
			height: 300px;
			bottom: 0px;
			position: absolute;
			left: -60px;
		}
		
		.charts-css.column tbody tr td.grey {
			background-color: #4a914a !important;
		}
		
		.charts-css.column tbody tr th.value .gold,
		.gold	{
			background-color: #ffbc00 !important;
			text-shadow: 1px 1px 4px rgb(0 0 0);
			color: #ffffff !important;
			cursor: pointer;
			opacity: 0.9;
		}
		
		.charts-css.column tbody tr th.value .gold  *,
		.gold *	{
			color: #ffffff !important;
		}
		
		.value {
			bottom: -40px !important;
			width: 100%;
			z-index: 99999;
			font-size: 0.8em;
		}
		.sticky {
			height: 100px;
			position: fixed;
		}
		
		.range-tag {
			padding: 1px 2px !important;
		}
		
		tr.red .line-green.start {
			border-left: 2px solid #4a914a;
			width: 2px;
			height: 250px;
			bottom: 0px;
			position: absolute;
			left: 50%;
		}
		
		tr.red .line-green.start:before {
			content: '';
			border-top: 2px solid #4a914a;
			height: 2px;
			width: 60px;
			top: 0px;
			position: absolute;
		}
		
		tr.red .line-green.end {
			border-left: 2px solid #4a914a;
			width: 2px;
			height: 250px;
			bottom: 0px;
			position: absolute;
			left: 50%;
		}
		
		tr.red .line-green.end:before {
			content: '';
			border-top: 2px solid #4a914a;
			height: 2px;
			width: 60px;
			top: 0px;
			left: -60px;
			position: absolute;
		}
		
		tr .line-sell.start_sell {
			position: absolute;
			height: 220px;
			bottom: 0px;
			left: 0px;
			z-index: 9999;
		}
		tr .line-sell.start_sell:before {
			padding-top: 1px;
			content: 'Down: <?php echo (isset($config_api_data['sell_at']) ? number_format($config_api_data['sell_at'], 2, '.', '') : -0.5); ?> med';
			font-size: 9px;
			color: #ffffff;
			background: rgba(191, 59, 59, 0.1);
			height: 15px;
			width: <?php echo (isset($config_api_data['minute_amount']) ? $config_api_data['minute_amount'] * 80 : 10 * 80); ?>px;
			top: 0px;
			position: absolute;
		}
		tr .line-buy.start_buy {
			position: absolute;
			bottom: 0px;
			height: 240px;
			left: 0px;
			z-index: 9999;
		}
		tr .line-buy.start_buy:before {
			padding-top: 1px;
			content: 'Up:  <?php echo (isset($config_api_data['buy_at']) ? number_format($config_api_data['buy_at'], 2, '.', '') : 0.5); ?> med';
			font-size: 9px;
			color: #ffffff;
			background: rgba(74, 145, 74, 0.1);
			height: 15px;
			width: <?php echo (isset($config_api_data['minute_amount']) ? $config_api_data['minute_amount'] * 80 : 10 * 80); ?>px;
			top: 0px;
			position: absolute;
		}
		.label {
			padding: 2px 4px !important;
			font-size: 100%;
		}
		
		.time {
			display: inline !important;
		}
		
		.report_table tr {
			border-top: 1px solid #555555 !important;
			border-bottom: none !important;
		}
		
		.report_table td {
			border: none !important;
		}
		
		.report_table td p {
			font-size: 12px;
			text-transform: uppercase;
			margin-bottom: 0px;
		}
		
		table.dashboard tr td {
			text-align:center;
			border: 0px;
			box-sizing: border-box;
			min-height: 64px !important;
		}
		
		table.dashboard {
			margin: 5px 0px;
			padding: 5px 0px;
			border-top: 1px solid #444444;
			border-bottom: 1px solid #444444;
		}
		
		.main .item_dashboard {
			padding: 0px 10px;
		}
		
		.item_dashboard {
			display:inline-block;
			margin: 2px 0px;
			text-transform: uppercase;
		}
		
		table.dashboard tr td,
		table.dashboard tr td h5 {
			text-align: left;
		}
		
		table.dashboard tr td h5 {
			display:block !important;
		}
		
		.view-33 {
			display:none !important;
		}
		
		@media (max-width: 1400px){
			.stats tr td {
				display:inline-block;
				text-align:center;
				border: none !important;
				padding: 0px !important;
				width: auto !important;
			}
			
			.stats tr td:after {
				content: ', ';
			}

			.stats tr td:last-child:after {
				display:none;
			}
			
			.stats tr {
				padding: 10px 0px;
				text-align:center;
				display:block;
				border-top: 1px solid #888888;
			}
			
			.stats tr:nth-child(1) {
				border: none !important;
			}
			
			.stats tr:nth-child(2) {
				border: none !important;
			}
		}
		
		@media (max-width: 1200px) {
			table.dashboard tr td {
				display: block;
				float: left;
				width: 25%;
				padding: 5px;
			}
			
			table.dashboard tr td:nth-child(4n-1), 
			table.dashboard tr td:nth-child(4n-2),
			table.dashboard tr td:nth-child(4n-3) {
				border-right: 1px solid #444444;
			}
		}
		
		@media (max-width: 979px) {
			table.dashboard tr td {
				width: 33.3333%;
			}
			
			.view-33 {
				display:block !important;
			}
			
			table.dashboard tr td:nth-child(4n-1), 
			table.dashboard tr td:nth-child(4n-2),
			table.dashboard tr td:nth-child(4n-3) {
				border-right: 0px;
			}
			
			table.dashboard tr td:nth-child(3n-1), 
			table.dashboard tr td:nth-child(3n-2) {
				border-right: 1px solid #444444;
			}
			
			.scroll {
				margin-bottom: 10px;
			}
			
			h3 {
				margin: 0px !important;
			}

			#my-chart tr th {
				font-size: 70% !important;
			}
			
			br {
				display:none !important;
			}
			
			.progress br {
				display:block !important;
			}
			
			.report_table tr td {
				display:block;
				text-align:center;
				border: none !important;
				width: 100%;
				padding: 5px !important;
			}
			.report_table tr td p {
				margin: 0px;
			}
			.report_table tr td br:after {
				content: ' ';
			}
			.btn {
				margin-top: 5px;
			}
		}
		
		@media (max-width: 768px) {
			.view-33 {
				display:none !important;
			}
			
			table.dashboard tr td {
				width: 50%;
			}
			
			table.dashboard tr td:nth-child(3n-1), 
			table.dashboard tr td:nth-child(3n-2) {
				border-right: 0px;
			}
			
			table.dashboard tr td:nth-child(2n-1) {
				border-right: 1px solid #444444;
			}
		}
		
		@media (max-width: 480px) {
			table.dashboard tr td h5 {
				font-size: 13px;
			}
		}
		
		<?php if(isset($config_api_data['hide']) && $config_api_data['hide'] == 'hide'){ ?>
			table.dashboard tr td,
			table.dashboard tr td h5 {
				float: none !important;
				width: auto !important;
				display:inline-block !important;
				min-height: inherit !important;
				text-align: center !important;
			}
			
			table.dashboard tr td {
				border-right: 0px !important;
			}
			
			table.dashboard tr td h5 {
				margin: 0px 5px !important;
			}
			
			table.dashboard tr {
				text-align: center !important;
			}
			
			table.dashboard tr td {
				width: auto !important;
			}
			
			table.dashboard tr td.anti-spy,
			.anti-spy,
			table.dashboard tr td.view-33{
				display:none !important;
			}
		<?php } ?>
		
		<?php if(isset($config_api_data['history']) && $config_api_data['history'] == 'hide'){ ?>
			.history-hide {
				display:none !important;
			}
		<?php } ?>
	</style>
	<?php } else { ?>
	<style type="text/css">
		
		html {
		  background-color: #56baed;
		}

		body {
		  font-family: "Poppins", sans-serif;
		  height: 100vh;
		  background: url(images/webb.png);
		}

		a {
		  color: #92badd;
		  display:inline-block;
		  text-decoration: none;
		  font-weight: 400;
		}

		h2 {
		  text-align: center;
		  font-size: 16px;
		  font-weight: 600;
		  text-transform: uppercase;
		  display:inline-block;
		  margin: 40px 8px 10px 8px; 
		  color: #cccccc;
		}
		
		.wrapper {
		  display: flex;
		  align-items: center;
		  flex-direction: column; 
		  justify-content: center;
		  width: 100%;
		  min-height: 100%;
		  padding: 20px;
		}

		#formContent {
		  -webkit-border-radius: 10px 10px 10px 10px;
		  border-radius: 10px 10px 10px 10px;
		  background: #fff;
		  padding: 30px;
		  width: 90%;
		  max-width: 450px;
		  position: relative;
		  padding: 0px;
		  -webkit-box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3);
		  box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3);
		  text-align: center;
		}

		#formFooter {
		  background-color: #f6f6f6;
		  border-top: 1px solid #dce8f1;
		  padding: 25px;
		  text-align: center;
		  -webkit-border-radius: 0 0 10px 10px;
		  border-radius: 0 0 10px 10px;
		}

		h2.inactive {
		  color: #cccccc;
		}

		h2.active {
		  color: #0d0d0d;
		  border-bottom: 2px solid #5fbae9;
		}

		input[type=button], input[type=submit], input[type=reset]  {
		  background-color: #56baed;
		  border: none;
		  color: white;
		  padding: 15px 80px;
		  text-align: center;
		  text-decoration: none;
		  display: inline-block;
		  text-transform: uppercase;
		  font-size: 13px;
		  -webkit-box-shadow: 0 10px 30px 0 rgba(95,186,233,0.4);
		  box-shadow: 0 10px 30px 0 rgba(95,186,233,0.4);
		  -webkit-border-radius: 5px 5px 5px 5px;
		  border-radius: 5px 5px 5px 5px;
		  margin: 5px 20px 40px 20px;
		  -webkit-transition: all 0.3s ease-in-out;
		  -moz-transition: all 0.3s ease-in-out;
		  -ms-transition: all 0.3s ease-in-out;
		  -o-transition: all 0.3s ease-in-out;
		  transition: all 0.3s ease-in-out;
		}

		input[type=button]:hover, input[type=submit]:hover, input[type=reset]:hover  {
		  background-color: #39ace7;
		}

		input[type=button]:active, input[type=submit]:active, input[type=reset]:active  {
		  -moz-transform: scale(0.95);
		  -webkit-transform: scale(0.95);
		  -o-transform: scale(0.95);
		  -ms-transform: scale(0.95);
		  transform: scale(0.95);
		}

		input[type=text] {
		  background-color: #f6f6f6;
		  border: none;
		  color: #0d0d0d;
		  padding: 15px 32px;
		  text-align: center;
		  text-decoration: none;
		  display: inline-block;
		  font-size: 16px;
		  margin: 5px;
		  width: 85%;
		  border: 2px solid #f6f6f6;
		  -webkit-transition: all 0.5s ease-in-out;
		  -moz-transition: all 0.5s ease-in-out;
		  -ms-transition: all 0.5s ease-in-out;
		  -o-transition: all 0.5s ease-in-out;
		  transition: all 0.5s ease-in-out;
		  -webkit-border-radius: 5px 5px 5px 5px;
		  border-radius: 5px 5px 5px 5px;
		}

		input[type=text]:focus {
		  background-color: #fff;
		  border-bottom: 2px solid #5fbae9;
		}

		input[type=text]:placeholder {
		  color: #cccccc;
		}
		
		.fadeInDown {
		  -webkit-animation-name: fadeInDown;
		  animation-name: fadeInDown;
		  -webkit-animation-duration: 1s;
		  animation-duration: 1s;
		  -webkit-animation-fill-mode: both;
		  animation-fill-mode: both;
		}

		@-webkit-keyframes fadeInDown {
		  0% {
			opacity: 0;
			-webkit-transform: translate3d(0, -100%, 0);
			transform: translate3d(0, -100%, 0);
		  }
		  100% {
			opacity: 1;
			-webkit-transform: none;
			transform: none;
		  }
		}

		@keyframes fadeInDown {
		  0% {
			opacity: 0;
			-webkit-transform: translate3d(0, -100%, 0);
			transform: translate3d(0, -100%, 0);
		  }
		  100% {
			opacity: 1;
			-webkit-transform: none;
			transform: none;
		  }
		}
		
		@-webkit-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
		@-moz-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
		@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

		.fadeIn {
		  opacity:0;
		  -webkit-animation:fadeIn ease-in 1;
		  -moz-animation:fadeIn ease-in 1;
		  animation:fadeIn ease-in 1;

		  -webkit-animation-fill-mode:forwards;
		  -moz-animation-fill-mode:forwards;
		  animation-fill-mode:forwards;

		  -webkit-animation-duration:1s;
		  -moz-animation-duration:1s;
		  animation-duration:1s;
		}

		.fadeIn.first {
		  -webkit-animation-delay: 0.4s;
		  -moz-animation-delay: 0.4s;
		  animation-delay: 0.4s;
		}

		.fadeIn.second {
		  -webkit-animation-delay: 0.6s;
		  -moz-animation-delay: 0.6s;
		  animation-delay: 0.6s;
		}

		.fadeIn.third {
		  -webkit-animation-delay: 0.8s;
		  -moz-animation-delay: 0.8s;
		  animation-delay: 0.8s;
		}

		.fadeIn.fourth {
		  -webkit-animation-delay: 1s;
		  -moz-animation-delay: 1s;
		  animation-delay: 1s;
		}
		
		.underlineHover:after {
		  display: block;
		  left: 0;
		  bottom: -10px;
		  width: 0;
		  height: 2px;
		  background-color: #56baed;
		  content: "";
		  transition: width 0.2s;
		}

		.underlineHover:hover {
		  color: #0d0d0d;
		}

		.underlineHover:hover:after{
		  width: 100%;
		}

		*:focus {
			outline: none;
		} 

		#icon {
		  width:60%;
		}
	</style>
<?php } ?>
	
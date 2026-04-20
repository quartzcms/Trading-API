# Trading-API

<p>Trading-API application let you view a dashboard with a daily, weekly, monthly, yearly on a median scale using a risk factor as well as a weekly, monthly, yearly days addition view. It is used to accomplish automated transaction in a portfolio to minimize your risk of losses and schedule automatic actions with complex processes. With this application comes many features:</p>

<ul>
	<li>1 median indicator, 3 RSI calculation, 1 ratio, 3 moving average indicators,</li>
	<li>4 different mode: experimental, detect lowest, bearish, bullish,</li>
	<li>works using Alpha-Vantage API and paid Crypto News API with sentiment analysis,</li>
	<li>one automated statement feature to collect trading data,</li>
	<li>3 different API to use including Binance, Coinbase and Kraken,</li>
	<li>2 graphs to show open, closed, low, high and income amount (absolute and daily)</li>
	<li>1 feature to view extra invested funds within the portfolio,</li>
	<li>many features to set for the automated bot including: operating time, percentage level, gained limit, etc..</li>
</ul>

<p>To install the application, you will need to set a cron task on the A.I. file and the output file. Here are the lines you will need to add to the cron file:</p>

<pre>* * * * * cd /var/www/example.com/public_html/json_request &amp;&amp; sudo -u www-data php output_process.php
* * * * * cd /var/www/example.com/public_html/json_request &amp;&amp; sudo bash output_ai.sh</pre>

<p>You may replace the /var/www/example.com/public_html path with your document_root path in the above command and within the config.php file. Additional information about the license key for Alpha-Vantage can be found in the config.php file. You may also set a login and password to access the dashboard within the config.php file.</p>

<p>Bcause the script uses intensive file edits, you will have to set output_buffering to -1 in the PHP settings. Make sure you also always run the script with an SSL for protection and chmod files to make them writable.</p>

<p>In the /json_request folder you will find the output_ai.sh which contains www-data user. If your apache user is different than www-data, you will need to set this one in the output_ai.sh (which execute 1 call each second) and for the cron task above here:</p>

<pre>* * * * * cd /var/www/example.com/public_html/json_request &amp;&amp; sudo -u <span style="background-color:#ffff00">www-data</span> php output_process.php
* * * * * cd /var/www/example.com/public_html/json_request &amp;&amp; sudo bash output_ai.sh</pre>

<p>---------------------</p>

<h4>Permissions of files</h4>

<p>The files should be in writing permissions.</p>

<h4>Requirements</h4>

<p>These server requirements are needed for all versions:</p>

<ul>
	<li>Apache: 2.2 or higher</li>
	<li>SSL Certificate</li>
	<li>Cron tasks edition</li>
	<li>PHP: 7.0 to 7.2</li>
	<li>Storage: 100 MB or higher</li>
	<li>Output Buffering: -1</li>
	<li>MEMORY_LIMIT: 256 MB or higher</li>
	<li>VPS or shared hosting</li>
</ul>

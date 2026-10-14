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

<p>To run the automated market-data update and trading tasks, schedule the PHP entry-point wrappers in <code>json_request</code>. These wrappers load the application bootstrap and configuration, then delegate the work to services in <code>core/</code>; do not schedule the core service class files directly. Run the commands from <code>json_request</code> because the wrappers and the AI shell script use paths relative to that directory.</p>

<pre>* * * * * cd /var/www/example.com/public_html/json_request &amp;&amp; sudo -u www-data php output_process.php
* * * * * cd /var/www/example.com/public_html/json_request &amp;&amp; sudo bash output_ai.sh</pre>

<p>Replace <code>/var/www/example.com/public_html</code> with your application document root in both commands and in <code>config.php</code>. The market-data wrapper is <code>json_request/output_process.php</code> and delegates to <code>Core\Market\MarketDataUpdateService</code>. The AI shell script repeatedly runs <code>json_request/output_ai.php</code>, which delegates to <code>Core\Trading\AiTradingService</code>. Additional information about the Alpha-Vantage license key, and the dashboard login and password, can be set in <code>config.php</code>.</p>

<p>Session data is stored in the project-root <code>sessions/data</code> directory. Keep the existing session data folders there when deploying; the application reads and writes this location independently of the current working directory.</p>

<p>Bcause the script uses intensive file edits, you will have to set output_buffering to -1 in the PHP settings. Make sure you also always run the script with an SSL for protection and chmod files to make them writable.</p>

<p><code>output_ai.sh</code> runs the AI wrapper once per second for about one minute. If your web-server account is not <code>www-data</code>, change the user in <code>json_request/output_ai.sh</code> and in the <code>sudo -u</code> portion of the market-data cron command to the account that owns the application files.</p>

<p>---------------------</p>

<h4>Permissions of files</h4>

<p>The files should be in writing permissions.</p>

<h4>Requirements</h4>

<p>These server requirements are needed for all versions:</p>

<ul>
	<li>Apache: 2.2 or higher</li>
	<li>SSL Certificate</li>
	<li>Cron tasks edition</li>
	<li>PHP: 7.0 to 7.4</li>
	<li>Storage: 100 MB or higher</li>
	<li>Output Buffering: -1</li>
	<li>MEMORY_LIMIT: 256 MB or higher</li>
	<li>VPS or shared hosting</li>
</ul>

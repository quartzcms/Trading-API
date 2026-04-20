<?php
	$clean_uri = explode('?', basename($_SERVER['REQUEST_URI']));
	$clean_uri = $clean_uri[0];
?>
<div class="wrapper fadeInDown">
  <div id="formContent">
	<!-- Tabs Titles -->

	<!-- Icon -->
	<div class="fadeIn first">
		<br />
	  <p align="center"><img src="images/logo.png" id="icon" style="width: 50px;" width="50" height="auto" alt="User Icon" /></p>
	</div>

	<!-- Login Form -->
	<form action="<?php echo $clean_uri; ?>" method="post">
	  <input type="text" id="login" class="fadeIn second" name="login" placeholder="Login">
	  <input type="text" id="password" class="fadeIn third" name="password" placeholder="Password">
	  <input type="submit" class="fadeIn fourth" value="Log In">
	</form>

  </div>
</div>
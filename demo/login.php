<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */

require 'wsinit.php';


try
{
	if (Principal::getCurrent()->isAuthenticated())
		RequestUtil::redirect('index.php');
}
catch (Exception $e)
{
	echo 'Error: ', htmlentities($e->getMessage());
	echo '<hr>';
	echo '<a href="index.php">Home</a>';
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>phpdev</title>

    <link href="css/bootstrap.css" type="text/css" rel="stylesheet"/>
    <link href="css/general.css" type="text/css" rel="stylesheet"/>

    <script src="js/jquery.js"></script>
    <script src="js/jquery.ui.core.js"></script>
    <script src="js/jquery.ui.widget.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/json.js"></script>
    <script src="js/common.js"></script>
	<script src="js/jquery.validate.js"></script>
	<script src="js/jquery.ui-login-user.js"></script>
</head>
<body>
<header>
    <div class="navbar">
        <div class="navbar-inner">
            <div class="container">
                <!-- Be sure to leave the brand out there if you want it shown -->
                <a class="brand" href="index.php">phpdev</a>
            </div>
        </div>
    </div>
</header>


<div id="content" class="container">

    <div class="row">

		<form class="form-horizontal ui-login-user" style="width: 600px; margin: 0 auto;">

			<fieldset>
				<legend>Login</legend>
			</fieldset>

			<div class="control-group">
				<label class="control-label" for="user-name">User Name:</label>
				<div class="controls">
					<input id="user-name" class="input-xlarge required" type="text" maxlength="20"/>
                    <p class="help-block" style="display: none;">
                        <label class="error"></label>
                    </p>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="user-password">Password:</label>
				<div class="controls">
					<input id="user-password" class="input-xlarge required" type="password" maxlength="32"/>
                    <p class="help-block" style="display: none;">
                        <label class="error"></label>
                    </p>
				</div>
			</div>

			<div class="form-actions">
				<button class="btn btn-small btn-primary">Login</button>
                <a class="btn btn-small" href="register.php">I don't have an account</a>
			</div>

		</form>

	</div>

</div>


<footer>

    <div class="container">
        <div class="row">
            <p>Copyright &copy; 2012 by Cristian Trifan</p>
        </div>
    </div>

</footer>


<script>
	jQuery.noConflict();
	jQuery(function($)
	{
		$('.ui-login-user').loginUser();
	});

</script>

</body>
</html>
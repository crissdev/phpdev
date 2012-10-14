<?php
/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */

require 'wsinit.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>phpdev</title>

    <link href="css/bootstrap.css" type="text/css" rel="stylesheet"/>
    <link href="css/bootstrap-responsive.css" type="text/css" rel="stylesheet"/>
    <link href="css/general.css" type="text/css" rel="stylesheet"/>

    <script src="js/jquery.js"></script>
    <script src="js/jquery.ui.core.js"></script>
    <script src="js/jquery.ui.widget.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/json.js"></script>
    <script src="js/common.js"></script>
    <script src="js/jquery.validate.js"></script>
    <script src="js/jquery.ui-current-user.js"></script>
    <script src="js/jquery.ui-user-profile.js"></script>
</head>
<body>
<header>
    <div class="navbar">
        <div class="navbar-inner">
            <div class="container">
                <a class="brand" href="index.php">phpdev</a>
                <div class="btn-group pull-right ui-current-user">
                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="icon icon-user"></i>
                        <span class="user-name">User</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="login.php">Login</a></li>
                        <li class="divider"></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="logout.php">Log Out</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<div id="content" class="container">

    <div class="row">

        <form class="form-horizontal ui-user-profile" style="width: 600px; margin: 0 auto;">

            <fieldset>
                <legend>Profile</legend>
            </fieldset>

            <div class="control-group">
                <label class="control-label" for="user-name">User Name:</label>
                <div class="controls">
                    <input id="user-name" class="input-xlarge" type="text" readonly="readonly"/>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="user-email">Email:</label>
                <div class="controls">
                    <input id="user-email" name="user-email" class="input-xlarge required email" type="text"/>
                    <p class="help-block" style="display: none;">
                        <label class="error"></label>
                    </p>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="user-first-name">First Name:</label>
                <div class="controls">
                    <input id="user-first-name" name="user-first-name" class="input-xlarge" type="text"/>
                    <p class="help-block" style="display: none;">
                        <label class="error"></label>
                    </p>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="user-last-name">Last Name:</label>
                <div class="controls">
                    <input id="user-last-name" name="user-last-name" class="input-xlarge" type="text"/>
                    <p class="help-block" style="display: none;">
                        <label class="error"></label>
                    </p>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-small btn-primary">Update</button>
                <a class="btn btn-small" href="javascript:window.history.back();">Cancel</a>
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
        $('.ui-current-user').currentUser();
		$('.ui-user-profile').userProfile();
    });
</script>
</body>
</html>
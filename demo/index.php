<?php

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
    <script src="js/bootstrap.js"></script>
</head>
<body>
	<header>
		<div class="navbar">
			<div class="navbar-inner">
				<div class="container">

					<!-- .btn-navbar is used as the toggle for collapsed navbar content -->
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>

					<!-- Be sure to leave the brand out there if you want it shown -->
					<a class="brand" href="#">phpdev</a>
<?php
//					<!-- Everything you want hidden at 940px or less, place within here -->
//					<div class="nav-collapse">
//						<ul class="nav">
//							<li><a href="">Home</a></li>
//						</ul>
//                        <div class="btn-group pull-right">
//							<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
//								<i class="icon icon-user"></i>
//								<span class="current-user-name">User</span>
//								<span class="caret"></span>
//							</a>
//							<ul class="dropdown-menu">
//								<li><a href="#login">Login</a></li>
//								<li class="divider"></li>
//								<li><a href="#register">Register</a></li>
//							</ul>
//                    	</div>
//					</div>
?>
				</div>
			</div>
		</div>
	</header>

	<div id="content" class="container-fluid">

		<div class="row-fluid">

			<div class="ui-library-info container-fluid" style="margin-top: 60px">

				<div class="title row-fluid">
					<h2>phpdev Library 1.0</h2>
				</div>

				<div class="description row-fluid" style="margin-top: 60px">
					<h4>Description</h4>
					<p>
						<mark>phpdev</mark> was born to make your life easier as a PHP developer and also to make sure
                        your code is safe.
					</p>
				</div>

				<div class="features row-fluid" style="margin-top: 60px;">
					<h4>Features</h4>
					<p>
						<mark>phpdev</mark> contains a lot of features ready to be used and extended. Below is a list of
						what is included in the library.
					</p>
					<ul style="margin-left: 40px;">
                        <li><strong>Membership</strong> module helps you to manager users an roles and it can be combined
						with the <strong>Authentication</strong> and <strong>Authorization</strong> modules.</li>
						<li><strong>Database</strong> module - currently only PostgreSQL support is provided - You'll find
						that talking to a database is much easier and you'll write a lot less code, without making your
						code look `crazy'.</li>
						<li><strong>Session</strong> module helps you to access session without worrying about initialization.</li>
						<li><strong>JSON-RPC</strong> module helps you to have PHP method calls initiated from JavaScript. </li>
						<li><strong>Logging</strong> module helps you to trace different events in the application, allowing you
						to easily fix code issues.</li>
						<li><strong>Utility</strong> module will assist you with frequent tasks such as JSON encoding / decoding,
							security checks, formatting and more.
						</li>
					</ul>
				</div>

				<div class="license row-fluid" style="margin-top: 60px;">
					<h4>Licensing</h4>
					<p>
                        The license of this project is <a href="http://opensource.org/licenses/ms-pl" target="_blank">Microsoft Public License (Ms-PL)</a>
					</p>
				</div>
            </div>

		</div>

	</div>


	<footer style="margin-top: 30px; margin-bottom: 30px; text-align: center; font-size: small;">

		<div class="container">
			<div class="row">
                <p>Copyright &copy; 2012 by Cristian Trifan</p>
			</div>
		</div>

	</footer>

</body>
</html>
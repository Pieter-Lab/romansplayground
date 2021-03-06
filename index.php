<?php
//Error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
//Bring in custom DB wrapper
require __DIR__.'/db_wrapper.php';
//Bring in Composer Libraries
require __DIR__ . '/vendor/autoload.php';
//---------------------------------------------------------------------------------
//Set DB wrapper
$db = new db_wrapper();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="icon" href="../../favicon.ico">

	<title>Romans GMAP Playground</title>
	<!-- Bootstrap core CSS -->
	<link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	<link href="/vendor/twbs/bootstrap/docs/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

	<!-- Custom styles for this template -->
	<link href="/vendor/twbs/bootstrap/docs/examples/jumbotron/jumbotron.css" rel="stylesheet">
	<!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
	<!--[if lt IE 9]><script src="/vendor/twbs/bootstrap/docs/assets/js//ie8-responsive-file-warning.js"></script><![endif]-->
	<script src="/vendor/twbs/bootstrap/docs/assets/js/ie-emulation-modes-warning.js"></script>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<!-- Custom Styles goes here for now -->
    <link href="_assets/css/styles.css" rel="stylesheet">
	<!-- Map Icons -->
		<!-- CSS -->
		<link rel="stylesheet" type="text/css" href="/_assets/maps-icons/css/map-icons.css">
</head>
<body>
<a href="https://romans-new-labpieter.c9users.io/node4-bedroom-detached-house-566" target="_blank">Test External referrer</a>
<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">Romans GMAP Playground</a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<form class="navbar-form navbar-right">
				<div class="form-group">
					<input type="text" placeholder="Email" class="form-control">
				</div>
				<div class="form-group">
					<input type="password" placeholder="Password" class="form-control">
				</div>
				<button type="submit" class="btn btn-success">Sign in</button>
			</form>
		</div><!--/.navbar-collapse -->
	</div>
</nav>
<!-- Main jumbotron for a primary marketing message or call to action -->
<div class="jumbotron">
	<div class="container">
		<h1>Hello, world!</h1>
		<p>This is a template for a simple marketing or informational website. It includes a large callout called a jumbotron and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
		<p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more &raquo;</a></p>
	</div>
</div>
<div class="container">
	<!-- Example row of columns -->
	<div class="row">
		<div class="col-md-8">
			<h3>GMAP</h3>
			<div id="customgmap" class="col-md-12"></div>
		</div>
		<div class="col-md-4">
			<h2>Map Testing!!</h2>
			<p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
			<!-- Auto Lookup --------------------------------------------------------------------------------------- -->
			<div class="form-group">
                <div id="autoAreaLookupCnt">
                    <input id="autoAreaLookup" type="text" class="form-control" placeholder="Area Lookup" aria-describedby="basic-addon2">
                    <div id="autoAreaLookupList" class="col-md-12">
                    </div>
                </div>
            </div>
			<!-- Auto Lookup --------------------------------------------------------------------------------------- -->
		</div>
	</div>

	<hr>

	<footer>
		<p>&copy; 2016 LAB LATERAL, Ltd.</p>
	</footer>
</div> <!-- /container -->
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="/vendor/twbs/bootstrap/docs/assets/js/vendor/jquery.min.js"><\/script>')</script>
<!--Boostrap Framework -->
<script src="/vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="/vendor/twbs/bootstrap/docs/assets/js/ie10-viewport-bug-workaround.js"></script>
<!-- OUR OWN MAGIC js -->
<script src="/_assets/js/main.js" type="application/javascript"></script>
<!-- ---------------------------------------------------------------------------------------------------------------- -->
<!-- Init Google map system -->
<!--Bring in Google maps javascript API -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDhGrqYjWtbqCvspodZvrY-CgfJQ5Adtdo&libraries=places,geometry&callback=LABMAP.init" async defer></script>
<!-- ---------------------------------------------------------------------------------------------------------------- -->
</body>
</html>

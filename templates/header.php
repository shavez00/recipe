<?php
echo <<<EOT
<!DOCTYPE html>
<html class= "none" lang="en">
<head>
	<meta charset="UTF-8">
	<title>RECIPE API</title>
	</meta>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="templates/theme-style.css" rel="stylesheet" type="text/css" />
        <link href="http://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">
</head>
<body>
<div class="features white vertical-top">Recipe List
EOT;
session_start(); 
if(!(isset($_SESSION['login_user']) OR isset($_COOKIE['login_user']))) echo '<a id="login" href="login.php">Login</a>';
echo '</div>';



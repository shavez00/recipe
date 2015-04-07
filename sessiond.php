<?php
// page2.php

\session_start(); 

echo 'Welcome to page #2<br />';

echo $_COOKIE['login_user'] . " Cookie<br>";

echo $_SESSION['login_user'] . " Session<br>"; // green
echo $_SESSION['animal'];   // cat
echo date('Y m d H:i:s', $_SESSION['time']);

session_unset();
setcookie('login_user', "", time() + 3600);

// You may want to use SID here, like we did in page1.php
echo '<br /><a href="session.php">page 1</a>'; 
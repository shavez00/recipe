<?php

require('core.php');
require('templates/header.php');

$calendar = users::getUserCalendar($_SESSION['login_user']);

switch ($calendar) {
    case "goog":
        header ("Location: google_authenticate.php");
        break;
    case "native":
        header ("Location: calendar/");
        break;
     default:
        echo "No calendar configured";
}

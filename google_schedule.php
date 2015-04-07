<?php
/**
 * called by google_authenticate.php
 * gets user and date
 * calls google calendar
 */
require('core.php');
include('templates/header.php');

//$user = validator::testInput($_GET['user']);
$date = validator::testInput($_GET['date']);
$access_token = $_GET['access-token'];

$email = Users::getUserEmail($_SESSION['login_user']);

$regex = "/((0[1-9]|1[0-2]))-(0[1-9]|[12][0-9]|3[01])$/";
$dateCheck = preg_match($regex, $date);
//echo $dateCheck;

if ($dateCheck == 0) {
    echo '<form action="">
            Please enter a date in MM-DD format.<br>
            <input type="text" name="date">Day to schedule<br>
            <input type="submit">
        </form>';
} else {
   
    $explodeDate = explode("-", $date);
    $endDate = $explodeDate[1] + 1;
    $finalEndDate = $explodeDate[0] . "-" . $endDate;
}
    //echo $finalEndDate . "<br>";

    $url = 'https://www.googleapis.com/calendar/v3/calendars/' . $email . '/events?key={YOUR_API_KEY}';
    
$header = array(
    "Authorization: Bearer ". $access_token, 
    "GET /calendar/v3/calendars/" . $email . "/events", 
    "HOST www.googleapis.com",
    "Content-Type: application/json"
);

$event = array (
    "end" => array(
        "date" => "2015-" . $finalEndDate
    ),
    "start" => array(
        "date" => "2015-" . $date
    ),
    "summary" => $_COOKIE['LABEL'],
    "description" => $_COOKIE['ID'] . ", " . $_COOKIE['URL'], 
    "transparency" => "transparent"
);


/**print "<pre>";
print_r($header);
print_r($event);
print "</pre>";*/

$json_event = json_encode($event);

$ch = curl_init( $url ); 
curl_setopt( $ch, CURLOPT_POST, 1); 
curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_event); 
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 
curl_setopt( $ch, CURLOPT_HEADER, 0); 
curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1); 
$response = curl_exec( $ch );

$temp = explode(",", $response);
$htmlTemp = explode('"', $temp[4]);
$calendarLink = $htmlTemp[3];
/**print "<pre>";
print_r($htmlTemp);
print "</pre>";*/

setcookie("id", "");
setcookie("lable", "");
setcookie("summary", "");
setcookie("url", "");

header ("Location: " . $calendarLink);

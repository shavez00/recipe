<?php
/**
called by google_authenticate.php
gets $code
calls google_schedule.php test
*/

include('templates/header.php');

if(isset($_GET['code'])) { 
    // try to get an access token
    $code = $_GET['code'];
    $url = 'https://accounts.google.com/o/oauth2/token';
    $params = array(
        "code" => $code, 
	"client_id" => "261743167902-lkl9jro3mhjbgo02ms3l8q96pta88pns.apps.googleusercontent.com", 
	"client_secret" => "37gBWT356jG1qJN_-WkBoh6t", 
	"redirect_uri" => "http://www.vezcore.com/recipe/oauth2callback.php", 
	"grant_type" => "authorization_code" 
    );

    $ch = curl_init( $url ); 
    curl_setopt( $ch, CURLOPT_POST, 1); 
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $params); 
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt( $ch, CURLOPT_HEADER, 0); 
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1); 
    $response = curl_exec( $ch );
    //var_dump($response);  //for troubleshooting
}

/**Cookie troubleshooting
echo $_COOKIE['ID'] . "<br>";
echo $_COOKIE['URL'] . "<br>";
echo $_COOKIE['LABEL'];*/

$Response_array = json_decode($response, true);
$access_token = $Response_array['access_token'];
//var_dump($Response_array); //for troubleshooting
//echo $access_token; //for troubleshooting

echo '<div id="main">
        <div class="Center">
            <form action="google_schedule.php" target="_blank">
                Please enter a date in MM-DD format.<br>
                <input type="text" name="date">Day to schedule<br>
                <input type="hidden" name="access-token" value="' . $access_token . '">
                <input type="submit">
            </form>
        </div>
    </div>';
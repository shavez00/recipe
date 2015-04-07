<?php
/**
called by result_list.php
gets $id
calls oauth2callback.php
*/

$url="https://accounts.google.com/o/oauth2/auth";
$params = array( 
		"response_type" => "code", 
		"approval_prompt" => "auto",
		"client_id" => "261743167902-lkl9jro3mhjbgo02ms3l8q96pta88pns.apps.googleusercontent.com", 
		"redirect_uri" => "http://www.vezcore.com/recipe/oauth2callback.php", 
		"scope" => "https://www.googleapis.com/auth/calendar"
);

$id = explode(',', $_GET['id']);

/**print "<pre>";
print_r($id);
print "</pre>";*/


setcookie('ID', $id[0], time() + 3600);
setcookie('LABEL', $id[1], time() + 3600);
setcookie('URL', $id[2], time() + 3600);

$request_to = $url . '?' . http_build_query($params);
header("Location: " . $request_to);
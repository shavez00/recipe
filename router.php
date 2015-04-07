<?php
$google = $_GET['google'];
$cookbook = $_GET['cookbook'];

if (isset($google) OR isset($cookbook)) {
if (isset($google)) {
    $id = explode(',', $_GET['id']);

        setcookie('id', $id[0], time()+3600);
        setcookie('label', $id[1], time()+3600);
        setcookie('url', $id[2], time()+3600);
        setcookie('summary', $id[3], time()+3600);
               
        header("Location: google_authenticate.php");
}

if (isset($cookbook)) {
    echo "add to cookbook";
    echo $_GET['id'];
}
} else {
    echo "ERROR!";
}

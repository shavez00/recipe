<?php
class validator 
{
    public static function testInput ($input) {
		    $input = trim($input);
		    $input = stripslashes($input);
		    $input = htmlspecialchars($input);
		    return $input;
    }
}
?>
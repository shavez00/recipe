<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>RECIPE API</title>
	</meta>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="http://www.vezcore.com/smartphone/css/theme-style.css" rel='stylesheet' type='text/css' />
</head>
<form action="test.php">
<div id="fea" class="features">
  <div class="container">
    <div class="head text-center">
<?php

foreach ($Recipe_array['hits'] as $recipe) {
        //echo '<div id="fea" class="features"><div class="container"><div class="head text-center">';
        echo '<a href=' . $recipe['recipe']['url'] . '><img src=' . $recipe['recipe']['image'] . '>';
        echo '<br>';
        echo '<input type="radio" name="id" value="' . $recipe['recipe']['label'] . '"><a href=' . $recipe['recipe']['url'] . '>' . $recipe['recipe']['label'] . '</a>';
        echo '</br>';
}
?>
      <a href="index.html">home | </a><a href=""> previous | </a><a href=""> next</a></br>
        <button type="submit">Schedule</button>
      </form>
    </div>
  </div>
</div>
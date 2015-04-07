<?php
//called by index
//passed Recipe varible

require_once ('core.php');

$page = @validator::testInput($_GET['page']);

$Recipe = validator::testInput($_GET['Recipe']);

$from = @validator::testInput($_GET['from']);

$results = new Results();

$from = $results->searchResultValidation($page, $from, $Recipe);

$finalRecipeArray = $results->finalRecipeArray();

include('templates/header.php');

/**
 * called by index.html
 * uses $finalRecipeArray from results object
 * calls google_authenticate.php
 */

echo '
<form action="google_authenticate.php">
    <nav id="nav" role="navigation">
        <a href="#nav" title="Show navigation"><img src="images/mobile-menu-icon.png" width="20"></a>    
        <a href="#" title="Hide navigation"><img src="images/mobile-menu-icon.png" width="20"></a>    
        <ul>        
            <li><a href="index.php" class="button">Search</a></li>        
            <li><input value="Schedule" type="submit" id="submit" /></li>
            <li><a href="#" class="button">Save</a></li>
            <li><a href="result_list.php?page=next&Recipe=' . $Recipe . '&from=' . $from . '" class="button">More</a></li>';
if ( $from > 1) 
{ 
    echo '<li><a href="result_list.php?page=previous&Recipe=' . $Recipe . '&from=' . $from . '" class="button">Previous</a></li>';
} 
echo '
        </ul>
    </nav>
    <div id="sidebar">
        <a href="index.php" class="button">Search</a>
        <input type="submit" id="submit" value="Schedule" />
        <a href="#" class="button">Save</a>
        <a href="result_list.php?page=next&Recipe=' . $Recipe . '&from=' . $from . '" class="button">More</a>';
if ( $from > 1) 
{ 
    echo '<a href="result_list.php?page=previous&Recipe=' . $Recipe . '&from=' . $from . '" class="button">Previous</a>';
} 
echo '</div>
    <div id="main">';

for ($i = 0; $i < count($finalRecipeArray['RecipeID']); $i++) 
{
    echo '<div class="Center"><a href=' . $finalRecipeArray['RecipeURL'][$i] . ' target="_blank"><img src=' . $finalRecipeArray['RecipeImageURL'][$i] . ' width="300">';
    echo '<input type="radio" name="id" value="' . $finalRecipeArray['RecipeID'][$i] . ',' .  $finalRecipeArray['RecipeTitle'][$i] . ',' . $finalRecipeArray['RecipeURL'][$i] . '"><a class="a_results"  href=' . $finalRecipeArray['RecipeURL'][$i] . ' target="_blank">' . $finalRecipeArray['RecipeTitle'][$i] . '</a>';
    echo '</div>';
}
echo '</div>
</form>
    </body>
</html>';
exit;

/******************************************************************************
if ($database instanceof recipePuppy) {
    $RecipeIngredients = ["Recipe" => $Recipe, "Ingredients" => $Ingredients];
    
    if ($page == 'next') {
	$Recipe_array = $database->nextRecipe($Recipe, $from);
	$from = $from + 1;
    } elseif ($page == 'previous') {
        $Recipe_array = $database->nextRecipe($Recipe, $from);
        if ($from != 1) {
            $from = $from - 1;
        }
    } else {
	$Recipe_array = $database->getRecipe($RecipeIngredients);
	$from = 2;
    }

echo '
<!DOCTYPE html>
<!---
called by index.html
uses $Recipe_array from edamam object
calls test.php
-->

<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>RECIPE API</title>
	</meta>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="/templates/theme-style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<form action="router.php"><!--google_authenticate.php-->
<div id="fea" class="features">
  <div class="container">
    <div class="head text-center">';

foreach ($Recipe_array['results'] as $recipe) {
        //echo '<div id="fea" class="features"><div class="container"><div class="head text-center">';
        echo '<a href=' . $recipe['href'] . '><img src=' . $recipe['thumbnail'] . '>';
        echo '<br>';
        echo '<input type="radio" name="id" value="' . $recipe['href'] . ',' .  $recipe['title'] . ',' . $recipe['href'] . ',' . $recipe['title'] . '"><a href=' . $recipe['href'] . '>' . $recipe['title'] . '</a>';
	echo '</br>';
}
        if ( $from > 2) { 
            echo '<a href="result_list.php?page=previous&Recipe=' . $Recipe . '&from=' . $from . '"> previous |</a>';
        } 
        echo '<a href="result_list.php?page=next&Recipe=' . $Recipe . '&from=' . $from . '"> next</a></br>
        <input type="checkbox" name="google" value="google">Schdule<br>
        <input type="checkbox" name="cookbook" value="cookbook">Add to my cookbook<br>
        <button type="submit">Submit</button>
      </form>
    </div>
  </div>
</div>
</body>';
}*/
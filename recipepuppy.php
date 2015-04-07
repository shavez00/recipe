<?php
/*  edamam class
* + = public function/property
* - = private function/property
* x = proteced function/proptery
*
* The Edamam class extends the database interface and uses the Edamam API
* https://developer.edamam.com/recipe-docs 
* The class contains zero properties: 
*
* The class contains one methods:
* + getRecipe() - recieves a varible and inserts the varible into the API query string
* then transforms the JSON response into an array and returns it.
*/

class recipePuppy implements databaseInterface {
    function getRecipe($Recipe) {
        $recipePuppy_url = "http://www.recipepuppy.com/api/?q=" . urlencode($Recipe);
        //echo $recipePuppy_url; //print out url sent for troubleshooting
        $Recipe_json = file_get_contents($recipePuppy_url);
        $Recipe_array = json_decode($Recipe_json, true);
 				$Results_array = $this->createRecipeResultsArray($Recipe_array);      
        //print_r($Recipe_json);
        //var_dump($Recipe_array);
        return $Recipe_array;
print "<pre>";
	print_r($Recipe_array);
	print "</pre>";
        //var_dump($Recipe_array['results']); //print out array returned fortroubleshooting
    }

function nextRecipe($Recipe, $page) {
        $recipePuppy_url = "http://www.recipepuppy.com/api/?q=" . urlencode($Recipe) . "&p=" . $page;
        //echo $recipePuppy_url; //print out url sent for troubleshooting
        $Recipe_json = file_get_contents($recipePuppy_url);
        $Recipe_array = json_decode($Recipe_json, true);
        //print_r($Recipe_json);
        //var_dump($Recipe_array);
        return $Recipe_array;
        //var_dump($Recipe_array['results']); //print out array returned fortroubleshooting
    }

private function createRecipeResultsArray($recipeArray) {
        foreach ($recipeArray['results'] as $recipe) {
            $recipepuppyRecipeURL[] = $recipe['href'];
            //$transformedURL = substr($recipe['ImageURL'], 33);
            //$bigovenRecipeImageURL[] = "http://images.bigoven.com/image/upload/t_recipe-256/" . $transformedURL;
            $recipepuppyRecipeImageURL[] = $recipe['thumbnail'];
            $recipepuppyRecipeID[] = $recipe['href'];
            $recipepuppyRecipeTitle[] = $recipe['title'];
        }
	$numOfRecipepuppyResults = count($recipeArray);
	return array("recipepuppyRecipeURL" => $recipepuppyRecipeURL, "recipepuppyRecipeImageURL" => $recipepuppyRecipeImageURL, "recipepuppyRecipeID" => $recipepuppyRecipeID, "recipepuppyRecipeTitle" => $recipepuppyRecipeTitle, "numOfRecipepuppyResults" => $numOfRecipepuppyResults);
    }

}
			

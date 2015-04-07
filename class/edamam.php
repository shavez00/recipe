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
* The class contains three methods:
* + getRecipe($Recipe) - recieves a varible and inserts the varible into the API query string
* then transforms the JSON response into an array and returns it.
* + nextRecipe($Recipe, $from) - recieves a varible and inserts the varible into the API query string
* as well as the $from varible to get the next set of results, then transforms the JSON response
* into an array and returns it.
* - createRecipeResultsArray($recipeArray) - parses the array $recipeArray then normalizes it and
* returns an array
*/

class Edamam implements DatabaseInterface 
{
    public function getRecipe($Recipe) 
    {
        $Edamam_url = "https://api.edamam.com/search?q=" . urlencode($Recipe) . "&app_id=5b5cdfb6&app_key=62bb003f040913e957cf81070e39d44b";
        //$Edamam_url = "https://api.edamam.com/search?q=" . urlencode($Recipe) . "&app_id=5b5cdfb6&app_key=62bb003f040913e957cf81070e39d44b";
        //echo $Edamam_url; //print out url sent for troubleshooting
        $Recipe_json = file_get_contents($Edamam_url);
        $Recipe_array = json_decode($Recipe_json, true);
        //print_r($Recipe_json);
        $Results_array = $this->createRecipeResultsArray($Recipe_array);
       return $Results_array;
       //print "<pre>";
       //print_r($Results_array);
       //print "</pre>";
       //var_dump($Results_array['numOfEdamamResults']); //print out array returned fortroubleshooting
    }

    public function nextRecipe($Recipe, $from) 
    {
        $from = $from * 10;
        $to = $from + 10;
        $Edamam_url = "https://api.edamam.com/search?q=" . urlencode($Recipe) . "&from=" . $from . "&to=" . $to . "&app_id=5b5cdfb6&app_key=62bb003f040913e957cf81070e39d44b";
        //$Edamam_url = "https://api.edamam.com/search?q=" . urlencode($Recipe) . "&app_id=5b5cdfb6&app_key=62bb003f040913e957cf81070e39d44b";
        echo $Edamam_url; //print out url sent for troubleshooting
        $Recipe_json = file_get_contents($Edamam_url);
        $Recipe_array = json_decode($Recipe_json, true);
        //print_r($Recipe_array);
        $Results_array = $this->createRecipeResultsArray($Recipe_array);
        //return $Results_array;
	      //print "<pre>";
	      //print_r($Results_array);
	      //print "</pre>";
        //var_dump($Recipe_array['hits']['0']['recipe']['ingredients']['1']['label']); //print out array returned fortroubleshooting
    }

    private function createRecipeResultsArray(array $recipeArray) 
    {
        foreach ($recipeArray['hits'] as $recipe) 
        {
            $edamamRecipeURL[] = $recipe['recipe']['url'];
            $edamamRecipeImageURL[] = $recipe['recipe']['image'];
            $RecipeID = strstr($recipe['recipe']['uri'], "_");
            $ID = substr($RecipeID, 1);
            $edamamRecipeID[] = $ID;
            $edamamRecipeTitle[] = $recipe['recipe']['label'];
        }
        $numOfEdamamResults = $recipeArray['count'];
        return array("RecipeURL" => $edamamRecipeURL, "RecipeImageURL" => $edamamRecipeImageURL, "RecipeID" => $edamamRecipeID, "RecipeTitle" => $edamamRecipeTitle, "numOfResults" => $numOfEdamamResults );
    }
}
			
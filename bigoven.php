<?php
/*  BigOven class
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

class bigoven implements databaseInterface {
    function getRecipe($Recipe) {
        $bigoven_url = "http://api.bigoven.com/recipes?title_kw=" . urlencode($Recipe) . "&pg=1&rpp=10&api_key=dvx9wqGFe5S4yYFsNVKxgfT3Ki2uE3jb";
        echo $bigoven_url; //print out url sent for troubleshooting
        
        $header = array(
            "Accept: application/json"
        );
        
        $ch = curl_init( $bigoven_url );
        //curl_setopt( $ch, CURLOPT_POST, 1); 
        //curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_event); 
        //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt( $ch, CURLOPT_HEADER, 0); 
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1); 
        $Recipe_json = curl_exec( $ch );
        
        //var_dump($Recipe_json);  //for troubleshooting
        
        $Recipe_array = json_decode($Recipe_json, true);
        $Results_array = $this->createRecipeResultsArray($Recipe_array);
        //var_dump($Recipe_array);
        //return $Results_array;
        print "<pre>";
	print_r($Recipe_array);
	print "</pre>";
        //var_dump($Recipe_array['Results']); //print out array returned fortroubleshooting
    }

function nextRecipe($Recipe, $from) {
        //$to = $from + 10;
        $bigoven_url = "http://api.bigoven.com/recipes?title_kw=" . urlencode($Recipe) . "&pg=" . $from . "&rpp=10&api_key=dvx9wqGFe5S4yYFsNVKxgfT3Ki2uE3jb";
        //echo $bigoven_url; //print out url sent for troubleshooting
        
        $header = array(
            "Accept: application/json"
        );
        
        $ch = curl_init( $bigoven_url );
        //curl_setopt( $ch, CURLOPT_POST, 1); 
        //curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_event); 
        //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt( $ch, CURLOPT_HEADER, 0); 
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1); 
        $Recipe_json = curl_exec( $ch );
        
        //var_dump($Recipe_json);  //for troubleshooting
        
        $Recipe_array = json_decode($Recipe_json, true);
        $Results_array = $this->createRecipeResultsArray($Recipe_array);
        //var_dump($Recipe_array);
        return $Results_array;
        //print "<pre>";
	//print_r($Results_array);
	//print "</pre>";
        //var_dump($Recipe_array['Results']); //print out array returned fortroubleshooting
    }
    
    private function createRecipeResultsArray(array $recipeArray) {
        foreach ($recipeArray['Results'] as $recipe) {
            $bigovenRecipeURL[] = $recipe['WebURL'];
            $transformedURL = substr($recipe['ImageURL'], 33);
            $bigovenRecipeImageURL[] = "http://images.bigoven.com/image/upload/t_recipe-256/" . $transformedURL;
            //$bigovenRecipeImageURL[] = $recipe['ImageURL120'];
            $bigovenRecipeID[] = $recipe['RecipeID'];
            $bigovenRecipeTitle[] = $recipe['Title'];
        }
	return array("bigovenRecipeURL" => $bigovenRecipeURL, "bigovenRecipeImageURL" => $bigovenRecipeImageURL, "bigovenRecipeID" => $bigovenRecipeID, "bigovenRecipeTitle" => $bigovenRecipeTitle);
    }

}
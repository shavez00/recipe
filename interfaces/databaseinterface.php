<?php

/*  database interface
* + = public function/property
* - = private function/property
* x = proteced function/proptery
*
* The database interface creates a standard interface that the client program can depend
* on and call one method, and then the type of database object can be changed, with
* and understanding that you'll still be getting back a useful response  
* 
* The class contains zero properties: 
*
* The class contains three methods:
* + getRecipe($Recipe) - query the database
* + nextRecipe($Recipe, $from) - query the database to get 
* the next set of results
* - createRecipeResultsArray(array $recipeArray) - normalize the result set
*/

interface DatabaseInterface 
{
    public function getRecipe($Recipe);

    public function nextRecipe($Recipe, $from);

    //private function createRecipeResultsArray(array $recipeArray);
}

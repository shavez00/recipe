<?php
/*  Results class
* + = public function/property
* - = private function/property
* x = proteced function/proptery
*
* The Results class takes an array of normalized results from the databases and combines
* then into the _Stack property and returns this combined array
* 
* The class contains six properties: 
* - _Stack - an array of results
* - finalRecipeURL - an array of URLs to results
* - finalRecipeImageURL - an array of URLs to recipe images
* - finalRecipeID - an array of recipe IDs
* - finalRecipeTitle - an array of recipe titles
* - numOfResults - a counter to help with checking if there were any results
*
* The class contains six methods:
* + finalRecipeArray() - parses the _Stack property and returns an array
* - getRecipeArrayStack() - return the _Stack property
* - addRecipeDatabase(array $recipeArray) - gets an array of results and
* adds it to the _Stack property
* + searchResultValidation($page, $from, $Recipe) - recieves three variables
* performs a nullcheck to make sure the database is returning a result.  Based
* upon that check it uses the addDatabase and nextDatabase methods to add
* results to the _Stack.  It also has logic to check the variables and numOfResults
* property and send error codes.
* -  addDatabase(DatabaseInterface $recipeDatabase, $Recipe) - recieves an
* object based on the databaseinterface interface and calls the addRecipeDatabase
* method
* - nextDatabase(DatabaseInterface $recipeDatabasr, $Recipe) -  recieves an
* object based on the databaseinterface interface and calls the nextRecipeDatabase
* method
*/

class Results 
{
    private $_Stack = [];
    
    private $finalRecipeURL = [];
    
    private $finalRecipeImageURL = [];
    
    private $finalRecipeID = [];
    
    private $finalRecipeTitle = [];
    
    private $numOfResults;
    
    public function finalRecipeArray() 
    {
        $recipeArray = $this->_Stack;
        foreach ($recipeArray as $recipeURL) {
            foreach ($recipeURL['RecipeURL'] as $final) {
                $this->finalRecipeURL[] = $final;
            }
        }
        
        foreach ($recipeArray as $recipeURL) {
            foreach ($recipeURL['RecipeImageURL'] as $final) {
                $this->finalRecipeImageURL[] = $final;
            }
        }
        
        foreach ($recipeArray as $recipeURL) {
            foreach ($recipeURL['RecipeID'] as $final) {
                $this->finalRecipeID[] = $final;
            }
        }
        
        foreach ($recipeArray as $recipeURL) {
            foreach ($recipeURL['RecipeTitle'] as $final) {
                $this->finalRecipeTitle[] = $final;
            }
        }
        return array("RecipeURL" => $this->finalRecipeURL, "RecipeImageURL" => $this->finalRecipeImageURL, "RecipeID" => $this->finalRecipeID, "RecipeTitle" => $this->finalRecipeTitle);
    }
    
    private function getRecipeArrayStack() 
	  {
        return $this->_Stack;
    }
    
    private function addRecipeDatabase(array $recipeArray) 
	  {
        if ($recipeArray['numOfResults'] != 0) {
            $this->numOfResults = $this->numOfResults + 1;
        }
        array_push($this->_Stack, $recipeArray);
    }
    
    public function searchResultValidation($page, $from, $Recipe) 
	  {
        if ($page == 'next') {
            $from = $from + 1;
            $bigoven = new bigoven();
            $nullCheck = $bigoven->nextRecipe($Recipe, $from);
            if (empty($nullCheck['RecipeURL'])) {
                $this->addDatabase(new edamam(), $Recipe);
                $from = 1;
                return $from;
            }
            $this->nextDatabase(new bigoven(), $Recipe, $from);
        } elseif ($page == 'previous') {
            if ($from != 1) {
                $from = $from - 1;
                if ($from === 1) {
                    $this->addDatabase(new edamam(), $Recipe);
                }
            } else {
                $this->addDatabase(new edamam(), $Recipe);
            }
            $this->nextDatabase(new bigoven(), $Recipe, $from);
        } else {
            $this->addDatabase(new edamam(), $Recipe);
            $this->addDatabase(new bigoven(), $Recipe);
            $from = 1;
        }
        
        //Validate that there were results from each of the databases
        if ($Recipe != NULL AND $this->numOfResults == 0) {
            header ('Location: index.php?error=2&recipe=' . $Recipe);
            //echo $edamamRecipeArray['numOfEdamamResults'] ; //troubleshooting error condition of when Recupe is passed but no results are returned
            //echo $bigovenRecipeArray['numOfBigovenResults'];  //troubleshooting error condition of when Recupe is passed but no results are returned
            exit;
        } elseif ($this->numOfResults != 0) {
            return $from;
        } else {
            header ('Location: index.php?error=1');
            exit;
        }
        return $from;
    }
    
    private function addDatabase(DatabaseInterface $recipeDatabase, $Recipe) 
	  {
        $this->addRecipeDatabase($recipeDatabase->getRecipe($Recipe));
    }
    
    private function nextDatabase(DatabaseInterface $recipeDatabase, $Recipe, $from)
	  {
        $this->addRecipeDatabase($recipeDatabase->nextRecipe($Recipe, $from));
    }
}
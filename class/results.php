<?php

class results {
    private $_Stack = [];
    
    private $finalRecipeURL = [];
    
    private $finalRecipeImageURL = [];
    
    private $finalRecipeID = [];
    
    private $finalRecipeTitle = [];
    
    private $numOfResults;
    
    public function finalRecipeArray() {
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
    
    private function getRecipeArrayStack() {
        return $this->_Stack;
    }
    
    private function addRecipeDatabase(array $recipeArray) {
        if ($recipeArray['numOfResults'] != 0) {
            $this->numOfResults = $this->numOfResults + 1;
        }
        array_push($this->_Stack, $recipeArray);
    }
    
    public function searchResultValidation($page, $from, $Recipe) {
        if ($page == 'next') {
            $from = $from + 1;
            $bigoven = new bigoven();
            $nullCheck = $bigoven->nextRecipe($Recipe, $from);
            if (empty($nullCheck['RecipeURL'])) {
                $this->addDatabase(new edamam(), $Recipe);
                $from = 1;
                /**echo '<div id="fea" class="features">
                    <div class="Center">No more results, please select a recipe or go to home and search different ingredients.
                    </div>
                    </div>
                    </body>
                    </html>';*/
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
    
    private function addDatabase(databaseInterface $recipeDatabase, $Recipe) {
        $this->addRecipeDatabase($recipeDatabase->getRecipe($Recipe));
    }
    
    private function nextDatabase(databaseInterface $recipeDatabase, $Recipe, $from) {
        $this->addRecipeDatabase($recipeDatabase->nextRecipe($Recipe, $from));
    }
}
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
* The class contains one methods:
* + getRecipe() - 
*/

interface DatabaseInterface 
{
    public function getRecipe($Recipe);
}

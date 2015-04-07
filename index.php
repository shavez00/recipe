<?php
require('templates/header.php');

require('core.php');
			
$error = @validator::testInput($_GET['error']);  //added @ to supress error messages
$recipe = @validator::testInput($_GET['recipe']);  //added @ to supress error messages
			
?>
    <div id="outer">
            <div id="middle">
                <div id="inner">
		<?php
			//echo $error;
			if ($error == 1) {
				echo "Please input an ingredient";
			} elseif($error == 2) {
				echo "Sorry, nothing delicious found that contains '" . $recipe . "' try removing one ingredient or searching for something else yummy";
				}
		?><div id="index">Find something delicious to eat today.  Start by searching below.  Once you've found something yummy, schedule it in your calendar, or add it to your personal cookbook.  Go ahead...find something delicious.</div>
		<form id="tfnewsearch" action="result_list.php">
				<input type="text" class="tftextinput" name="Recipe" placeholder="Find something yummy..." required>
                                <span class="arrow_box">
                                    <input type="submit" class="tfbutton" value ="Search">
                                </span>
		</form>
                <div class="tfclear"></div>
                </div>
            </div>
        </div>
</body>
</html>

<?php 
require_once("core.php"); //include the config
require('templates/header.php');

$registration = NULL;

$top_page = <<<EOT
<body>
    <div class="features white vertical-top"></div>
        <div id="outer">
            <div id="middle">
                <div id="inner">
EOT;

$noRegPage = '<div id="index">Please choose a Username and Password</div>';
			
$bot_page = <<<EOT
        <div id="index">
            <form method="post">
            <ul>
                <li>
                    <label for="usn">Username : </label>
                    <input type="text" id="usn" maxlength="30" required autofocus name="username" placeholder="Username"/>
		</li>
		<li>
                    <label for="passwd">Password : </label>
                    <input type="password" id="passwd" maxlength="30" required name="password" placeholder="Password"/>
		</li>
		<li>
                    <label for="conpasswd">Confirm Password : </label>
                    <input type="password" id="conpasswd" maxlength="30" required name="conpassword" placeholder="Confirm password"/>
		</li>
                <li>
                    <label for="first">First Name : </label>
                    <input type="text" id="first" maxlength="30" required name="first" placeholder="first name"/>
		</li>
                <li>
                    <label for="last">Last Name : </label>
                    <input type="text" id="last" maxlength="30" required name="last" placeholder="last name"/>
		</li>
                <li>
                    <label for="birthdate">Birthday : </label>
                    <input type="date" id="birthdate" maxlength="30" required name="birthdate"/>
		</li>
                <li>
                    <label for="mobile">Mobile : </label>
                    <input type="text" id="mobile" maxlength="30" required name="mobile"/>
		</li>
                <li>
                    <label for="email">Email : </label>
                    <input type="text" id="email" maxlength="30" required name="email" placeholder="email address"/>
		</li>
                <li>
                    <label for="google">Do you want to schedule your meals on Google Calendar?</label><br>
                    <label for="google">Yes : </label>
                    <input type="checkbox" id="google" required name="google"/>
		</li>
		<li class="buttons">
                    <input type="submit" name="register" value="Register" />
                    <input type="button" name="cancel" value="Cancel" onclick="location.href='index.php'" />
                    <input type="hidden" value="<?php echo $registration; ?>" name="registration" />
		</li>
            </ul> 
            </form>
	</div>
    </div>
</div>
        </div>
</body>
</html>
EOT;

//if register button was clicked.
if($_POST) {
    $registration = @validator::testInput($_POST['registration']);

    //var_dump($_POST);
    $usr = new Users; //create new instance of the class Users
    $usr->storeFormValues( $_POST ); //store form values

    //if the entered password is match with the confirm password then register him
    if( $_POST['password'] == $_POST['conpassword'] ) {
        $registration = $usr->register($_POST);
    } else {
        //if not then say that he must enter the same password to the confirm box.
        $registration = 3;
        //echo 'Success';
    }
}

if ($registration === 2) {
	$existingUserPage = '<div id="index">Username exists, please choose a different username</div>';
        echo $top_page . $existingUserPage . $bot_page;
        exit;
} elseif ($registration === 3) {
	$passNoMatch = '<div id="index">Passwords do not match, please re-enter.</div>';
        echo $top_page . $passNoMatch . $bot_page;
} elseif ($registration === 1) {
	header ("Location: index.php");
	//echo 'Success';
}

//echo $registration;

//if ($registration = 1) header ("Location: login.php"); 

 //if user did not click registration button show the registration field.
if( !(isset( $_POST['register'] ) ) ) echo $top_page . $noRegPage . $bot_page; 

//if( $registration != NULL) {echo $page . $page2 . $page3;}
                 
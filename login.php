<?php
require('templates/header.php');
?>
        <div id="outer">
            <div id="middle">
                <div id="inner">
		<?php
                    require('core.php');
                    if( isset( $_SESSION['login_user'] )  OR isset($_COOKIE['login_user']) ) { 
                        header ("Location: index.php");
                    } elseif (!(isset($_POST['login']))) {    
		?>
                <div id="index">Please log in.<?php if ((isset($_GET['error']))) {echo "<br>Incorrect Username/Password";}?></div>
                <div id="index">
		<form method= "post" action="">
		<ul>
                <li>
                    <label for="usn">Username : </label>
                    <input type="text" id="usn" maxlength="30" required autofocus name="username" placeholder="Username"/>
		</li>
 		<li>
                    <label for="passwd">Password : </label>
                    <input type="password" id="passwd" maxlength="30" required name="password" placeholder="Password" />
		</li>
                    <input type="checkbox" name="keepli" value="checked" /> Keep me logged in<br>
                    <input type="submit" name="login" value="Log me in" />
                    <input type="button" name="register" value="Register" onclick="location.href='register.php'" />
		</form>
                </div>
                <div class="tfclear"></div>
                </div>
            </div>
        </div>
</body>
</html>
<?php 
                //else look at the database and see if he entered the correct details
                } else {
                    $usr = new Users;
                    $usr->storeFormValues( $_POST );

                    //if our function userLogin() returns true then the user is valid, display welcome else say it's incorrect.
                    if( $usr->userLogin() ) {
                        header ("Location: index.php"); 
                    } else {
                        header ("Location: login.php?error=1"); 
                    }
                }
?>

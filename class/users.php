<?php
/*  Users class
* + = public function/property
* - = private function/property
* x = proteced function/proptery
*
* The Usera class registers users, logs uses in, and establishes sessions
* and Cookies to maintain the user's logged in status
*
* The class contains nine properties: 
* - username - username
* - password - users password
* - first - users first name
* - last - users last name
* - birthdate - users birthdate
* - mobile - users mobile number
* - keepli - Boolean value to mark that cookie should be set
* to keep use logged in between sessions
* - email - users email
* - salt - value used to salt users password
*
* The class contains five methods:
* + _construct($data) - passed a group of values that is put into an array
* The array values are then used to populate the properties
* + storeFormValues( $params ) - calls the construct method and passes $params
* to it
* + userLogin() - queries the database to seeif user exists, if they do, establishes session
* and passes the $success variable to indicate that the user was found or not
* + register() - queries if user exists already, if not it inserts the user into the database and
* establishes the session
* - sessionEstablish() - creates a session and places a cookie
*/

define( "DB_DSN", "mysql:host=localhost;dbname=recipe" ); //this constant will be use as our connectionstring/dsn

define( "DB_USERNAME", "shavez00" ); //username of the database
define( "DB_PASSWORD", "morgan08" ); //password of the database

class Users 
{
    private $username = null;
    private $password = null;
    private $first = null;
    private $last = null;
    private $birthdate = null;
    private $mobile = null;
    private $keepli = null;
    private $email = null;
    private $salt = "Zo4HYTZ1YyKJAASY0PT6EUg7BBYduiuPaNLuxAwUjhT51ElzHv0Ri7EM6ihgf5w";
    
    public function __construct( $data = array() ) 
    {
        if( isset( $data['username'] ) ) $this->username = stripslashes( strip_tags( $data['username'] ) );
        if( isset( $data['password'] ) ) $this->password = stripslashes( strip_tags( $data['password'] ) );
        if( isset( $data['first'] ) ) $this->first = stripslashes( strip_tags( $data['first'] ) );
        if( isset( $data['last'] ) ) $this->last = stripslashes( strip_tags( $data['last'] ) );
        if( isset( $data['birthdate'] ) ) $this->birthdate = stripslashes( strip_tags( $data['birthdate'] ) );
        if( isset( $data['mobile'] ) ) $this->mobile = stripslashes( strip_tags( $data['mobile'] ) );
        if( isset( $data['email'] ) ) $this->email = stripslashes( strip_tags( $data['email'] ) );
	      if( isset( $data['keepli'] ) ) $this->keepli = stripslashes( strip_tags( $data['keepli'] ) );
    }
    
    public function storeFormValues($params) 
	  {
        //store the parameters
        $this->__construct( $params );
    }
    
    public function userLogin() 
	  {
        //success variable will be used to return if the login was successful or not.
        $success = false;
        try {
            //create our pdo object
            $con = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
            //set how pdo will handle errors
            $con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            //this would be our query.
            $sql = "SELECT * FROM users WHERE username = :username AND password = :password LIMIT 1";
            //prepare the statements
            $stmt = $con->prepare( $sql );
            //give value to named parameter :username
            $stmt->bindValue( "username", $this->username, PDO::PARAM_STR );
            //give value to named parameter :password
            $stmt->bindValue( "password", hash("sha256", $this->password . $this->salt), PDO::PARAM_STR );
            $stmt->execute();
            $valid = $stmt->fetchColumn();
            if( $valid ) {
                $this->sessionEstablish();
                $success = true;
            }
            $con = null;
            return $success;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $success;
        }
    }
    
    public function register() 
	  {
        $valid = $this->userLogin();
        if($valid) {
            //need to fix so that when user userLogin() checked session isn't set
            session_unset();
            //need to adjust tojust session_start();check username
            return 2;
            exit;
        }
        try {
            /**echo "<br>" . $this->username;
            echo "<br>" . $this->first;
            echo "<br>" . $this->last;
            echo "<br>" . $this->birthdate;
            echo "<br>" . $this->email;
            echo "<br>" . $this->mobile;*/
            $con = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
            $con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
         
            $sql = "INSERT INTO users(username, password, first, last, birthdate, mobile, email) VALUES(:username, :password, :first, :last, :birthdate, :mobile, :email)";
            
            $stmt = $con->prepare( $sql );
            $stmt->bindValue( "username", $this->username, PDO::PARAM_STR );
            $stmt->bindValue( "password", hash("sha256", $this->password . $this->salt), PDO::PARAM_STR );
            $stmt->bindValue( "first", $this->first, PDO::PARAM_STR );
            $stmt->bindValue( "last", $this->last, PDO::PARAM_STR );
            $stmt->bindValue( "birthdate", $this->birthdate, PDO::PARAM_STR );
            $stmt->bindValue( "mobile", $this->mobile, PDO::PARAM_INT );
            $stmt->bindValue( "email", $this->email, PDO::PARAM_STR );
            $stmt->execute();
            $this->sessionEstablish();
            return 1;
        } catch( PDOException $e ) {
            return $e->getMessage();
        }
    }

    private function sessionEstablish() 
	  {
        session_start();
        // Store Session Data
        $_SESSION['login_user']= $this->username;
        if ($this->keepli) {
            //setcookie for keeping user logged in between sessions
            setcookie('login_user', $this->username, time() + 3600);
        }
    }

    public static function getUserEmail($username) 
	  {
        //success variable will be used to return if the login was successful or not.
        $success = false;
        try {
            //create our pdo object
            $con = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
            //set how pdo will handle errors
            $con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            //this would be our query.
            $sql = "SELECT * FROM users WHERE username = :username";
            //prepare the statements
            $stmt = $con->prepare( $sql );
            //give value to named parameter :username
            $stmt->bindValue( "username", $username, PDO::PARAM_STR );
            $stmt->execute();
            $email = $stmt->fetchColumn(7);
            $con = null;
            return $email;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $success;
        }
    }
}


<?php

include_once("config/db_config.php"); //include the config

class Users {
    private $username = null;
    private $password = null;
    private $first = null;
    private $last = null;
    private $birthdate = null;
    private $mobile = null;
    private $keepli = null;
    private $email = null;
    private $salt = "Zo4HYTZ1YyKJAASY0PT6EUg7BBYduiuPaNLuxAwUjhT51ElzHv0Ri7EM6ihgf5w";
    
    public function __construct( $data = array() ) {
        if( isset( $data['username'] ) ) $this->username = stripslashes( strip_tags( $data['username'] ) );
        if( isset( $data['password'] ) ) $this->password = stripslashes( strip_tags( $data['password'] ) );
        if( isset( $data['first'] ) ) $this->first = stripslashes( strip_tags( $data['first'] ) );
        if( isset( $data['last'] ) ) $this->last = stripslashes( strip_tags( $data['last'] ) );
        if( isset( $data['birthdate'] ) ) $this->birthdate = stripslashes( strip_tags( $data['birthdate'] ) );
        if( isset( $data['mobile'] ) ) $this->mobile = stripslashes( strip_tags( $data['mobile'] ) );
        if( isset( $data['email'] ) ) $this->email = stripslashes( strip_tags( $data['email'] ) );
	if( isset( $data['keepli'] ) ) $this->keepli = stripslashes( strip_tags( $data['keepli'] ) );
    }
    
    public function storeFormValues( $params ) {
        //store the parameters
        $this->__construct( $params );
    }
    
    public function userLogin() {
        //success variable will be used to return if the login was successful or not.
        $success = false;
        try{
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
        }catch (PDOException $e) {
            echo $e->getMessage();
            return $success;
        }
    }
    
    public function register() {
        $valid = $this->userLogin();
        if($valid) {
            //need to fix so that when user is checked session isn't set
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
        }catch( PDOException $e ) {
            return $e->getMessage();
        }
    }

    private function sessionEstablish() {
        session_start();
        // Store Session Data
        $_SESSION['login_user']= $this->username;
        if ($this->keepli) {
            //setcookie for keeping user logged in between sessions
            setcookie('login_user', $this->username, time() + 3600);
        }
    }
}


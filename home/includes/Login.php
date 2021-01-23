<?php

/**
 * Login.php - The Login class responsible logging on a user.
 *
 * @author richard.siggins
 */
class Login {

    public $time;   //Time user was last active (page loaded)
    private $db;
    public $session;

    public function __construct($db, $session, $functions, $configs, $logger) {
        $this->db = $db;
        $this->session = $session;
        $this->functions = $functions;
        $this->configs = $configs;
        $this->logger = $logger;
        $this->time = time();
    }

    /**
     * login - The user has submitted his username and password through the login form, this 
     * function checks the authenticity of that information in the database and creates the session.
     * Effectively logging in the user if all goes well.
     */
    function login($subuser, $subpass, $subremember) {
        
        /* Username checking */
        $field = "username";  //Use field name for username
        if (!$subuser || strlen($subuser = trim($subuser)) == 0) {
            Form::setError($field, "* Username or Email not entered");
        }

        /* Password checking */
        $field = "password";  //Use field name for password
        if (!$subpass) {
            Form::setError($field, "* Password not entered");
        }

        /* Return if form errors exist */
        if (Form::$num_errors > 0) {
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = Form::getErrorArray();
            return false;
        }

        /* Checks that username/email is in database and password is correct */
        $result = $this->confirmUserPass($subuser, $subpass);

        /* Check error codes */

        if ($result == 1) {
            $field = "username";
            // Username doesn't match
            Form::setError($field, "* Login is invalid. Please try again");
        } else if ($result == 2) {
            $field = "username";
            // Password incorrect
            Form::setError($field, "* Login is invalid. Please try again");

            /* The next section checks the database for failed login attempts and delays 
             * the login screen by that number of attempts in seconds up to a maximum of 
             * 10 seconds. It resets to 0 if the user logs in succesffully.
             */
            $num_of_attemps = $this->addLoginAttempt($subuser);
            if ($num_of_attemps > 10) {
                $num_of_attemps = 10;
            }
            sleep($num_of_attemps);
        } else if ($result == 3) {
            $field = "username";
            Form::setError($field, "* Your account has not been activated yet");
        } else if ($result == 4) {
            $field = "username";
            Form::setError($field, "* Your account has not been activated by admin yet");
        } else if ($result == 5) {
            $field = "username";
            Form::setError($field, "* Your user account has been banned");
        } else if ($result == 6) {
            $field = "username";
            Form::setError($field, "* Your IP address has been banned");
        }

        /* Return if form errors exist */
        if (Form::$num_errors > 0) {
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = Form::getErrorArray();
            return false;
        }

        /* Username and password correct, register session variables */
        $this->userinfo = $this->getUserInfo($subuser, $this->db);
        $this->username = $_SESSION['username'] = $this->userinfo['username'];
        $this->userid = $_SESSION['userid'] = Functions::generateRandID();
        $this->userlevel = $this->userinfo['userlevel'];
        $this->id = $this->userinfo['id'];

        /* Insert userid, lastip into database and update active users table */
        $this->functions->updateUserField($this->username, "lastip", $_SERVER['REMOTE_ADDR']);
        $this->functions->updateUserField($this->username, "userid", $this->userid);
        $this->functions->addLastVisit($this->username);
        $this->functions->addActiveUser($this->username, $this->time);
        $this->resetLoginAttempts($this->username);
        $this->session->removeActiveGuest($_SERVER['REMOTE_ADDR']);
        
        /* Update Log */
        //if log turned on.. do the following...
        $this->logger->logAction($this->id, 'LOGIN');

        /* Remember Me Cookie - Expires on the time set in the control panel */
        if ($subremember) {

            $cookie_expire = $this->configs->getConfig('COOKIE_EXPIRE');
            $cookie_path = $this->configs->getConfig('COOKIE_PATH');

            setcookie("cookname", $this->username, time() + 60 * 60 * 24 * $cookie_expire, $cookie_path);
            setcookie("cookid", $this->userid, time() + 60 * 60 * 24 * $cookie_expire, $cookie_path);
        }

        /* Login completed successfully */
        return true;
    }

    /*
     * confirmUserPass - Checks whether or not the given username is in the database, 
     * if so it checks if the given password is the same password in the database
     * for that user. If the user doesn't exist or if the passwords don't match up, 
     * it returns an error code (1 or 2). On success it returns 0.
     */

    function confirmUserPass($username, $password) {
        /* Verify that user is in database */
        $query = "SELECT password, userlevel, usersalt FROM users WHERE (username = :username OR email = :username)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array(':username' => $username));
        $count = $stmt->rowCount();
        if (!$stmt || $count < 1) {
            return 1; // Indicates username failure
        }

        /* Retrieve password, usersalt and userlevel from result */
        $dbarray = $stmt->fetch();

        $sqlpass = hash($this->configs->getConfig('hash'), $dbarray['usersalt'] . $password);

        /* Validate that password matches and check if userlevel is equal to 1 */
        if (($dbarray['password'] == $sqlpass) && ($dbarray['userlevel'] == 1)) {
            return 3; // Indicates account has not been activated
        }

        /* Validate the password matches and check if userlevel is equal to 2 */
        if (($dbarray['password'] == $sqlpass) && ($dbarray['userlevel'] == 2)) {
            return 4; // Indicates admin has not activated account
        }

        /* Validate the password matches and check to see if banned */
        if (($dbarray['password'] == $sqlpass) && ($this->functions->checkBanned($username))) {
            return 5; // Indicates account is banned
        }

        /* Validate the password matches and check to see if IP address is banned */
        if (($dbarray['password'] == $sqlpass) && ($this->functions->ipDisallowed($_SERVER['REMOTE_ADDR']))) {
            return 6; // Indicates IP address is banned
        }

        /* Validate that password is correct */
        if ($dbarray['password'] == $sqlpass) {
            return 0; // Success! Username and password confirmed
        } else {
            return 2; // Indicates password failure
        }
    }

    /*
     * getUserInfo - Returns the result array from a mysql query asking for all 
     * information stored regarding the given username. If query fails, NULL is returned.
     */

    public function getUserInfo($username) {
        $query = "SELECT * FROM users WHERE (username = :username OR email = :username)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array(':username' => $username));
        $dbarray = $stmt->fetch();
        /* Error occurred, return given name by default */
        $result = count($dbarray);
        if (!$dbarray || $result < 1) {
            return NULL;
        }
        /* Return result array */
        return $dbarray;
    }

    /*
     * The next 3 functions are to do with failed login attempts and adding a timeout
     * period of up to a maximum of 10 seconds in order to help avoid brute force attacks.
     */

    public function addLoginAttempt($username) {
        $num_of_attempts = (($num_of_attempts = $this->getLoginAttempts($username)) + 1);
        $sql = $this->db->query("UPDATE users SET user_login_attempts = '$num_of_attempts' WHERE (username = '$username' OR email = '$username')");
        return $num_of_attempts;
    }

    // Failed login attempts is reset to zero on successful login
    public function resetLoginAttempts($username) {
        $sql = $this->db->query("UPDATE users SET user_login_attempts = '0' WHERE (username = '$username' OR email = '$username')");
    }

    public function getLoginAttempts($username) {
        $stmt = $this->db->query("SELECT user_login_attempts FROM users WHERE (username = '$username' OR email = '$username')");
        return $login_attempts = $stmt->fetchColumn();
    }

}

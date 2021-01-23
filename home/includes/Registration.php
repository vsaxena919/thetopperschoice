<?php

/**
 * Description of Registration
 *
 * @author Richard.Siggins
 */
class Registration {

    private $db;
    private $session;

    public function __construct($db, $session, $configs, $functions, $logger) {
        $this->db = $db;
        $this->session = $session;
        $this->configs = $configs;
        $this->functions = $functions;
        $this->logger = $logger;
    }

    /**
     * register - Gets called when the user has just submitted the registration form. Determines if there were any errors with the 
     * entry fields, if so, it records the errors and returns to the form. If no errors were found, it registers the new user and 
     * returns a success code depending on what type of activation it is. It returns 2 if registration failed.
     */
    function register($subuser, $subfirstname, $sublastname, $subpass, $subconf_pass, $subemail, $subconf_email, $isadmin) {
        $mailer = new Mailer($this->db, $this->configs);
        $token = Functions::generateRandStr(16);
        /* Username error checking */
        $field = "user";  //Use field name for username
        if (!$subuser || strlen($subuser = trim($subuser)) == 0) {
            Form::setError($field, "* Username not entered");
        } else {
            /* check length */
            if (strlen($subuser) < $this->configs->getConfig('min_user_chars')) {
                Form::setError($field, "* Username below " . $this->configs->getConfig('min_user_chars') . " characters");
            } else if (strlen($subuser) > $this->configs->getConfig('max_user_chars')) {
                Form::setError($field, "* Username above " . $this->configs->getConfig('max_user_chars') . " characters");
            }
            /* Check username contains correct characters (Regex is set in configs) */ else if (!$this->functions->usernameRegex($subuser)) {
                Form::setError($field, "* Username does not match requirements");
            }
            /* Check if username is reserved */ else if (strcasecmp($subuser, GUEST_NAME) == 0) {
                Form::setError($field, "* Username reserved word");
            }
            /* Check if username is already in use */ else if ($this->usernameTaken($subuser, $this->db)) {
                Form::setError($field, "* Username already in use");
            }
            /* Check if username is disallowed */ else if ($this->usernameDisallowed($subuser)) {
                Form::setError($field, "* Username Disallowed");
            }
            /* Check if IP address is banned */ else if ($this->functions->ipDisallowed($_SERVER['REMOTE_ADDR'])) {
                Form::setError($field, "* IP Address banned");
            }
        }

        /* Firstname error checking */
        $this->functions->nameCheck($subfirstname, 'firstname', 'First Name', 2, 30);

        /* Lastname error checking */
        $this->functions->nameCheck($sublastname, 'lastname', 'Last Name', 2, 30);

        /* Password error checking */
        $field = "pass";  //Use field name for password
        if (!$subpass) {
            Form::setError($field, "* Password not entered");
        } else {
            /* Check length */
            if (strlen($subpass) < $this->configs->getConfig('min_pass_chars')) {
                Form::setError($field, "* Password too short");
            }
            /* Check if password is too long */ 
            else if (strlen($subpass) > $this->configs->getConfig('max_pass_chars')) {
                Form::setError($field, "* Password too long");
            }
            /* Check if passwords match */ 
            else if ($subpass != $subconf_pass) {
                Form::setError($field, "* Passwords do not match");
            }
        }
        
        $this->functions->emailCheck($subemail, $subconf_email, 'email');

        /* Errors exist, have user correct them */
        if (Form::$num_errors > 0) {
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = Form::getErrorArray();
            return 1;  //Errors with form
        }
        /* No errors, add the new account to the database */ else {
            $usersalt = Functions::generateRandStr(8);
            if ($this->addNewUser($subuser, $subfirstname, $sublastname, $subpass, $subemail, $token, $usersalt)) {

                /* Check Account activation setting and process accordingly. */

                /* E-mail Activation */
                if (($this->configs->getConfig('ACCOUNT_ACTIVATION') == 2) AND ( $isadmin != '1')) {
                    $mailer->sendActivation($subuser, $subemail, $token, $this->configs);
                    $successcode = 3;
                }

                /* Admin Activation */ else if (($this->configs->getConfig('ACCOUNT_ACTIVATION') == 3) AND ( $isadmin != '1')) {
                    $mailer->adminActivation($subuser, $subemail, $this->configs);
                    $mailer->activateByAdmin($subuser, $subemail, $token);
                    $successcode = 4;
                }

                /* No Activation Needed but E-mail going out */ else if (($this->configs->getConfig('EMAIL_WELCOME') && $this->configs->getConfig('ACCOUNT_ACTIVATION') == 1 ) AND ( $isadmin != '1')) {
                    $mailer->sendWelcome($subuser, $subemail, $this->configs);
                    $successcode = 5;

                    /* No Activation Needed and NO E-mail going out */
                } else {
                    $successcode = 0;
                }
                
                /* Update Log */
                //if log turned on.. do the following...
                if($isadmin == '1'){
                    $id = $this->session->id;
                    $this->logger->logAction($id, "REGISTERED : ".$subuser );
                } else {
                    $id = $this->functions->getUserInfoSingular('id', $subuser);
                    $this->logger->logAction($id, "REGISTERED" );
                }
                
                return $successcode;  //New user added succesfully
            } else {
                return 2;  //Registration attempt failed
            }
        }
    }

    /**
     * usernameTaken - Returns true if the username has been taken by another user, false otherwise.
     */
    function usernameTaken($username) {
        $result = $this->db->query("SELECT username FROM users WHERE username = '$username'");
        $count = $result->rowCount();
        return ($count > 0);
    }

    /**
     * usernameDisallowed - Returns true if the username has been disallowed.
     */
    function usernameDisallowed($username) {
        $query = "select * from banlist where :username like concat('%',ban_username,'%') AND ban_username != ''";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        $count = $stmt->rowCount();
        return ($count > 0);
    }

    /*
     * addNewUser - Inserts the given (username, password, email) info into the database. 
     * Appropriate user level is set. Returns true on success, false otherwise.
     */
    function addNewUser($username, $firstname, $lastname, $password, $email, $token, $usersalt) {
        $time = time();
        /* If admin sign up, give admin user level */
        if (strcasecmp($username, ADMIN_NAME) == 0) {
            $ulevel = SUPER_ADMIN_LEVEL;
            /* Which validation is on? */
        } else if ($this->configs->getConfig('ACCOUNT_ACTIVATION') == 1) {
            $ulevel = REGUSER_LEVEL; /* No activation required */
        } else if ($this->configs->getConfig('ACCOUNT_ACTIVATION') == 2) {
            $ulevel = ACT_EMAIL; /* Activation e-mail will be sent */
        } else if ($this->configs->getConfig('ACCOUNT_ACTIVATION') == 3) {
            $ulevel = ADMIN_ACT; /* Admin will activate account */
        } else if (($this->configs->getConfig('ACCOUNT_ACTIVATION') == 4) && !$this->session->isAdmin()) {
            header("Location: " . $this->configs->homePage()); /* Registration Disabled so go back to Home Page */
        } else {
            $ulevel = REGUSER_LEVEL;
        }

        $password = hash($this->configs->getConfig('hash'), $usersalt . $password);
        $userip = $_SERVER['REMOTE_ADDR'];

        $query = "INSERT INTO users SET username = :username, firstname = :firstname, lastname = :lastname, password = :password, usersalt = :usersalt, userid = 0, userlevel = $ulevel, email = :email, timestamp = $time, actkey = :token, ip = '$userip', regdate = $time";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(array(':username' => $username, ':firstname' => $firstname, ':lastname' => $lastname, ':password' => $password, ':usersalt' => $usersalt, ':email' => $email, ':token' => $token));
    }

}

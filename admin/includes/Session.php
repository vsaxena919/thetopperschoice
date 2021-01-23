<?php

/*
 * Session.php
 * Last Updated : August 23rd, 2014
 */

class Session {

    public $username;     //Username given on sign-up
    public $userid;       //Random value generated on current login
    public $userlevel;    //The level to which the user pertains
    public $time;         //Time user was last active (page loaded)
    public $id;           //Users unique ID
    public $logged_in;    //True if user is logged in, false otherwise
    public $userinfo = array();  //The array holding all user info
    public $url;          //The page url current being viewed
    public $referrer;     //Last recorded site page viewed
    public $num_members;  //Number of signed-up users
    public $num_active_users;   //Number of active users viewing site
    public $num_active_guests;  //Number of active guests viewing site
    public $db;           //The Database Connection

    /**
     * Note: referrer should really only be considered the actual page referrer 
     * in process.php, any other time it may be inaccurate.
     */
    /* Class constructor */

    function __construct($db) {

        $this->db = $db;
        $this->functions = new Functions($db);
        $this->logger = new Logger($db);
        $this->configs = new Configs($db);
        $this->time = time();
        $this->startSession();

        /**
         * Only query database to find out number of members when getNumMembers() 
         * is called for the first time, until then, default value set.
         */
        $this->num_members = -1;
        if ($this->configs->getConfig('TRACK_VISITORS')) {
            /* Calculate number of users at site */
            $this->functions->calcNumActiveUsers();
            /* Calculate number of guests at site */
            $this->calcNumActiveGuests();
        }

        // Calculates total users online each time a user visits/refreshes and adds to dbase if a record
        $total = $this->total_users_online = $this->functions->calcNumActiveUsers() + $this->calcNumActiveGuests();
        if ($total > $this->configs->getConfig('record_online_users')) {
            $this->configs->updateConfigs($total, 'record_online_users');
            $this->configs->updateConfigs($this->time, 'record_online_date');
        }
    }

    /**
     * startSession - Performs all the actions necessary to initialise this session object. 
     * Tries to determine if the user has logged in already, and sets the variables 
     * accordingly. Also takes advantage of this page load to update the active visitors tables.
     */
    function startSession() {

        session_start();   //Tell PHP to start the session

        /* Determine if user is logged in */
        $this->logged_in = $this->checkLogin();

        /**
         * Set guest value to users not logged in, and update
         * active guests table accordingly.
         */
        if (!$this->logged_in) {
            $this->username = $_SESSION['username'] = GUEST_NAME;
            $this->userlevel = GUEST_LEVEL;
            $this->addActiveGuest($_SERVER['REMOTE_ADDR'], $this->time);
        }
        /* Update users last active timestamp */ else {
            $this->functions->addActiveUser($this->username, $this->time);
        }

        /* Remove inactive visitors from database */
        $this->removeInactiveUsers();
        $this->removeInactiveGuests();

        /* Set referrer page */
        if (isset($_SESSION['url'])) {
            $this->referrer = $_SESSION['url'];
        } else {
            $this->referrer = "/";
        }

        /* Set current url */
        $this->url = $_SESSION['url'] = htmlentities($_SERVER['PHP_SELF']);
    }

    /**
     * checkLogin - Checks if the user has already previously logged in, and 
     * a session with the user has already been established. Also checks to see 
     * if user has been remembered. If so, the database is queried to make sure 
     * of the user's authenticity. Returns true if the user is logged in.
     */
    function checkLogin() {

        /* Check if user has been remembered */
        if (isset($_COOKIE['cookname']) && isset($_COOKIE['cookid'])) {
            $this->username = $_SESSION['username'] = $_COOKIE['cookname'];
            $this->userid = $_SESSION['userid'] = $_COOKIE['cookid'];
        }

        /* Username and userid have been set and not guest */
        if (isset($_SESSION['username']) && isset($_SESSION['userid']) && $_SESSION['username'] != GUEST_NAME) {
            /* Confirm that username and userid are valid */
            if ($this->confirmUserID($_SESSION['username'], $_SESSION['userid']) != 0) {
                /* Variables are incorrect, user not logged in */
                unset($_SESSION['username']);
                unset($_SESSION['userid']);
                return false;
            }

            /* User is logged in, set class variables */
            $this->userinfo = $this->functions->getUserInfo($_SESSION['username'], $this->db);
            $this->username = $this->userinfo['username'];
            $this->userid = $this->userinfo['userid'];
            $this->id = $this->userinfo['id'];
            $this->userlevel = $this->userinfo['userlevel'];
            return true;
        }
        /* User not logged in */ else {
            return false;
        }
    }

    /*
     * confirmUserID - Checks whether or not the given username is in the database, 
     * if so it checks if the given userid is the same userid in the database
     * for that user. If the user doesn't exist or if the userids don't match up, 
     * it returns an error code (1 or 2). On success it returns 0.
     */

    function confirmUserID($username, $userid) {
        /* Verify that user is in database */
        $query = "SELECT userid FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array(':username' => $username));
        $count = $stmt->rowCount();

        if (!$stmt || $count < 1) {
            return 1; // Indicates username failure
        }

        $dbarray = $stmt->fetch();

        /* Validate that userid is correct */
        if ($userid == $dbarray['userid']) {
            return 0; //Success! Username and userid confirmed
        } else {
            return 2; //Indicates userid invalid
        }
    }

    /* removeInactiveUsers */

    function removeInactiveUsers() {
        if (!$this->configs->getConfig('TRACK_VISITORS')) {
            return;
        }
        $timeout = time() - $this->configs->getConfig('USER_TIMEOUT') * 60;
        $stmt = $this->db->prepare("DELETE FROM active_users WHERE timestamp < $timeout");
        $stmt->execute();
        $this->functions->calcNumActiveUsers();
    }

    /* removeInactiveGuests */

    function removeInactiveGuests() {
        if (!$this->configs->getConfig('TRACK_VISITORS')) {
            return;
        }
        $timeout = time() - $this->configs->getConfig('TRACK_VISITORS') * 60;
        $stmt = $this->db->prepare("DELETE FROM active_guests WHERE timestamp < $timeout");
        $stmt->execute();
        $this->calcNumActiveGuests();
    }

    /*
     * calcNumActiveGuests - Finds out how many active guests are viewing site and 
     * sets class variable accordingly.
     */

    function calcNumActiveGuests() {
        /* Calculate number of GUESTS at site */
        $sql = $this->db->query("SELECT * FROM active_guests");
        return $num_active_guests = $sql->rowCount();
    }   

    /*
     * confirmUserPass - Checks whether or not the given username is in the database, 
     * if so it checks if the given password is the same password in the database
     * for that user. If the user doesn't exist or if the passwords don't match up, 
     * it returns an error code (1 or 2). On success it returns 0.
     */

    function confirmUserPass($username, $password) {
        /* Verify that user is in database */
        $query = "SELECT password, userlevel, usersalt FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array(':username' => $username));
        $count = $stmt->rowCount();
        if (!$stmt || $count < 1) {
            return 1; // Indicates username failure
        }

        /* Retrieve password and userlevel from result, strip slashes */
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
        if (($dbarray['password'] == $sqlpass) && ($dbarray['userlevel'] == 4)) {
            return 5; // Indicates account is banned
        }

        /* Validate that password is correct */
        if ($dbarray['password'] == $sqlpass) {
            return 0; // Success! Username and password confirmed
        } else {
            return 2; // Indicates password failure
        }
    }

    /* removeActiveGuest */

    function removeActiveGuest($ip) {
        if (!$this->configs->getConfig('TRACK_VISITORS')) {
            return;
        }
        $sql = $this->db->prepare("DELETE FROM active_guests WHERE ip = '$ip'");
        $sql->execute();
        $this->calcNumActiveGuests();
    }

    /*
     * checkUserEmailMatch - Checks whether username and email match in forget password form.
     */

    function checkUserEmailMatch($username, $email) {
        $query = "SELECT username FROM users WHERE username = :username AND email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array(':username' => $username, ':email' => $email));
        $number_of_rows = $stmt->rowCount();

        if (!$stmt || $number_of_rows < 1) {
            return 0;
        } else {
            return 1;
        }
    }

    /* getNumMembers - Returns the number of signed-up users of the website. */

    function getNumMembers() {
        if ($this->num_members < 0) {
            $result = $this->db->query("SELECT username FROM users");
            $this->num_members = $result->rowCount();
        }
        return $this->num_members;
    }

    /* getLastUserRegistered - Returns the username of the last member to sign up and the date. */

    function getLastUserRegisteredName() {
        $result = $this->db->query("SELECT username, regdate FROM users ORDER BY regdate DESC LIMIT 0,1");
        $this->lastuser_reg = $result->fetchColumn();
        return $this->lastuser_reg;
    }

    /*
     * getLastUserRegistered - Returns the username of the last member to sign up and the date.
     */

    function getLastUserRegisteredDate() {
        $result = $this->db->query("SELECT username, regdate FROM users ORDER BY regdate DESC LIMIT 0,1");
        $this->lastuser_reg = $result->fetchColumn(1);
        return $this->lastuser_reg;
    }

    /* removeActiveUser */

    function removeActiveUser($username) {
        if (!$this->configs->getConfig('TRACK_VISITORS')) {
            return;
        }
        $sql = $this->db->prepare("DELETE FROM active_users WHERE username = '$username'");
        $sql->execute();
        $this->functions->calcNumActiveUsers();
    }

    /**
     * *********************************************************************************************
     * logout - Gets called when the user wants to be logged out of the website. It deletes any 
     * 'remember me' cookies that were stored on the users computer, and also unsets session variables 
     * and demotes his user level to guest.
     * **********************************************************************************************
     */
    function logout() {       

        // Delete cookies - the time must be in the past, so just negate what you added when creating the cookie.
        if (isset($_COOKIE['cookname']) && isset($_COOKIE['cookid'])) {

            $cookie_expire = $this->configs->getConfig('COOKIE_EXPIRE');
            $cookie_path = $this->configs->getConfig('COOKIE_PATH');

            setcookie("cookname", "", time() - 60 * 60 * 24 * $cookie_expire, $cookie_path);
            setcookie("cookid", "", time() - 60 * 60 * 24 * $cookie_expire, $cookie_path);
        }
        
        /* Update Log */
        //if log turned on.. do the following...
        if(!empty($this->id)) { $this->logger->logAction($this->id, 'LOGOFF'); }

        /* Unset PHP session variables */
        unset($_SESSION['username']);
        unset($_SESSION['userid']);

        /* Reflect fact that user has logged out */
        $this->logged_in = false;

        /**
         * Remove from active users table and add to
         * active guests tables.
         */
        $this->removeActiveUser($this->username);
        $this->addActiveGuest($_SERVER['REMOTE_ADDR'], $this->time);      

        /* Set user level to guest */
        $this->username = GUEST_NAME;
        $this->userlevel = GUEST_LEVEL;

        /* Destroy session */
        session_destroy();
    }

    /**
     * **********************************************************************************************
     * editAccount - Attempts to edit the user's account information including the password, which it 
     * first makes sure is correct if entered, if so and the new password is in the right format, the 
     * change is made. All other fields are changed automatically.
     * **********************************************************************************************
     */
    function editAccount($subcurpass, $subnewpass, $subconfnewpass, $subemail, $form) {

        /* New password entered */
        if ($subnewpass) {

            /* Current Password error checking */
            $field = "curpass";  //Use field name for current password
            
            if (!$subcurpass) {
                Form::setError($field, "* Current Password not entered");
            } else if ($this->confirmUserPass($this->username, $subcurpass) != 0) {
                Form::setError($field, "* Current Password incorrect");
            }

            /* New Password error checking */
            $field = "newpass";  //Use field name for new password
            
            /* check minimum password length (default 8 characters) */
            if (strlen($subnewpass) < $this->configs->getConfig('min_pass_chars')) {
                Form::setError($field, "* New Password too short");
            }
            /* check maximum password length (in an attempt to stop DOS attack for extra long password) */
            else if (strlen($subnewpass) > $this->configs->getConfig('max_pass_chars')) {
                Form::setError($field, "* New Password too long");
            }
            /* Check if passwords match */ 
            else if ($subnewpass != $subconfnewpass) {
                Form::setError($field, "* Passwords do not match");
            }
        }
        /* Current password entered but new one not */ else if ($subcurpass) {
            $field = "newpass";  //Use field name for new password
            Form::setError($field, "* New Password not entered");
        } else if ($subconfnewpass) {
            $field = "conf_newpass";  //Use field name for new password
            Form::setError($field, "* Current Password not entered");
        }
        
        //Checks E-mail Address - $subemail is there twice on purpose
        $this->functions->emailCheck($subemail, $subemail, 'email');

        /* Errors exist, have user correct them */
        if (Form::$num_errors > 0) {
            return false;  //Errors with form
        }

        /* Update password since there were no errors */
        if ($subcurpass && $subnewpass) {
            $usersalt = Functions::generateRandStr(8);
            $subnewpass = hash($this->configs->getConfig('hash'), $usersalt . $subnewpass);
            $this->functions->updateUserField($this->username, "password", $subnewpass);
            $this->functions->updateUserField($this->username, "usersalt", $usersalt);
            
            /* Update Log */
            //if log turned on.. do the following...
            $this->logger->logAction($this->id, 'PWD_CHANGE'); 
        }

        /* Change Email */
        if ($subemail) {
            $change = $this->functions->updateUserField($this->username, "email", $subemail);
            
            if($change){
            /* Update Log */
            //if log turned on.. do the following...
            $this->logger->logAction($this->id, 'EMAIL_CHANGE');
            }
        }

        /* Success! */
        return true;
    }

    /**
     * ****************************************************************************************
     * isAdmin - Returns true if currently logged in user is an administrator, false otherwise.
     * ****************************************************************************************
     */
    function isAdmin() {
        return ($this->userlevel == ADMIN_LEVEL || $this->userlevel == SUPER_ADMIN_LEVEL);
    }
    
    /**
     * ****************************************************************************************************
     * isSuperAdmin - Returns true if currently logged in user is THE Super Administrator, false otherwise.
     * ****************************************************************************************************
     */
    function isSuperAdmin() {
        return ($this->userlevel == SUPER_ADMIN_LEVEL and
                $this->username == ADMIN_NAME);
    }
    
    /**
     * *************************************************************************************************
     * isMemberOfGroup - Returns true if currently logged in user is a member of a certain group.
     * *************************************************************************************************
     */
    function isMemberOfGroup($groupname) {
        $userid = $this->id;
        $group_id = $this->functions->getGroupId($this->db, $groupname);
        $sql = $this->db->query("SELECT user_id FROM users_groups WHERE group_id = '$group_id' AND user_id = '$userid' LIMIT 1");
        return $groupinfo = $sql->fetchColumn();
    }
    
    /**
     * *******************************************************************************************************************
     * isMemberOfGroupOverLevel - Returns true if currently logged in user is a member of a group over the specified level
     * *******************************************************************************************************************
     */
    function isMemberOfGroupOverLevel($level) {
        $userid = $this->id;
        $sql = $this->db->query("SELECT groups.group_level, groups.group_id, users_groups.group_id, users_groups.user_id FROM `groups` INNER JOIN `users_groups` ON groups.group_id=users_groups.group_id WHERE users_groups.user_id = '$userid' AND groups.group_level > '$level'");
        $count = $sql->rowCount();
        return ($count > 0);
    }

    /**
     * *************************************************************************************************
     * isUserlevel - Returns true if currently logged in user is at a certain userlevel, false otherwise.
     * *************************************************************************************************
     */
    function isUserlevel($level) {
        return ($this->userlevel == $level);
    }

    /**
     * *****************************************************************************************************
     * overUserlevel - Returns true if currently logged in user is over a certain userlevel, false otherwise.
     * *****************************************************************************************************
     */
    function overUserlevel($level) {
        if ($this->userlevel > $level) {
            return true;
        } else {
            return false;
        }
    }

    /* 
     * **************************************************
     * addActiveGuest - Adds guest to active guests table
     * ************************************************** 
     */
    function addActiveGuest($ip, $time) {
        if (!$this->configs->getConfig('TRACK_VISITORS')) {
            return;
        }
        $sql = $this->db->prepare("REPLACE INTO active_guests VALUES ('$ip', '$time')");
        $sql->execute();
        $this->calcNumActiveGuests();
    }
    
    /* 
     * ******************************************
     * activateUser - Process to activate Users
     * ****************************************** 
     */
    function activateUser($user, $actkey){
        
        $userlevel = $this->db->query("SELECT userlevel, actkey FROM users WHERE username = '$user' LIMIT 1");
        $row = $userlevel->fetch();

        // Checks if account needs activating (1 is email activation - 2 is admin activation)
        if (($row['userlevel'] == 1) or ( $row['userlevel'] == 2) && ($row['actkey'] == $actkey)) {

            $sql = $this->db->prepare("UPDATE users SET USERLEVEL = '3' WHERE username=:user AND actkey=:actkey");
            $sql->bindParam(":user", $user);
            $sql->bindParam(":actkey", $actkey);
            $sql->execute();

            //Checks if successful
            $count = $sql->rowCount();

            if ($count) {

                //Display Activation Success message and send e-mail confirming.
                $mailer = new Mailer($this->db, $this->configs);
                if ($row['userlevel'] == 2) {
                    echo "<div>Your have activated the account for " . $user . ".</div>";
                } else {
                    echo "<div>Your account is now activated.</div>";
                }
                $sql = $this->db->query("SELECT email FROM users WHERE username = '$user'");
                $email_array = $sql->fetch();
                $email = array_shift($email_array);
                $mailer->adminActivated($user, $email);

                //Generate new activation key so old e-mail cannot change userlevel at a later date
                $token = Functions::generateRandStr(16);
                $sql = $this->db->prepare("UPDATE users SET ACTKEY = '$token' WHERE username=:user");
                $sql->bindParam(":user", $user);
                $sql->execute();
            } else {
                echo "<div>Your account was not activated. Please contact Admin for more assistance.</div>";
            }
        } else if (($row['userlevel'] != 1 ) && ($row['actkey'] === $actkey)) {
            echo "<div>This account does not need activating.</div>";
        } else {
            echo "<div>An error has occured. Please contact Admin for more assistance.</div>";
        }
    }

}

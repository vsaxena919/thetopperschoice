<?php

if (!isset($_POST['installsbt'])) {
    header("Location: index.php");
    exit;
}

    $database_host	= isset($_POST['database_host'])?$_POST['database_host']:"";
    $database_name	= isset($_POST['database_name'])?$_POST['database_name']:"";
    $database_username  = isset($_POST['database_username'])?$_POST['database_username']:"";
    $database_password  = isset($_POST['database_pwd'])?$_POST['database_pwd']:"";
    
    $webroot            = isset($_POST['webroot'])?$_POST['webroot']:"";
    $home_page		= isset($_POST['home_page'])?$_POST['home_page']:"";
    $login_page         = isset($_POST['login_page'])?$_POST['login_page']:"";
    
    $admin_username     = isset($_POST['admin_username'])?$_POST['admin_username']:"";
    $admin_email        = isset($_POST['admin_email'])?$_POST['admin_email']:"";
    $admin_pwd          = isset($_POST['admin_pwd'])?$_POST['admin_pwd']:"";
    
    try {
        $dbh = new PDO('mysql:host=' . $database_host . ';dbname=' . $database_name . ';charset=utf8', $database_username, $database_password);
        // set the PDO error mode to exception
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection = 1;
    }
        catch(PDOException $e)
    {
        $connection = 0;
    }    
    
    // connection was successful
    if($connection == '1') {
    
    $config_file = file_get_contents('../includes/constants.php');
    $config_file = str_replace("_DB_HOST_", $database_host, $config_file);
    $config_file = str_replace("_DB_NAME_", $database_name, $config_file);
    $config_file = str_replace("_DB_USER_", $database_username, $config_file);
    $config_file = str_replace("_DB_PASSWORD_", $database_password, $config_file);
    $config_file = str_replace("_ADMIN_NAME_", $admin_username, $config_file);
    
    $f = @fopen("../includes/constants.php", "w+");
    
    if (@fwrite($f, $config_file) > 0){
        $sql = file_get_contents('db_dump.sql');
        try {
            $dbh->exec($sql);
        }
            catch (PDOException $e)
        {
            //Cannot process sql file
            header("Location: summary.php?error=3");
        }
        
        $stmt = $dbh->prepare("UPDATE `configuration` SET `config_value` = :configvalue WHERE `config_name` = :configname");
        $stmt->execute(array(':configvalue'=>$webroot, ':configname'=>'WEB_ROOT'));
        $stmt->execute(array(':configvalue'=>$home_page, ':configname'=>'home_page'));
        $stmt->execute(array(':configvalue'=>$login_page, ':configname'=>'login_page'));
        $stmt->execute(array(':configvalue'=>$admin_email, ':configname'=>'EMAIL_FROM_ADDR'));
        
        function generateRandStr($length) {
            $randstr = "";
            for ($i = 0; $i < $length; $i++) {
                $randnum = mt_rand(0, 61);
                if ($randnum < 10) {
                    $randstr .= chr($randnum + 48);
                } else if ($randnum < 36) {
                    $randstr .= chr($randnum + 55);
                } else {
                    $randstr .= chr($randnum + 61);
                }
            }
            return $randstr;
        }

        $usersalt = generateRandStr(8);
        $token = generateRandStr(16);
        $time = time();
        $password = hash('sha256', $usersalt . $admin_pwd);
        $userip = $_SERVER['REMOTE_ADDR'];
        
        $query = "INSERT INTO users SET username = :username, firstname = 'Super', lastname = 'Admin', password = :password, usersalt = :usersalt, userid = 0, userlevel = '10', email = :email, timestamp = $time, actkey = :token, ip = '$userip', regdate = $time";
        $stmt = $dbh->prepare($query);
        $createadmin = $stmt->execute(array(':username' => $admin_username, ':password' => $password, ':usersalt' => $usersalt, ':email' => $admin_email, ':token' => $token));
        
    } else {
        //Cannot edit constants.php file
        header("Location: summary.php?error=2");
    }
    @fclose($f);
    header("Location: summary.php?success=1&username=$admin_username");
    } else {
    //Can't connect to database
    header("Location: summary.php?error=1");
    }
?>

<?php

/*
 * Database.php - The all important database class that connects to the database using PDO
 * Last Updated: 15th November, 2014
 */

class Database extends PDO {

    public function __construct() {

        try {
            parent::__construct(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $custom_errormsg = 'Error connecting to database - check your database connection properties in constants.php file';
            echo $custom_errormsg . " <br>\n<br>\n ". $e->getMessage();
            echo "<br>\nPHP Version : ".phpversion()."<br>\n";
        }
    }

}

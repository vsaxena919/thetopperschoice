<?php

/* 
 * The first page which is requried in any page that needs to interact with the
 * database in any way. So, any page that requires login / logout / protected
 * page - basically any user 'login script' functions should include this page at
 * the top before any other code. eg, include('controller.php');
 */

// Error Handling
ini_set("display_errors", 1);
ini_set('log_errors', 1);
ini_set("error_reporting", E_ALL);
 	
// Load constants (database credentials etc.)
require 'constants.php';

// The auto-loader which loads classes automatically
require 'autoload.php';

// Create an instance of the Database Class and assign the object to $db
$db = new Database();

// Create / Include the Session, Configs and Functions Objects
$session = new Session($db);
$configs = new Configs($db);
$functions = new Functions($db);
$logger = new Logger($db);
$adminfunctions = new Adminfunctions($db, $functions, $configs, $logger);
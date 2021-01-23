<?php

/**
 * The SPL __autoload() method is one of the Magic Methods supplied in PHP. It is used to autoload
 * classes so that you do not need to 'include' them in your scripts.
 */
function autoload($class) {

    if (file_exists(dirname(__FILE__) . "/" . $class . ".php")) {
        require (dirname(__FILE__) . "/" . $class . ".php");
    } else {
        exit('The file ' . $class . '.php is missing in the includes folder.');
    }
}

// spl_autoload_register
spl_autoload_register("autoload");

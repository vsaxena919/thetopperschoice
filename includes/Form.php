<?php

/**
 * Form.php
 *
 * The Form class simplifies the task of keeping track of errors in 
 * user submitted forms and the form field values that were entered correctly.
 *
 * Last Updated: August 26, 2014
 */
class Form {

    public static $values = array();    //Holds submitted form field values
    public static $errors = array();    //Holds submitted form error messages
    public static $num_errors;          //The number of errors in submitted form

    /* Class constructor */

    function __construct() {

        /**
         * Get form value and error arrays, used when there
         * is an error with a user-submitted form.
         */
        if (isset($_SESSION['value_array']) && isset($_SESSION['error_array'])) {
            self::$values = $_SESSION['value_array'];
            self::$errors = $_SESSION['error_array'];
            self::$num_errors = count(self::$errors);
            unset($_SESSION['value_array']);
            unset($_SESSION['error_array']);
        } else {
            $num_errors = 0;
        }
    }

    /**
     * setValue - Records the value typed into the given form field by the user.
     */
    public static function setValue($field, $value) {
        self::$values[$field] = $value;
    }

    /**
     * setError - Records new form error given the form field name and the error 
     * message attached to it.
     */
    public static function setError($field, $errmsg) {
        self::$errors[$field] = $errmsg;
        return self::$num_errors = count(self::$errors);
    }

    /**
     * value - Returns the value attached to the given
     * field, if none exists, the empty string is returned.
     */
    public static function value($field) {
        if (array_key_exists($field, self::$values)) {
            return htmlspecialchars(stripslashes(self::$values[$field]));
        } else {
            return "";
        }
    }

    /**
     * error - Returns the error message attached to the
     * given field, if none exists, the empty string is returned.
     */
    public static function error($field) {
        if (array_key_exists($field, self::$errors)) {
            return self::$errors[$field];
        } else {
            return "";
        }
    }

    /* getErrorArray - Returns the array of error messages */

    public static function getErrorArray() {
        return self::$errors;
    }

}

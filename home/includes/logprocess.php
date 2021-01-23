<?php

include_once 'controller.php';

/**
 * logprocess.php
 * 
 * The Process page simplifies the task of processing user submitted forms, redirecting the 
 * user to the correct pages if errors are found, or if form is successful, either way. Also handles the logout procedure.
 *
 * Last Updated : December 9th 2016
 */
if (isset($_POST['form_submission'])) {

    $form_submission = $_POST['form_submission'];
    switch ($form_submission) {

        case "delete_logs" :
            deleteLogs($logger, $session);
            break;
        default :
            if ($session->logged_in) {
                logout($session, $configs);
            } else {
                header("Location: " . $configs->homePage());
            }
    }
} else {
    logout($session, $configs);
}

/**
 * *************************************************************************
 * adminLogin - Admin process for logging in the admin to the control 
 * *************************************************************************
 */
function deleteLogs($logger, $session) {
    
    $logger->purgeLogs();
    $logger->logAction($session->id, "DELETED LOGS");
    header("Location: " . $session->referrer);
    
}

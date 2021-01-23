<?php

/**
 * Mailer.php
 *
 * The Mailer class simplifies the task of sending emails to users. 
 * Note: this email system will not work if your server is not setup to send mail.
 * 
 * Updated: August 23rd, 2014
 */
class Mailer {

    private $db;
    private $configs;

    public function __construct($db, $configs) {
        $this->db = $db;
        $this->configs = $configs;
    }

    /*
     * sendActivation - Sends an activation e-mail to the newly registered user with a link to activate the account.
     */
    function sendActivation($user, $email, $token) {
        $from = "From: " . $this->configs->getConfig('EMAIL_FROM_NAME') . " <" . $this->configs->getConfig('EMAIL_FROM_ADDR') . ">";
        $subject = $this->configs->getConfig('SITE_NAME') . " - Activate Your Account!";
        $body = $user . ",\n\n"
                . "Welcome! You've just registered at " . $this->configs->getConfig('SITE_NAME') . " "
                . "with the following username:\n\n"
                . "Username: " . $user . "\n\n"
                . "Please visit the following link in order to activate your account: "
                . $this->configs->loginPage()."?mode=activate&user=" . urlencode($user) . "&activatecode=" . $token . "#activate \n\n"
                . $this->configs->getConfig('SITE_NAME');

        return mail($email, $subject, $body, $from);
    }

    /**
     * adminActivation - Sends an activation e-mail to the newly registered user explaining that admin will activate the account.
     */
    function adminActivation($user, $email) {
        $from = "From: " . $this->configs->getConfig('EMAIL_FROM_NAME') . " <" . $this->configs->getConfig('EMAIL_FROM_ADDR') . ">";
        $subject = $this->configs->getConfig('SITE_NAME') . " - Welcome!";
        $body = $user . ",\n\n"
                . "Welcome! You've just registered at " . $this->configs->getConfig('SITE_NAME') . " "
                . "with the following username:\n\n"
                . "Username: " . $user . "\n\n"
                . "Your account is currently inactive and will need to be approved by an administrator. "
                . "Another e-mail will be sent when this has occured.\n\n"
                . "Thank you for registering.\n\n"
                . $this->configs->getConfig('SITE_NAME');

        return mail($email, $subject, $body, $from);
    }

    /**
     * activateByAdmin - Sends an activation e-mail to the admin to allow him or her to activate the account. 
     * E-mail will appear to come FROM the user using the e-mail address he or she registered with.
     */
    function activateByAdmin($user, $email, $token) {
        $from = "From: " . $user . " <" . $email . ">";
        $subject = $this->configs->getConfig('SITE_NAME') . " - User Account Activation!";
        $body = "Hello Admin,\n\n"
                . $user . " has just registered at " . $this->configs->getConfig('SITE_NAME')
                . " with the following details:\n\n"
                . "Username: " . $user . "\n"
                . "E-mail: " . $email . "\n\n"
                . "You should check this account and if neccessary, activate it. \n\n"
                . "Use this link to activate the account.\n\n"
                . $this->configs->loginPage()."?mode=activate&user=" . urlencode($user) . "&activatecode=" . $token . "#activate \n\n"
                . "Thanks.\n\n"
                . $this->configs->getConfig('SITE_NAME');

        $adminemail = $this->configs->getConfig('EMAIL_FROM_ADDR');
        return mail($adminemail, $subject, $body, $from);
    }

    /**
     * adminActivated - Sends an e-mail to the user once admin has activated the account.
     */
    function adminActivated($user, $email) {
        $from = "From: " . $this->configs->getConfig('EMAIL_FROM_NAME') . " <" . $this->configs->getConfig('EMAIL_FROM_ADDR') . ">";
        $subject = $this->configs->getConfig('SITE_NAME') . " - Account Activated!";
        $body = $user . ",\n\n"
                . "Welcome! You've just registered at " . $this->configs->getConfig('SITE_NAME') . " "
                . "with the following username:\n\n"
                . "Username: " . $user . "\n\n"
                . "Your account has now been activated. "
                . "Please click here to login - "
                . $this->configs->loginPage() . "\n\nThank you for registering.\n\n"
                . $this->configs->getConfig('SITE_NAME');

        return mail($email, $subject, $body, $from);
    }

    /**
     * sendWelcome - Sends an activation e-mail to the newly registered user with a link to activate the account.
     */
    function sendWelcome($user, $email) {
        $from = "From: " . $this->configs->getConfig('EMAIL_FROM_NAME') . " <" . $this->configs->getConfig('EMAIL_FROM_ADDR') . ">";
        $subject = $this->configs->getConfig('SITE_NAME') . " - Welcome!";
        $body = $user . ",\n\n"
                . "Welcome! You've just registered at " . $this->configs->getConfig('SITE_NAME') . " "
                . "with the following information:\n\n"
                . "Username: " . $user . "\n\n"
                . "Please keep this e-mail for your records. Your password is stored safely in "
                . "our database. In the event that it is forgotten, please visit the site and click "
                . "the Forgot Password link. "
                . "Thank you for registering.\n\n"
                . $this->configs->getConfig('SITE_NAME');

        return mail($email, $subject, $body, $from);
    }

    /**
     * sendNewPass - Sends the newly generated password to the user's email address that was specified at sign-up.
     */
    function sendNewPass($user, $email, $pass) {
        $from = "From: " . $this->configs->getConfig('EMAIL_FROM_NAME') . " <" . $this->configs->getConfig('EMAIL_FROM_ADDR') . ">";
        $subject = $this->configs->getConfig('SITE_NAME') . " - Your New Password!";
        $body = $user . ",\n\n"
                . "We've generated a new password for you at your "
                . "request, you can use this new password with your "
                . "username to log in to " . $this->configs->getConfig('SITE_NAME') . "\n\n"
                . "Username: " . $user . "\n"
                . "New Password: " . $pass . "\n\n"
                . "It is recommended that you change your password "
                . "to something that is easier to remember, which "
                . "can be done by going to the My Account page "
                . "after signing in.\n\n"
                . $this->configs->getConfig('SITE_NAME');

        return mail($email, $subject, $body, $from);
    }

}

<?php

/**
 * Logger - Class used to handle logging.
 */
class Logger { 

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /*
     * logAction - Use this funtion to log activity
     */
    public function logAction($userid, $logoperation) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
        $timestamp = time();
        $query = "INSERT INTO log_table (userid, log_operation, timestamp, ip) VALUES (:userid, :logop, $timestamp, '$ipaddress')";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array(':logop' => $logoperation, ':userid' => $userid));  
    }
    
    /*
     * purgeLogs - Delete Logs
     */
    public function purgeLogs() {
        $query = "truncate log_table";
        $stmt = $this->db->prepare($query);
        $stmt->execute();      
    }
    
    /*
     * purgeLogsOfUser - Delete Logs of individual user
     */
    public function purgeLogsOfUser($userid) {
        $query = $this->db->prepare("DELETE FROM log_table WHERE userid = '$userid'");
        $query->execute();   
    }
    
}

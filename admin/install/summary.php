<!DOCTYPE html>
<html>
    <head>
        <title>Xavier - PHP Login Script & User Registration </title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/ico" href="favicon.ico">

        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <link href="../fonts/Open Iconic/css/open-iconic-bootstrap.min.css" rel="stylesheet">

        <link href="css/styles.css" rel="stylesheet">
        
    </head>

    <body>
        
        <!-- Pen Title-->
        <div class="pen-title">
            <h1><i class="oi oi-x"></i>avier</h1><span>PHP Login Script by <a href='http://www.angry-frog.com'>Angry Frog</a></span>
        </div>
        
        <!-- Form Module-->
        <div class="module form-module">
            <!-- Login -->
            <div class="form" id="form-login">
                <?php 
                if(isset($_GET['error'])) {
                        if($_GET['error'] == 1 ) { echo "Could not connect to the database. Please check the connection details and try again."; }
                        if($_GET['error'] == 2 ) { echo "Cannot edit constants.php file. Please check the location, permissions or chmod permissions on your webserver."; }
                        if($_GET['error'] == 3 ) { echo "Cannot process or access the db_dump.sql file."; }
                } else if (isset($_GET['success'])) {
                        echo "Congratulations, you've successfully set up your database and created your admin account with the username <strong>". $_GET['username'] ."</strong>.<br><br> Click <a href='../index.php'>here</a> to log in.";
                }
                ?>
            </div>
        </div>
        
        <div class="text-muted text-center" id="login-footer">
            <small><span id="year-copy">2017</span> © <a href="http://www.angry-frog.com" target="_blank">Xavier PHP Login Script</a></small>
        </div>

        <!-- JavaScript Resources -->
        <script src="../js/jquery-2.1.3.min.js"></script>
        <script src="../js/jquery-ui.js"></script>
        <script src="../js/bootstrap.min.js"></script>

    </body>
</html>

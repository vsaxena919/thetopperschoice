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
        <link href="css/jquery.steps.css" rel="stylesheet">
        
    </head>
    
    <?php
    $constants_file = '../includes/constants.php';
    $errors = 0;
    $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $url .= $_SERVER['SERVER_NAME'].= $_SERVER['REQUEST_URI'];
    ?>

    <body>
        
        <!-- Pen Title-->
        <div class="pen-title">
            <h1><i class="oi oi-x"></i>avier</h1><span>PHP Login Script by <a href='http://www.angry-frog.com'>Angry Frog</a></span>
        </div>
        
        <!-- Form Module-->
        <div class="module form-module">
            <!-- Login -->
            <div class="form" id="form-login">
                
                <form class="form-horizontal" action="process.php" method="POST" id="form">
               
                <h1>Requirements</h1>
                <fieldset>
                    <h2>Server Requirements</h2>
                    <div class="row">
                        <div class="col-lg-12">
                            <ul style="padding-left: 10px;">
                                <li>PHP version: 
                                    <?php
                                    if (floatval(phpversion()) < 5.1) {
                                        echo "<span class = 'failure'>" . floatval(phpversion()) . " - The script will not work unless you update your PHP version.</span>";
                                        $errors = 1;
                                    } else {
                                        echo "<span class = 'success'>" . floatval(phpversion()) . " - Your version of PHP will support this script.</span>";
                                    }
                                    ?>
                                </li>
                                <li>PDO Enabled:
                                    <?php
                                    if (class_exists('PDO')) {
                                        echo '<span class="success">Yes</span>';
                                    } else {
                                        echo '<span class="failure">No - Check the instructions or forum on how to enable this.</span>';
                                        $errors++;
                                    }
                                    ?>
                                </li>
                                <li>Config File Writable:
                                    <?php
                                    if (is_writable($constants_file)) {
                                        echo '<span class = "success">The file is writable</span>';
                                    } else {
                                        echo '<span class="failure">The file is not writable!</span>';
                                        $errors++;
                                    }
                                    ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </fieldset>
 
                <h1>Paths</h1>
                <fieldset>                     
                    <h2>Folder Paths</h2>
                    <div class="row">
                        <div class="col-lg-12">
                            <p>We've taken a guess at the settings below. They will be populated in your database but can be changed at any time from the Admin Panel. They are important for redirection after logging in and out.</p>               
                            <div class="form-group">
                                <label for="webroot" class="col-sm-3 control-label">Install Path <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input class="form-control required" name="webroot" id="webroot" placeholder="Required Field.." value="<?php echo dirname($url)."/"; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="home_page" class="col-sm-3 control-label">Home Page <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon" id="webroot_static"></span>
                                        <input class="form-control required" name="home_page" id="home_page" placeholder="Required Field.." value="index.php">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="login_page" class="col-sm-3 control-label">Login Page <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input class="form-control required" name="login_page" id="login_page" value="../example/index.php">
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>                     
                    
                <h1>Database</h1>
                <fieldset>
                    <h2>Database Details</h2>
                    <div class="row">
                        <div class="col-lg-12">
                            <p>Complete the form below to setup your database tables and to populate the database settings to the script. You will need to create the database first - ask your hosting company about this if you are not sure.</p>
                            <div class="form-group">
                                <label for="database_host" class="col-sm-4 control-label">Database Host <span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input class="form-control required" name="database_host" id="database_host" placeholder="Usually but not always localhost" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="database_name" class="col-sm-4 control-label">Database Name <span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input class="form-control required" name="database_name" id="database_name" placeholder="Required Field.." value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="database_username" class="col-sm-4 control-label">Database Username <span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input class="form-control required" name="database_username" id="database_username" placeholder="Required Field.." value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="database_pwd" class="col-sm-4 control-label">Database Password <span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input class="form-control required" type="password" name="database_pwd" id="database_pwd" placeholder="Required Field.." value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                           
                <h1>Admin</h1>
                <fieldset> 
                    <h2>Admin Details</h2>
                    <div class="row">
                        <div class="col-lg-12">
                            <p>Complete the form below to setup your database tables and to populate the database settings to the script. You will need to create the database first - ask your hosting company about this if you are not sure.</p>
                            <div class="form-group">
                                <label for="admin_username" class="col-sm-4 control-label">Admin Username <span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input class="form-control required" name="admin_username" id="admin_username" value="Admin">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="admin_email" class="col-sm-4 control-label">Admin E-mail Address <span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input class="form-control required" name="admin_email" id="admin_email" placeholder="Required Field..">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="admin_pwd" class="col-sm-4 control-label" id="password">Password <span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input class="form-control required" type="password" name="admin_pwd" id="admin_pwd" placeholder="Required Field..">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="admin_pwd_conf" class="col-sm-4 control-label">Password (Confirm) <span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input class="form-control required" type="password" name="admin_pwd_conf" id="admin_pwd_conf" placeholder="Required Field..">
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                    
                <h1>Summary</h1>
                <fieldset>
                    <h2>Summary</h2>
                    <div class="row">
                        <div class="col-lg-12">
                            <div>Admin Site Root : <span id="webroot_sum"></span><span id="home_page_sum"></span></div>
                            <div>Database Host : <span id="database_host_sum"></span></div>
                            <div>Database Name : <span id="database_name_sum"></span></div>
                            <div>Database Username : <span id="database_username_sum"></span></div>
                            <div>Admin Username : <span id="admin_username_sum"></span></div>
                            <div>Admin E-mail : <span id="admin_email_sum"></span></div>
                        </div>
                    </div>
                    <h2 style="margin-top:12px;">Click Finish to set up the database.</h2>
                </fieldset>         
                <input type="hidden" name="installsbt" value="summary" />  
                </form>
            </div>
        </div>
        
        <div class="text-muted text-center" id="login-footer">
            <small><span id="year-copy">2017</span> © <a href="http://www.angry-frog.com" target="_blank">Xavier PHP Login Script</a></small>
        </div>

        <!-- JavaScript Resources -->
        <script src="../js/jquery-2.1.3.min.js"></script>
        <script src="../js/jquery-ui.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        
        <!-- Page Plugins -->
        <script src="plugins/jquery-validate/jquery.validate.min.js"></script>
        <script src="plugins/jquery-steps/jquery.steps.min.js"></script>
        
        <script>
        $(document).ready(function(){
            $("#wizard").steps();
            $("#form").steps({
                bodyTag: "fieldset",
                onStepChanging: function (event, currentIndex, newIndex)
                {
                    // Always allow going backward even if the current step contains invalid fields!
                    if (currentIndex > newIndex)
                    {
                        return true;
                    }

                    var form = $(this);

                    // Clean up if user went backward before
                    if (currentIndex < newIndex)
                    {
                        // To remove error styles
                        $(".body:eq(" + newIndex + ") label.error", form).remove();
                        $(".body:eq(" + newIndex + ") .error", form).removeClass("error");
                    }

                    // Disable validation on fields that are disabled or hidden.
                    form.validate().settings.ignore = ":disabled,:hidden";

                    // Start validation; Prevent going forward if false
                    return form.valid();
                },
                onFinishing: function (event, currentIndex)
                {
                    var form = $(this);

                    // Disable validation on fields that are disabled.
                    // At this point it's recommended to do an overall check (mean ignoring only disabled fields)
                    form.validate().settings.ignore = ":disabled";

                    // Start validation; Prevent form submission if false
                    return form.valid();
                },
                onFinished: function (event, currentIndex)
                {
                    var form = $(this);

                    // Submit form input
                    form.submit();
                }
            }).validate({
                        errorPlacement: function (error, element)
                        {
                            element.before(error);
                        },
                        rules: {
                            admin_username: {
                                minlength: 5
                            },
                            admin_email: {
                                email: true
                            },
                            admin_pwd: {
                                minlength: 4
                            },
                            admin_pwd_conf: {
                                equalTo: "#admin_pwd"
                            }
                        },
                        messages: {
                            admin_pwd_conf: {
                                equalTo: 'Please enter the same password as above'
                            }
                        }
                    });
        });
        </script>
    
        <script>
        $(function(){ 
            $("#webroot").keyup(function() {
            var value = $(this).val();
            $("#webroot_static").text(value);
            $("#webroot_sum").text(value);
            }).keyup();
            $("#home_page").keyup(function() {
            var value = $(this).val();
            $("#home_page_sum").text(value);
            }).keyup();
            $("#database_host").keyup(function() {
            var value = $(this).val();
            $("#database_host_sum").text(value);
            }).keyup();
            $("#database_name").keyup(function() {
            var value = $(this).val();
            $("#database_name_sum").text(value);
            }).keyup();
            $("#database_username").keyup(function() {
            var value = $(this).val();
            $("#database_username_sum").text(value);
            }).keyup();
            $("#admin_username").keyup(function() {
            var value = $(this).val();
            $("#admin_username_sum").text(value);
            }).keyup();
            $("#admin_email").keyup(function() {
            var value = $(this).val();
            $("#admin_email_sum").text(value);
            }).keyup();
        });
        </script>

    </body>
</html>

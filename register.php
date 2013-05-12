<?php
session_start();
require_once 'res/db.php';
require_once 'res/query.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="js/limitInput.js"></script>
        <title>PHP Document Management System</title>
        <link rel="stylesheet" href="css/style.css" type="text/css" />
    </head>
    <body>
        <div id="main">
            <?php
            if (!empty($_POST['username']) && !empty($_POST['password'])) {
                $username = mysql_real_escape_string($_POST['username']);
                $password = md5(mysql_real_escape_string($_POST['password']));
                $email = mysql_real_escape_string($_POST['email']);

                // check for duplicate name, should be replaced with ajax
                $checkusername = check_user_name_only($username);

                if (mysql_num_rows($checkusername) == 1) {
                    echo "<h1>Error</h1>";
                    echo "<p>Sorry, that username is taken. Please <a href='register.php'>go back and try again</a>.</p>";
                } else {


                    $path = "file/" . $username . "/";
                    $create_user_dir = mkdir($path);

                    if ($create_user_dir) {
                        // create user
                        $registerquery = register_user($username, $_POST['password'], $email);
                        $register_user_role = false;
                    }

                    if ($registerquery) {
                        // register role
                        $userid = get_user_id($username);

                        if ($userid > 0) {
                            // register user as normal user, so role id = 3 (normal user)
                            $roleid = 3;
                            $register_user_role = register_user_role($userid, $roleid);
                        }
                    }

                    if ($register_user_role) {
                        // register folder permission
                        $register_file_permission = register_file_permission($userid, $username, $path, 0, 'file/', 'folder', 100, 100, 100, 100, 100, 90);
                    }

                    if ($register_file_permission) {
                        echo "<h1>Success</h1>";
                        echo "<p>Your account and folder were successfully created. Going to login page, or <a href=\"index.php\">click here to login</a>.</p>";
                        echo "<meta http-equiv='refresh' content='3;login.php' />";
                    } else {
                        echo "<h1>Error</h1>";
                        echo "<p>Sorry, your registration failed. Please <a href='register.php'>go back and try again</a>.</p>";
                    }
                }
            } else {
            ?>

                <h1>Register</h1>

                <p>Please enter your details below to register.</p>

                <form method="post" action="register.php" name="registerform" id="registerform">
                    <fieldset>
                        <label for="username">Username:</label><input type="text" name="username" id="username" onKeyUp="limitInput(this);" onKeyDown="limitInput(this);" /><br />
                        <label for="password">Password:</label><input type="password" name="password" id="password" /><br />
                        <label for="email">Email Address:</label><input type="text" name="email" id="email" /><br />
                        <input type="submit" name="register" id="register" value="Register" />
                    </fieldset>
                </form>
                <br />
                <br />
                <p><a href="index.php">Return to main page.</a></p>

            <?php
            }
            ?>

        </div>
    </body>
</html>
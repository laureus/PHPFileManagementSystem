<?php
require_once 'res/db.php';
require_once 'res/query.php';
session_start();
require_once 'html/header.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
$index_location = "index.php";
if (!empty($_SESSION['LoggedIn']) && !empty($_SESSION['username'])) {
    header('Location:' . $index_location);
    die();
} elseif (!empty($_POST['username']) && !empty($_POST['password'])) {
    // login
    $checklogin = check_user_name($_POST['username'], $_POST['password']);
    $username = mysql_real_escape_string($_POST['username']);

    if (mysql_num_rows($checklogin) == 1) {
        $row = mysql_fetch_array($checklogin);

        $email = $row['EmailAddress'];

        $_SESSION['username'] = $username;
        $_SESSION['emailAddress'] = $email;
        $_SESSION['LoggedIn'] = 1;

        echo "<h1>Success</h1>";
        echo "<p>We are now redirecting you to the member area.</p>";
        echo "<p>If your browser could not refresh, pleas click <a href='index.php'>here</a>.</p>";
        echo "<meta http-equiv='refresh' content='2;index.php' />";
    } else {
        echo "<h1>Error</h1>";
        echo "<p>Sorry, login error. Please <a href=\"index.php\">click here to try again</a>.</p>";
    }
} else {
    // show login form
?>

            <h1>Login</h1>

            <p>Thanks for visiting! Please either login below, or <a href="register.php">click here to register</a>.</p>

            <form method="post" action="login.php" name="loginform" id="loginform">
                <fieldset>
                    <label for="username">Username:</label><input type="text" name="username" id="username" /><br />
                    <label for="password">Password:</label><input type="password" name="password" id="password" /><br />
                    <input type="submit" name="login" id="login" value="Login" />
                </fieldset>
            </form>

<?php
require_once 'html/footer.php';
}
?>

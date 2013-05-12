<?php

//  authentication

session_start();

$redirect_location = "login.php";

if (empty($_SESSION['LoggedIn']) || empty($_SESSION['username'])) {
    header('Location:' . $redirect_location);
    die();
}
?>
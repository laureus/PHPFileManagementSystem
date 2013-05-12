<?php
//session_start();

echo '';

$dbhost = "localhost"; 
$dbname = "test_php"; 
$dbuser = "user"; 
$dbpass = "password";


mysql_connect($dbhost, $dbuser, $dbpass) or die("MySQL Error: " . mysql_error());
mysql_select_db($dbname) or die("MySQL Error: " . mysql_error());  
?>

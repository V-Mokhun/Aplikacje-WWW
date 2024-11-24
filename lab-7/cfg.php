<?php
$db_host = "localhost:3306";
$db_user = "root";
$db_pass = "";
$db_name = "my_page";

$login = "some_email@example.com";
$password = "some_password";

$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$link) {
  die("Could not connect: " . mysqli_connect_error());
}

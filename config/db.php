<?php

$db_server = "10.22.97.28";
$db_user = "esicia";
$db_pass = "49a1lMlPh1dv/KVV";
$db_database = "liverw";

$db_mysql = mysqli_connect($db_server, $db_user, $db_pass, $db_database);


if (!$db_mysql || mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
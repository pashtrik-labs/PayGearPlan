<?php

$servername = "localhost";
$connUsername = "root";
$connPassword = "";
$dbname = "paygearplanDB";
$port = 3307;

$conn = mysqli_connect($servername, $connUsername, $connPassword, $dbname, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Successfully connected to paygearplanDB on port 3307!";

?>
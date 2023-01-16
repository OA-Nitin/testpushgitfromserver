<?php


$servername = "db-echeck-t3xlarge-0322.cy3zfjqhf5df.us-west-2.rds.amazonaws.com:3306";
$username = "suitecrm2022";
$password = "B2xWPKgTuF01mj7a";
$dbname = "suitecrm2022";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 
else
{
	echo "Connected";
}


?>
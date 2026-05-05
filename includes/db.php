<?php
$servername = "localhost";
$username = "s18100807_smileify_final"; // s18100807_smileify || root
$password = "P@ssw0rd"; // P@ssw0rd
$dbname = "s18100807_smileify_final"; // s18100807_smileify || smile-ify

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}  
?>  
<?php
header("Content-Type: application/json");

if (!isset($_GET['email'])) {
    echo json_encode(["valid" => false]);
    exit;
}

$email = trim($_GET['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["valid" => false]);
    exit;
}

$domain = substr(strrchr($email, "@"), 1);
$isValid = checkdnsrr($domain, "MX");

echo json_encode(["valid" => $isValid]);

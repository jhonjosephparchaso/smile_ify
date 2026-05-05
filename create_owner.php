<?php

require_once __DIR__ . '/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (php_sapi_name() !== 'cli') {
    exit("Run this script in CLI only.\n");
}

$check = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='owner'");
$count = $check->fetch_assoc()['c'];

if ($count > 0) {
    exit("Owner already exists. Aborting.\n");
}

echo "=== Smile-ify Owner Setup ===\n\n";

function prompt($label, $required = true)
{
    do {
        echo "$label: ";
        $input = trim(fgets(STDIN));
    } while ($required && $input === '');
    return $input;
}

function promptPasswordHidden($label)
{
    if (stripos(PHP_OS, 'WIN') === 0) {
        $vbs = tempnam(sys_get_temp_dir(), 'psw');
        file_put_contents($vbs,
            'wscript.echo(InputBox("' . $label . ':", "' . $label . '", ""))'
        );
        $password = rtrim(shell_exec("cscript //nologo " . escapeshellarg($vbs)));
        unlink($vbs);
        echo "\n";
        return $password;
    }

    echo "$label: ";
    system('stty -echo');
    $password = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
    return $password;
}

$username = prompt("Enter username");

do {
    $password = promptPasswordHidden("Enter password");
    $confirm  = promptPasswordHidden("Confirm password");
} while ($password !== $confirm);

$last_name  = prompt("Enter last name");
$first_name = prompt("Enter first name");

$email = prompt("Enter email");
while (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $email = prompt("Enter email");
}

$contact_number = prompt("Enter contact number");

$role = 'owner';
$status = 'Active';
$date_started = date('Y-m-d');
$date_updated = date('Y-m-d H:i:s');
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

list($cn_encrypted, $cn_iv, $cn_tag) = encryptField($contact_number);

$stmt = $conn->prepare("
    INSERT INTO users (
        username, password, first_name, last_name, email,
        contact_number, contact_number_iv, contact_number_tag,
        role, status, date_started, date_updated, date_created
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param(
    "ssssssssssss",
    $username,
    $hashed_password,
    $first_name,
    $last_name,
    $email,
    $cn_encrypted,
    $cn_iv,
    $cn_tag,
    $role,
    $status,
    $date_started,
    $date_updated
);

if ($stmt->execute()) {
    echo "\nOwner account created successfully.\n";
    echo "Username: $username\n";
    echo "Email: $email\n";
} else {
    if ($stmt->errno == 1062) {
        echo "\nERROR: An owner account already exists. MySQL prevented creating another.\n";
    } else {
        echo "\nSQL Error: " . $stmt->error . "\n";
    }
}

$stmt->close();
$conn->close();

echo "\nDelete this script after use.\n";

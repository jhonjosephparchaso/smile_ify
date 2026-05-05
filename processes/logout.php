<?php
session_start();
session_unset();
session_destroy();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
header("Location: " . BASE_URL . "/index.php");
exit;
?>

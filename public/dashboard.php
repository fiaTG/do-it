<?php
session_start();
if (!isset($_SESSION["userID"])) {
    header("Location: login.php");
    exit();
}

// Dashboard aus dem privaten Bereich laden
require_once __DIR__ . '/../private/dashboard/dashboard.php';
?>

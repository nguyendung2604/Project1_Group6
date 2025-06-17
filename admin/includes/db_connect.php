<?php
session_start();

require_once 'Database.php';
$db = new Database();
$conn = $db->connect();
?>
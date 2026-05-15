<?php
session_start();

require_once 'config/config.php';
require_once 'classes/User.php';

User::logout();
header('Location: index.php');
?>

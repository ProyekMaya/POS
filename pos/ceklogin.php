<?php
require 'function.php';
require('owner/function_owner.php');

//Jika belum login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'True') {
    header('location:login.php');
    exit();
}
?>
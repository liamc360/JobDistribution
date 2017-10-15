<?php
session_start();

if(!isset($_SESSION['login_user']) || !$_SESSION['loggedin']){
	header('Location: index.php'); // Redirecting To Home Page
}
?>
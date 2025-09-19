<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Unset all session variables
$_SESSION = array();

// If session uses cookies, delete the session cookie
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'],
		$params['secure'], $params['httponly']
	);
}

// Destroy the session
session_destroy();

header('Location: login.php');
exit();

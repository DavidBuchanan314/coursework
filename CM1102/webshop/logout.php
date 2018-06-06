<?php
// TODO prevent CSRF (not 100% necessary)

// this code to properly destroy the session was taken from https://secure.php.net/session_destroy

session_start();
$_SESSION = [];

if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'],
		$params['secure'], $params['httponly']
	);
}

session_destroy();
header('Location: index.php');
?>

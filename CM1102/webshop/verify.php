<?php

require 'private/setup.php';

// TODO: refactor this horrible code

/* Handle incoming GET requests with auth tokens */
if (isset($_GET['token']) AND isset($_GET['email'])) {
	$stmt = $con->prepare('SELECT * FROM users WHERE email=? AND token=?;'); // No SQLi here
	$stmt->bind_param('ss', $_GET['email'], $_GET['token']);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();
	
	if (!is_null($result)) {
		$stmt = $con->prepare('UPDATE users SET verified=1, token=NULL WHERE email=?;'); // No SQLi here
		$stmt->bind_param('s', $_GET['email']);
		$stmt->execute();
		
		if ($result['verified'] === 0) { // verify a new account
			$_SESSION['message'] = 'Account Verified!';
			$_SESSION['next'] = 'login.php';
			header('Location: message.php');
			exit();
		} else {// else password reset for existing account
			$_SESSION['reset-email'] = $_GET['email'];
			header('Location: reset.php');
			exit('Reset verification success.');
		}
		
	} else {
		exit('Token invalid or session expired.');
	}
} else {
	exit('Invalid request.');
}

?>

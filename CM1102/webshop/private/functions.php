<?php
function verify_captcha() { // based on: https://gist.github.com/jonathanstark/dfb30bdfb522318fc819
	
	if ($_SERVER['REMOTE_ADDR'] === "::1") return TRUE; // when testing locally
	
	$post_data = http_build_query([
		'secret' => '6LdavhoUAAAAAP3sYtt2U0hKtwbt_IdsQG87-ING', // TODO: put in setup.php
		'response' => $_POST['g-recaptcha-response'],
		'remoteip' => $_SERVER['REMOTE_ADDR']
	]);
	
	$opts = array('http' =>
		[
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $post_data
		]
	);
	
	$context  = stream_context_create($opts);
	$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', FALSE, $context);
	$result = json_decode($response);
	return $result->success;
}

function password_requirements($password) { // NOTE! This function only verifies that a password meets the basic format requirements
	return (strlen($password) >= 8);
}
?>

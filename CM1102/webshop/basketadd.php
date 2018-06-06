<?php

require 'private/setup.php';

if (isset($_POST['id'], $_POST['async'], $_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	if (!isset($_SESSION['basket'][$_POST['id']])) $_SESSION['basket'][$_POST['id']] = 0; // initialise quantity if unset
	
	$_SESSION['basket'][$_POST['id']] += 1;
	
	if ($_POST['async'] === 'FALSE') { // if async was true, we could save bandwidth by not providing a response
		array_pop($_SESSION['history']);
		header('Location: '.array_pop($_SESSION['history']));
	}
	
	exit('OK');
}

exit('Bad request.');

?>

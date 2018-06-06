<?php

require 'private/setup.php';

function validate_registration($con, $username, $email, $password) {
	
	if (!verify_captcha()) return 'Captcha verification failed.';
	
	/* Check details are valid */
	if (!ctype_alnum($username)) return 'Only alphanumeric characters are allowed in username.';
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Invalid email address.';
	if (!password_requirements($password)) return 'Password must be at least 8 characters long.';
	
	/* Check if user already exists */
	$stmt = $con->prepare('SELECT * FROM users WHERE email = ?;'); // No SQLi here
	$stmt->bind_param('s', $email);
	$stmt->execute();
	$user = $stmt->get_result()->fetch_assoc();
	
	if (!is_null($user)) return 'A user with that email address already exists!';
	
	/* Create a new user with 'user' privs */
	$stmt = $con->prepare('INSERT INTO users (name, email, hash, token, verified, priv) VALUES (?, ?, ?, ?, 0, 2);'); // No SQLi here
	$hash = password_hash($password, PASSWORD_DEFAULT);
	$token = bin2hex(random_bytes(32)); // only used for verifying accounts
	$stmt->bind_param('ssss', $username, $email, $hash, $token);
	$stmt->execute();
	
	/* Send verification email */
	$location = explode('/', $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	array_pop($location);
	array_push($location, 'verify.php');
	$location = implode('/', $location);
	mail($email, 'Account Validation' , 'Hello '.$username.",\n\n".
	'Please click this link to validate your account: https://'.$location.'?email='.urlencode($email).'&token='.$token."\n\n".
	'If you were not expecting this email, please ignore it.');
	
	return 'Account validation email has been sent to '.$email.'. Please check your inbox.';
}

if (isset($_POST['username']) AND isset($_POST['email']) AND isset($_POST['password']) AND isset($_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	$error = validate_registration($con, $_POST['username'], $_POST['email'], $_POST['password']);
}

$page = 'Sign Up';
require 'private/templates/header.php';

if (isset($error) AND $error) { ?>
		<section class="formsection">
			<div id="formerror"><?= $error ?></div>
		</section>
<?php } ?>
		<section class="formsection">
			<h1>Register</h1>
			<form action="register.php" method="POST">
				<ul>
					<li>
						<label for="username">Username:</label>
						<input required pattern="[a-zA-Z0-9]*" name="username" />
					</li>
					<li>
						<label for="email">Email:</label>
						<input required name="email" />
					</li>
					<li>
						<label for="password">Password:</label>
						<input required pattern=".{8,}" type="password" name="password" />
					</li>
					<li>
						<div class="g-recaptcha" data-sitekey="<?= $CAPTCHA_KEY ?>"></div>
					</li>
					<li>
						<input type="submit" value="Register" id="submitbtn" />
					</li>
				</ul>
				<input type="hidden" name="csrf-token" value="<?= $_SESSION["csrf-token"] ?>" />
			</form>
		</section>
<?php

require 'private/templates/footer.php';

?>

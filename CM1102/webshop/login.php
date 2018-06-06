<?php

require 'private/setup.php';

function validate_login($con, $email, $password) {
	if (!verify_captcha()) return 'Captcha verification failed.';
	
	$stmt = $con->prepare('SELECT * FROM users WHERE email = ?;'); // No SQLi here
	$stmt->bind_param('s', $email);
	$stmt->execute();
	$user = $stmt->get_result()->fetch_assoc();
	
	if (is_null($user)) return 'Incorrect email address.';
	
	if (!password_verify($password, $user['hash'])) return 'Incorrect password!';
	
	if (!$user['verified']) return 'Your account is not verified. Please check your email at ' . $user['email'];
	
	$_SESSION['user'] = $user;
	$_SESSION['priv'] = $user['priv'];
	
	if (sizeof($_SESSION['basket']) === 0 AND !is_null($user['basket'])) {
		$_SESSION['basket'] = unserialize($user['basket']);
	}
	
	return FALSE; // no errors occured
}

if (isset($_POST['email'], $_POST['password'], $_POST['csrf-token'], $_POST['g-recaptcha-response'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	$error = validate_login($con, $_POST['email'], $_POST['password']);
	
	if (!$error) { // login success
		header('Location: index.php');
		exit();
	}
	
}

$page = 'Login';
require 'private/templates/header.php';

if (isset($error) AND $error) { ?>
		<section class="formsection">
			<div id="formerror"><?= $error ?></div>
		</section>
<?php } ?>
		<section class="formsection">
			<h1>Login</h1>
			<p>Don't have an account? <a href="register.php">Register here.</a></p>
			<form action="login.php" method="POST">
				<ul>
					<li>
						<label for="email">Email:</label>
						<input required name="email" />
					</li>
					<li>
						<label for="password">Password:</label>
						<input required type="password" name="password" />
					</li>
					<li>
						<div class="g-recaptcha" data-sitekey="<?= $CAPTCHA_KEY ?>"></div>
					</li>
					<li>
						<input type="submit" value="Login" id="submitbtn" />
					</li>
				</ul>
				<input type="hidden" name="csrf-token" value="<?= $_SESSION["csrf-token"] ?>" />
			</form>
			<p><a href="recovery.php">Forgot your password?</a></p>
		</section>
<?php

require 'private/templates/footer.php';

?>

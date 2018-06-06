<?php

require "private/setup.php";

function validate_recovery($con, $email) {
	
	if (!verify_captcha()) return 'Captcha verification failed.';
	
	/* Check details are valid */
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Invalid email address.';
	
	/* Check if user already exists */
	$stmt = $con->prepare("SELECT * FROM users WHERE email=? AND verified=1;"); // No SQLi here
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$user = $stmt->get_result()->fetch_assoc();
	
	if (is_null($user)) return "An account does not exist with that username.";
	
	/* Create verification token */
	$token = bin2hex(random_bytes(32));
	$stmt = $con->prepare('UPDATE users SET token=? WHERE email=?;'); // No SQLi here
	$stmt->bind_param('ss', $token, $email);
	$stmt->execute();
	
	/* Send verification email */
	$location = explode('/', $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	array_pop($location);
	array_push($location, 'verify.php');
	$location = implode('/', $location);
	mail($email, 'Password Reset' , 'Hello '.$user['name'].",\n\n".
	'Please click this reset your password: https://'.$location.'?email='.urlencode($email).'&token='.$token."\n\n".
	'If you were not expecting this email, please ignore it.');
	
	return 'Password reset email has been sent to '.$email.'. Please check your inbox.';
}

if (isset($_POST['email']) AND isset($_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit("CSRF validation failed.");
	}
	$error = validate_recovery($con, $_POST['email']);
}

$page = "Account Recovery";
require "private/templates/header.php";

if (isset($error) AND $error) { ?>
		<section class="formsection">
			<div id="formerror"><?= $error ?></div>
		</section>
<?php } ?>
		<section class="formsection">
			<h1>Account Recovery</h1>
			<form action="recovery.php" method="POST">
				<ul>
					<li>
						<label for="email">Email</label>
						<input name="email" />
					</li>
					<li>
						<div class="g-recaptcha" data-sitekey="<?= $CAPTCHA_KEY ?>"></div>
					</li>
					<li>
						<input type="submit" value="Reset Password" id="submitbtn" />
					</li>
				</ul>
				<input type="hidden" name="csrf-token" value="<?= $_SESSION["csrf-token"] ?>" />
			</form>
		</section>
<?php

require "private/templates/footer.php";

?>

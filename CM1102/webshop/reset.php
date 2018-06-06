<?php

require 'private/setup.php';

if (!isset($_SESSION['reset-email'])) {
	header('Location: index.php');
	exit('Password reset permissions deined.');
}

function reset_password($con, $password) {
	if (!password_requirements($password)) return 'Password too short';
	
	/* Reset password */
	$stmt = $con->prepare('UPDATE users SET hash=? WHERE email=?;'); // No SQLi here
	$hash = password_hash($password, PASSWORD_DEFAULT);
	$stmt->bind_param('ss', $hash, $_SESSION['reset-email']);
	$stmt->execute();
	
	unset($_SESSION['reset-email']);
	
	$_SESSION['message'] = 'Password reset completed.';
	$_SESSION['next'] = 'login.php';
	header('Location: message.php');
	exit();
}

if (isset($_POST['password']) AND isset($_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	$error = reset_password($con, $_POST['password']);
}

$page = 'Reset';
require 'private/templates/header.php';

if (isset($error) AND $error) { ?>
		<section class="formsection">
			<div id="formerror"><?= $error ?></div>
		</section>
<?php } ?>
		<section class="formsection">
			<h1>Password Reset for <?= $_SESSION['reset-email'] ?></h1>
			<form action="reset.php" method="POST">
				<ul>
					<li>
						<label for="password">New Password:</label>
						<input required pattern=".{8,}" type="password" name="password" />
					</li>
					<li>
						<input type="submit" value="Reset Password" id="submitbtn" />
					</li>
				</ul>
				<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>" />
			</form>
		</section>

<?php

require 'private/templates/footer.php';

?>

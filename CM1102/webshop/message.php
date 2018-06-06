<?php

require 'private/setup.php';

$page = 'Message';
require 'private/templates/header.php';

if (isset($_SESSION['message'], $_SESSION['next'])) { ?>
	<h1><?= $_SESSION['message'] ?></h1>
	<p><a href="<?= $_SESSION['next'] ?>">Continue</a></p>
<?php
	unset($_SESSION['message']);
	unset($_SESSION['next']);
} else {
	header("Location: index.php");
	exit("Error");
}

require 'private/templates/footer.php';

?>

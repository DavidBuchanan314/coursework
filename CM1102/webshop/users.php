<?php

require 'private/setup.php';

if (isset($_POST['email'], $_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	foreach ($_POST['email'] as $entry) {
		$entry = explode(':', $entry);
		$stmt = $con->prepare('UPDATE users SET priv=? WHERE id=?;'); // No SQLi here
		$stmt->bind_param('is', $entry[1], $entry[0]);
		$stmt->execute();
	}
}

$page = 'Users';
require 'private/templates/header.php';

$stmt = $con->prepare('SELECT * FROM users WHERE verified=1;');
$stmt->execute();
$results = $stmt->get_result();

$privs = ['ROOT', 'ADMIN', 'USER'];

?>
<form action="users.php" method="POST">
	<table>
		<tr><th>Email</th><th>Username</th><th>Priviliges</th></tr>
<?php while ($row = $results->fetch_assoc()) {
		$email = htmlspecialchars($row['email']);
		$select = '<select name="email[]">';
		foreach ($privs as $id=>$name) {
			$select .= '<option '.($row['priv'] === $id ? 'selected ' : '').'value="'.$row['id'].':'.$id.'">'.$name.'</option>';
		}
		$select .= '</select>';
?>
		<tr><td><?= $email ?></td><td><?= $row['name'] ?></td><td><?= $select ?></td></tr>
<?php } ?>
	</table>
	<input type="submit" name="submit" value="Update"/>
	<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>" />
</form>
<?php

require 'private/templates/footer.php';

?>

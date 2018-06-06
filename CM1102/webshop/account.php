<?php

require 'private/setup.php';

$page = 'Account';
require 'private/templates/header.php';

$stmt = $con->prepare('SELECT * FROM transactions WHERE customer_id=? ORDER BY timestamp DESC;');
$stmt->bind_param('i', $_SESSION['user']['id']);
$stmt->execute();
$results = $stmt->get_result();

?>
<h1>Order History</h1>
<table>
	<tr><th>Transaction ID</th><th>Order Total</th><th>Date</th></tr>
<?php while ($row = $results->fetch_assoc()) {
$transaction = unserialize($row['transaction']);
?>
	<tr><td><a href="transactions.php?id=<?= $row['id'] ?>">Order <?= $row['id'] ?> (Details)</a></td><td><?= $transaction['price'] ?></td><td><?= $row['timestamp'] ?></td></tr>
<?php } ?>
</table>
<?php

require 'private/templates/footer.php';

?>

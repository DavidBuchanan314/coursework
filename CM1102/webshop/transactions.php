<?php

if (!isset($_GET['id'])) exit('Error');

require 'private/setup.php';

$page = 'Transactions';
require 'private/templates/header.php';

$stmt = $con->prepare('SELECT * FROM transactions WHERE id=? AND customer_id=?;');
$stmt->bind_param('ii', $_GET['id'], $_SESSION['user']['id']);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (is_null($transaction)) exit('Error');

$transaction = unserialize($transaction['transaction']);
$basket = $transaction['basket'];

?>
	<h1>Order <?= $_GET['id'] ?>:</h1>
	<table>
		<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>
<?php

foreach ($basket as $id=>$quantity) {
	$stmt = $con->prepare('SELECT * FROM computers WHERE id = ?;'); // No SQLi here
	$stmt->bind_param('i', $id);
	$stmt->execute();
	$product = $stmt->get_result()->fetch_assoc();
	
	$total = $quantity * $product['price'];
?>
		<tr><td><a href="item.php?id=<?=  $id ?>" ><?= $product['name'] ?></a></td><td><?= $quantity ?></td><td>£<?= number_format($product['price'], 2) ?></td><td>£<?= number_format($total, 2) ?></td></tr>
<?php } ?>
	</table>
	<h2 style="text-align: right;">Total: <?= $transaction['price'] ?></h2>
	<h2>Shipping Address:</h2>
	<br><pre><?= htmlspecialchars($transaction['address']) ?></pre>
<?php

require 'private/templates/footer.php';

?>

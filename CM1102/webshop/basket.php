<?php

require 'private/setup.php';

$page = 'Basket';
require 'private/templates/header.php';

if (isset($_POST['stripeToken'], $_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	require 'private/stripe-php/init.php';
	\Stripe\Stripe::setApiKey($STRIPE_PRIV_KEY);
	
	$email = $_SESSION['user'] ? $_SESSION['user']['email'] : $_POST['stripeEmail'];
	$username = $_SESSION['user'] ? $_SESSION['user']['name'] : $_POST['stripeShippingName'];
	
	$customer = \Stripe\Customer::create([
		'description' => 'Customer for ' . $email,
		'source'  => $_POST['stripeToken']
	]);

	$charge = \Stripe\Charge::create([
		'customer' => $customer->id,
		'amount'   => round($_SESSION['total']*100),
		'currency' => 'gbp'
	]);
	
	
	$_SESSION['transaction']['address'] = implode("\n", [$_POST['stripeShippingName'], $_POST['stripeShippingAddressLine1'], $_POST['stripeShippingAddressZip'], $_POST['stripeShippingAddressCity'], $_POST['stripeShippingAddressCountry']]);
	$_SESSION['transaction']['price'] = '£'.number_format($_SESSION['total'], 2);
	$transaction = serialize($_SESSION['transaction']);
	
	$customer_id = isset($_SESSION['user']) ? $_SESSION['user']['id'] : 0; // 0 = Anon
	
	$stmt = $con->prepare('INSERT INTO transactions (customer_id, transaction) VALUES (?, ?);'); // No SQLi here
	$stmt->bind_param('ss', $customer_id, $transaction);
	$stmt->execute();
	
	/* Send confirmation email */
	$location = explode('/', $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	array_pop($location);
	array_push($location, 'transactions.php');
	$location = implode('/', $location);
	mail($email, 'Order Confirmed' , 'Hello '.$username.",\n\n".
	"Your order is on its way to:\n\n".$_SESSION['transaction']['address']."\n\n".
	'Order details: https://'.$location.'?id='.$con->insert_id);
	
	/* Update stock values */
	foreach ($_SESSION['basket'] as $id=>$quantity) {
		$stmt = $con->prepare("UPDATE computers SET stock=stock-? WHERE id=?;"); // No SQLi here
		$stmt->bind_param("ii", $quantity, $id);
		$stmt->execute();
	}
	
	$_SESSION['basket'] = [];
	unset($_SESSION['transaction']);
	
	$_SESSION['message'] = 'Payment Success!';
	$_SESSION['next'] = 'index.php';
	header('Location: message.php');
	exit();
}

if (isset($_POST['submit'], $_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	foreach ($_SESSION['basket'] as $id=>$quantity) {
		if (isset($_POST[$id]) AND intval($_POST[$id]) >= 0) {
			$_SESSION['basket'][$id] = intval($_POST[$id]);
		}
	}
	$_SESSION['basket'] = array_filter($_SESSION['basket']); // This handy built-in function will remove any items bought 0 times from the basket, because 0 casts to FALSE
	header('Location: basket.php');
	exit();
}

if (sizeof($_SESSION['basket']) > 0) {

?>

<form action="basket.php" method="POST">
	<table>
		<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>
<?php

$_SESSION['total'] = 0;
foreach ($_SESSION['basket'] as $id=>$quantity) {
	$stmt = $con->prepare('SELECT * FROM computers WHERE id = ?;'); // No SQLi here
	$stmt->bind_param('i', $id);
	$stmt->execute();
	$product = $stmt->get_result()->fetch_assoc();
	if (is_null($product) OR $product['stock'] < 0 OR $product['hidden']) { // Someone tried something sneaky...
		unset($_SESSION['basket'][$id]);
		continue;
	} else if ($product['stock'] < $quantity) { // stock levels may have changed
		$quantity = $product['stock'];
		$_SESSION['basket'][$id] = $quantity;
	}
	$total = $quantity * $product['price'];
	$_SESSION['total'] += $total;
?>
		<tr><td><a href="item.php?id=<?=  $id ?>" ><?= $product['name'] ?></a></td><td><input name="<?= $id ?>" type="number" min="0" max="<?= $product['stock'] ?>" required step="1" value="<?= $quantity ?>" /></td><td>£<?= number_format($product['price'], 2) ?></td><td>£<?= number_format($total, 2) ?></td></tr>
<?php } 
$_SESSION['transaction'] = ['basket' => $_SESSION['basket']]; // Prevent the transaction from being modified before final payment
?>
	</table>
	<h2 style="text-align: right;">Total: £<?= number_format($_SESSION['total'], 2) ?></h2>
	<input type="submit" name="submit" value="Update Quantities"/>
	<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>" />
</form>

<form action="basket.php" method="POST" style="width: 100%; text-align: center;">
	<script
		src="https://checkout.stripe.com/checkout.js" class="stripe-button"
		data-key="<?= $STRIPE_PUB_KEY ?>"
		data-amount="<?= round($_SESSION['total']*100) ?>"
		data-name="Computers"
		data-description="Checkout"
		data-image="img/icon.png"
		data-locale="auto"
		data-zip-code="true"
		data-currency="gbp"
		data-bitcoin="true"
		data-shipping-address="true"
		<?= isset($_SESSION['user']) ? 'data-email="'.$_SESSION['user']['email'].'"' : '' ?>
		data-label="Checkout with Card or Bitcoin (Stripe)">
	</script>
	<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>" />
</form>
<a href="oldform/" style="font-size: 10px; color: grey;">Legacy card form, just in case it's required...</a>

<?php

} else {
	echo '<h1>Basket empty</h1>';
}

require 'private/templates/footer.php';

?>

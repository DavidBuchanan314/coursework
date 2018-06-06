<?php

require 'private/setup.php';

$page = 'Home';
require 'private/templates/header.php';

if (isset($_SESSION['user'])) {
	echo "\n\t\t<p><strong>Welcome back, ".$_SESSION['user']['name']."</strong></p>\n";
}

$stmt = $con->prepare('SELECT * FROM computers WHERE hidden=0 AND name LIKE ? '.$sorts[$sort][1].';');
$query = '%'.$query.'%';
$stmt->bind_param('s', $query);
$stmt->execute();
$products = $stmt->get_result();

?>
		<section class="products">
			<h1><?= ($products->num_rows === 0) ? 'No results found' : 'Products:' ?></h1>
			<ul>
<?php
			for ( $i = 0; $i < $products->num_rows; $i++) {
				$product = $products->fetch_assoc();
				$bought = isset($_SESSION['basket'][$product['id']]) ? $_SESSION['basket'][$product['id']] : 0;
				$button = '<input type="submit" value="Add to Basket" />';
				if (($product['stock'] - $bought) <= 0) {
					$button = '<input disabled class="soldout" type="submit" value="Out of Stock" />';
				} else if ($bought > 0) {
					$button = '<input class="bought" type="submit" value="Buy Another" />';
				}
?>
				<li>
					<img src="img/products/<?= $product["imgid"] ?>.png" alt="product image">
					<form class="basketadd" action="basketadd.php" method="POST" onsubmit="return basketadd(this);">
						<?= $button ?>
						<input type="hidden" name="id" value="<?= $product['id'] ?>" />
						<input type="hidden" name="async" value="FALSE" />
						<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>" />
					</form>
					<h2><a href="item.php?id=<?= $product['id'] ?>"><?= $product['name'] ?></a> - &pound;<?= number_format($product['price'], 2) ?></h2>
					<ul>
						<li>RAM: <?= $product['ram'] ?>KiB</li>
						<li>Release year: <?= $product['year'] ?></li>
						<li>In stock: <?= $product['stock'] ?></li>
					</ul>
					<p class="truncated"><?= $product["description"] ?></p>
				</li>
<?php } ?>
			</ul>
		</section>
<?php

require 'private/templates/footer.php';

?>

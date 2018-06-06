<?php

require 'private/setup.php';

$page = 'Item';
require 'private/templates/header.php';

if (!isset($_GET['id'])) exit("Error");

$stmt = $con->prepare('SELECT * FROM computers WHERE id=?;');
$stmt->bind_param('i', $_GET['id']);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (is_null($product)) exit('Error');

$desc = str_replace("\r\n\r\n", '</p><p>', $product['description']); // allow multiple paragraphs, markdown style
$desc = str_replace("\r\n", " ", $desc);

$bought = isset($_SESSION['basket'][$product['id']]) ? $_SESSION['basket'][$product['id']] : 0;
$button = '<input type="submit" value="Add to Basket" />';
if (($product['stock'] - $bought) <= 0) {
	$button = '<input disabled class="soldout" type="submit" value="Out of Stock" />';
} else if ($bought > 0) {
	$button = '<input class="bought" type="submit" value="Buy Another" />';
}

?>
		<section class="products">
			<ul>
				<li>
					<img src="img/products/<?= $product["imgid"] ?>.png" alt="product image">
					<form class="basketadd" action="basketadd.php" method="POST" onsubmit="return basketadd(this);">
						<?= $button ?>
						<input type="hidden" name="id" value="<?= $product['id'] ?>" />
						<input type="hidden" name="async" value="FALSE" />
						<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>" />
					</form>
					<h2><a href="#"><?= $product['name'] ?></a> - &pound;<?= number_format($product['price'], 2) ?></h2>
					<ul>
						<li>RAM: <?= $product['ram'] ?>KiB</li>
						<li>Release year: <?= $product['year'] ?></li>
						<li>In stock: <?= $product['stock'] ?></li>
					</ul>
					<p><?= $desc ?></p>
				</li>
			</ul>
		</section>
<?php

require 'private/templates/footer.php';

?>

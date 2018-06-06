<?php

require 'private/setup.php';

$page = 'Manage';
require 'private/templates/header.php';

if (isset($_POST['submit']) AND $_POST['submit'] === 'Create' AND isset($_POST['name'], $_FILES['image'], $_POST['price'], $_POST['description'], $_POST['ram'], $_POST['year'], $_POST['cpu'], $_POST['stock'], $_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	$imgid = uniqid();
	move_uploaded_file($_FILES['image']['tmp_name'], 'img/products/'.$imgid.'.png');
	$desc = htmlspecialchars($_POST["description"]);
	$name = htmlspecialchars($_POST["name"]);
	$stmt = $con->prepare("INSERT INTO computers (name, imgid, price, description, ram, year, stock, cpu) VALUES(?, ?, ?, ?, ?, ?, ?, ?);"); // No SQLi here
	$stmt->bind_param("ssdsiiis", $name, $imgid, $_POST['price'], $desc, $_POST['ram'], $_POST['year'], $_POST['stock'], $_POST['cpu']);
	$stmt->execute();
	$error = 'Product added successfully!';
}

if (isset($_POST['submit']) AND $_POST['submit'] === 'Add' AND isset($_POST['product'], $_POST['stock'], $_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	$stmt = $con->prepare("UPDATE computers SET stock=stock+? WHERE id=?;"); // No SQLi here
	$stmt->bind_param("ii", $_POST['stock'], $_POST['product']);
	$stmt->execute();
	
	$error = 'Stock updated successfully!';
}

if (isset($_POST['submit']) AND $_POST['submit'] === 'Delete' AND isset($_POST['product'], $_POST['csrf-token'])) {
	if (!hash_equals($_SESSION['csrf-token'], $_POST['csrf-token'])) {
		exit('CSRF validation failed.');
	}
	
	$stmt = $con->prepare("UPDATE computers SET hidden=1 WHERE id=?;"); // No SQLi here
	$stmt->bind_param("i", $_POST['product']);
	$stmt->execute();
	
	$error = 'Product deleted.';
}

$stmt = $con->prepare('SELECT * FROM computers WHERE hidden=0;');
$stmt->execute();
$products = $stmt->get_result();

if (isset($error) AND $error) { ?>
		<section class="formsection" style="max-width: 512px">
			<div id="formerror"><?= $error ?></div>
		</section>
<?php } ?>
		<section class="formsection" style="max-width: 512px">
			<h1>Add Stock</h1>
			<form action="manage.php" method="POST">
				<ul>
					<li>
						<label for="product">Product:</label>
						<select name="product">
<?php for ( $i = 0; $i < $products->num_rows; $i++) {
	$product = $products->fetch_assoc();
?>
							<option value="<?= $product['id'] ?>"><?= $product['name'] . ' ('.$product['stock'].')' ?></option>
<?php } ?>
						</select>
					</li>
					<li>
						<label for="stock">New Stock:</label>
						<input required type="number" name="stock" step="1" />
					</li>
					<li>
						<input type="submit" name="submit" value="Add" id="submitbtn" />
					</li>
				</ul>
				<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>" />
			</form>
		</section>
<?php
$stmt = $con->prepare('SELECT * FROM computers WHERE hidden=0;');
$stmt->execute();
$products = $stmt->get_result();
?>
		<section class="formsection" style="max-width: 512px">
			<h1>Delete Product</h1>
			<p>Note: The product will be kept in the database, so users can still see items they bought in the past</p>
			<form action="manage.php" method="POST">
				<ul>
					<li>
						<label for="product">Product:</label>
						<select name="product">
<?php for ( $i = 0; $i < $products->num_rows; $i++) {
	$product = $products->fetch_assoc();
?>
							<option value="<?= $product['id'] ?>"><?= $product['name'] ?></option>
<?php } ?>
						</select>
					</li>
					<li>
						<input type="submit" name="submit" value="Delete" id="submitbtn" />
					</li>
				</ul>
				<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>" />
			</form>
		</section>
		
		<section class="formsection" style="max-width: 512px">
			<h1>Add New Product</h1>
			<form action="manage.php" method="POST" enctype="multipart/form-data">
				<ul>
					<li>
						<label for="name">Name:</label>
						<input required name="name" />
					</li>
					<li>
						<label for="image">Image:</label>
						<input required name="image" type="file" />
					</li>
					<li>
						<label for="price">Price: (£)</label>
						<input required type="number" name="price" step="0.01" />
					</li>
					<li>
						<label for="description">Description:</label>
						<textarea required name="description" rows="10"></textarea>
					</li>
					<li>
						<label for="ram">RAM: (KiB)</label>
						<input required type="number" name="ram" step="1" />
					</li>
					<li>
						<label for="year">Year:</label>
						<input required type="number" name="year" step="1" />
					</li>
					<li>
						<label for="cpu">CPU:</label>
						<input required name="cpu" />
					</li>
					<li>
						<label for="stock">Stock:</label>
						<input required type="number" name="stock" step="1" />
					</li>
					<li>
						<input type="submit" name="submit" value="Create" id="submitbtn" />
					</li>
				</ul>
				<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>" />
			</form>
		</section>
<?php

require 'private/templates/footer.php';

?>

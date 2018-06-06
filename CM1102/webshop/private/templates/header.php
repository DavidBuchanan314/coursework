<?php
	if (!in_array($_SESSION['priv'], $pages[$page]['priv'])) {
		header('Location: index.php');
		exit();
	}
	
	if (!isset($_SESSION['q'])) $_SESSION['q'] = '';
	if (!isset($_SESSION['s'])) $_SESSION['s'] = 0;
	
	$query = isset($_GET['q']) ? $_GET['q'] : $_SESSION['q'];
	$sort = isset($_GET['s']) ? intval($_GET['s']) : $_SESSION['s'];
	if ($sort >= sizeof($sorts)) $sort = 0;
	$_SESSION['q'] = $query; // uncomment for persistent search/sort criteria
	$_SESSION['s'] = $sort;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta charset="UTF-8">
<?php foreach ($pages[$page]['styles'] as $style) { ?>
		<link rel="stylesheet" type="text/css" href="css/<?= $style ?>" />
<?php } 
		foreach ($pages[$page]['scripts'] as $script) { ?>
		<script type="text/javascript" src="<?= $script ?>"></script>
<?php } ?>
		<link rel="icon" type="image/png" href="img/icon.png" />
		<script type="text/javascript">
			var basket = <?= $basket_items ?>;
		</script>
		<title><?= $page ?></title>
	</head>
	<body>
		<header>
			<div class="centered">
				<a href="index.php?q=&amp;s="><img src="img/logo.png"></a>
			</div>
		</header>
		<div class="content">
			<canvas id="bgcanvas"></canvas>
			<div id="bgoverlay"></div>
			<script type="text/javascript" src="js/background.js"></script>
			<nav>
				<div class="centered">
					<form action="index.php" method="GET">
						<input type="search" name="q" placeholder="Search" value="<?= htmlspecialchars($query) ?>" />
						<select name="s" onchange="this.parentNode.submit();">
<?php foreach ($sorts as $id=>$value) { ?>
							<option <?= $id === $sort ? 'selected ' : '' ?>value="<?= $id ?>"><?= $value[0] ?></option>
<?php } ?>
						</select>
						<input type="submit" value="Go" />
					</form>
					<ul>
<?php foreach ($pages as $name => $info) if (in_array($_SESSION['priv'], $info['priv']) AND !isset($info['hidden'])) {
	$selected = $page === $name;
	if ($name === 'Basket') $name .= ' ('.$basket_items.')';
?>
						<li><a href="<?= $info['url'] ?>" <?php if ($selected) echo 'class="selected"'; ?>><?= $name ?></a></li>
<?php } ?>
					</ul>
				</div>
			</nav>
			<div class="centered">
			<!-- END HEADER HTML -->

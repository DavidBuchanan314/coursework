<?php

$CAPTCHA_KEY = 'REDACTED';
$STRIPE_PUB_KEY = 'REDACTED';
$STRIPE_PRIV_KEY = 'REDACTED';

require 'private/functions.php';

ini_set('session.cookie_httponly', 1); // make session cookie inaccessible from JavaScript
session_start();

if (!isset($_SESSION['history'])) $_SESSION['history'] = [];
array_push($_SESSION['history'], $_SERVER['REQUEST_URI']);

if (!isset($_SESSION['csrf-token'])) {
	$_SESSION['csrf-token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['basket'])) {
	$_SESSION['basket'] = [];
}

$basket_items = 0;
foreach ($_SESSION['basket'] as $id=>$quantity) {
	$basket_items += $quantity;
}

if (!isset($_SESSION['priv'])) $_SESSION['priv'] = 3;

$con = mysqli_connect('REDACTED');

if (!$con) exit('Unable to connect to database');

/* save basket if logged in */
if (isset($_SESSION['user'])) {
	$basket = serialize($_SESSION['basket']);
	$stmt = $con->prepare('UPDATE users SET basket=? WHERE email=?;'); // No SQLi here
	$stmt->bind_param('ss', $basket, $_SESSION['user']['email']);
	$stmt->execute();
}

/* Set security headers */
header('X-Frame-Options: DENY'); // no clickjacking
header('X-XSS-Protection: 1; mode=block'); // make reflected XSS less likely
header('Strict-Transport-Security: max-age=31536000'); // HSTS cache for 1 year
header('X-Content-Type-Options: nosniff'); // reduce likelihood of MIME-type confusion attacks

/*
 * Privilige levels:
 *
 * 0: root - can modify other user priviliges, in addition to admin permissions
 * 1: admin - can add/edit products, in addition to user permissions
 * 2: user - view account details, in addition to guest permissions
 * 3: guest - view products, buy products, add to session basket
 */

$PRIV_ANY =  [0, 1, 2, 3];
$PRIV_LOGGED_IN = [0, 1, 2];
$PRIV_LOGGED_OUT = [3];
$PRIV_ADMIN = [0, 1];
$PRIV_ROOT = [0];

$pages = [
	'Home' => [
		'url' => 'index.php?q=&amp;s=',
		'priv' => $PRIV_ANY,
		'styles' => ['products.css'],
		'scripts' => ['js/reload.js']
	],
	'Item' => [
		'url' => 'item.php',
		'priv' => $PRIV_ANY,
		'styles' => ['products.css'],
		'scripts' => ['js/reload.js'],
		'hidden' => TRUE
	],
	'Message' => [
		'url' => 'message.php',
		'priv' => $PRIV_ANY,
		'hidden' => TRUE
	],
	'Sign Up' => [
		'url' => 'register.php',
		'priv' => $PRIV_LOGGED_OUT,
		'scripts' => ['https://www.google.com/recaptcha/api.js'],
		'hidden' => TRUE
	],
	'Account Recovery' => [
		'url' => 'recovery.php',
		'priv' => $PRIV_LOGGED_OUT,
		'scripts' => ['https://www.google.com/recaptcha/api.js'],
		'hidden' => TRUE
	],
	'Reset' => [
		'url' => 'reset.php',
		'priv' => $PRIV_LOGGED_OUT,
		'hidden' => TRUE
	],
	'Debug' => [
		'url' => 'debug.php',
		'priv' => $PRIV_ROOT // TODO this should be root-only or removed entirely in production
	],
	'Manage' => [
		'url' => 'manage.php',
		'priv' => $PRIV_ADMIN
	],
	'Users' => [
		'url' => 'users.php',
		'priv' => $PRIV_ROOT,
		'styles' => ['users.css']
	],
	'Account' => [
		'url' => 'account.php',
		'priv' => $PRIV_LOGGED_IN,
		'styles' => ['users.css']
	],
	'Transactions' => [
		'url' => 'transactions.php',
		'priv' => $PRIV_LOGGED_IN,
		'styles' => ['users.css'],
		'hidden' => TRUE
	],
	'Basket' => [
		'url' => 'basket.php',
		'priv' => $PRIV_ANY,
		'styles' => ['users.css']
	],
	'Login' => [
		'url' => 'login.php',
		'priv' => $PRIV_LOGGED_OUT,
		'scripts' => ['https://www.google.com/recaptcha/api.js']
	],
	'Logout' => [
		'url' => 'logout.php',
		'priv' => $PRIV_LOGGED_IN
	]
];

foreach ($pages as $page => $info) {
	if (!isset($info['styles'])) $pages[$page]['styles'] = [];
	if (!isset($info['scripts'])) $pages[$page]['scripts'] = [];
	
	array_push($pages[$page]['styles'], 'main.css');
}

$sorts = [
	['Best match', ''],
	['Price ▲', 'ORDER BY price ASC'],
	['Price ▼', 'ORDER BY price DESC'],
	['RAM ▲', 'ORDER BY ram ASC'],
	['RAM ▼', 'ORDER BY ram DESC'],
	['Year ▲', 'ORDER BY year ASC'],
	['Year ▼', 'ORDER BY year DESC'],
	['Name ▲', 'ORDER BY name ASC'],
	['Name ▼', 'ORDER BY name DESC'],
];

?>

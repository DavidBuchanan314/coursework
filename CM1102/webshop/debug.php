<?php

require "private/setup.php";

$page = "Debug";
require "private/templates/header.php";

echo "<pre>";
var_dump(getallheaders());
var_dump($_SESSION);
var_dump($_SERVER);
echo "</pre><pre>";

$stmt = $con->prepare("SELECT * FROM users;");
$stmt->execute();
$results = $stmt->get_result();

while ($row = $results->fetch_assoc()) {
	foreach($row as $field) {
		echo htmlspecialchars($field)."\t";
	}
	echo "\n";
}

echo "</pre>";

require "private/templates/footer.php";

?>

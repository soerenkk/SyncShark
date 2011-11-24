<?php
require "header.php";
?>
<h1>Databases</h1>

<?php
$info = checkDatabases();
echo $info["status"];
echo "<br><br>";
echo $info["html"];
?>
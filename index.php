<?php
require "header.php";
?>
<h1>Status</h1>

<?php
$file_info = checkFiles(false);
echo $file_info["status"];

$database_info = checkDatabases(false);
echo $database_info["status"];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
function setSelected($file) {
	if ($file == basename($_SERVER["SCRIPT_FILENAME"])) {
		echo ' class="selected"';
	}
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <title><?php echo $_SERVER["HTTP_HOST"]; ?></title>
        <link rel="shortcut icon" href="favicon.ico" />
        <link href="style.css" type="text/css" rel="stylesheet" />
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
	</head>
	<body>
		<div style="height: 5px; background-color: #0099A7;"></div>
		<div id="head">
			<img height="40" src="images/logo.png" style="position:absolute; left: 26px; top: 19px; font-size: 16px;" />
			<span style="position:absolute; left: 84px; top: 28px; font-size: 18px;">SyncShark</span>
	
			<div id="menu">
				<a href="index.php"<?php setSelected("index.php"); ?>>Status</a>
				<a href="files.php"<?php setSelected("files.php"); ?>>Files</a>
				<a href="databases.php"<?php setSelected("databases.php"); ?>>Databases</a>
				<a href="settings.php"<?php setSelected("settings.php"); ?>>Settings</a>
				<a href="info.php"<?php setSelected("info.php"); ?>>PHPinfo</a>
			</div>
		</div>
		<div style="height: 2px; background-color: #D3D3D3;"></div>
		<div id="content">
<?php
require "init.php";
?>

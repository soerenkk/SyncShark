<?php
require "header.php";

if (!empty($_POST)) {
	$_POST["svn_enabled"] = isset($_POST["svn_enabled"]);
	foreach ($_POST as $key => $value) {
		$settings->set($key, $value);
	}
	
	if(is_file($sync_dir.".htaccess"))
	{
		unlink($sync_dir.".htaccess");
	}
	
	if(is_file($sync_dir.".htpasswd"))
	{
		unlink($sync_dir.".htpasswd");
	}
	
	if ($_POST["sync_login"] != "" || $_POST["sync_password"] != "") {
		file_put_contents($sync_dir . ".htaccess", "#\n# THIS FILE IS GENERATED - DO NOT EDIT\n#\nAuthType Basic\nAuthName \"SyncShark\"\nAuthUserFile ".$sync_dir . ".htpasswd\nRequire valid-user");
		exec('htpasswd -b -c -m '.$sync_dir.'.htpasswd "'.$_POST["sync_login"].'" "'.$_POST["sync_password"].'"', $cmd_result);
	}
	echo "<script>window.location = 'index.php';</script>";
}

?>
<h1>Settings</h1>

<form action="settings.php" method="post">
	<table>
		<tr>
			<td class="headline">Protection</td>
		</tr>
		<tr>
			<td class="label">Sync login:</td>
			<td><input type="text" name="sync_login" value="<?php echo $settings->get("sync_login"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Sync password:</td>
			<td><input type="text" name="sync_password" value="<?php echo $settings->get("sync_password"); ?>" /></td>
		</tr>
		<tr>
			<td class="headline">Production server</td>
		</tr>
		<tr>
			<td class="label">Directory:</td>
			<td><input type="text" name="production_dir" value="<?php echo $settings->get("production_dir"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Database host:</td>
			<td><input type="text" name="production_db_host" value="<?php echo $settings->get("production_db_host"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Database username:</td>
			<td><input type="text" name="production_db_user" value="<?php echo $settings->get("production_db_user"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Database password:</td>
			<td><input type="text" name="production_db_pass" value="<?php echo $settings->get("production_db_pass"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Database name:</td>
			<td><input type="text" name="production_db_name" value="<?php echo $settings->get("production_db_name"); ?>" /></td>
		</tr>
		<tr>
			<td class="headline">Development server</td>
		</tr>
		<tr>
			<td class="label">Directory:</td>
			<td><input type="text" name="devel_dir" value="<?php echo $settings->get("devel_dir"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Database host:</td>
			<td><input type="text" name="devel_db_host" value="<?php echo $settings->get("devel_db_host"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Database username:</td>
			<td><input type="text" name="devel_db_user" value="<?php echo $settings->get("devel_db_user"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Database password:</td>
			<td><input type="text" name="devel_db_pass" value="<?php echo $settings->get("devel_db_pass"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Database name:</td>
			<td><input type="text" name="devel_db_name" value="<?php echo $settings->get("devel_db_name"); ?>" /></td>
		</tr>
		<tr>
			<td class="headline">Generel</td>
		</tr>
		<tr>
			<td class="label">User running script:</td>
			<td>
<?php
exec("whoami", $whoami);
echo $whoami[0];
?>
			</td>
		</tr>
		<tr>
			<td class="label">rsync path:</td>
			<td><input type="text" name="rsync" value="<?php echo $settings->get("rsync"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Recent file extensions:</td>
			<td><input type="text" name="recent_ext" value="<?php echo $settings->get("recent_ext"); ?>" /></td>
		</tr>
		<tr>
			<td class="label">Ignore list:</td>
			<td>
				<textarea rows="8" cols="50" name="ignore_list"><?php echo $settings->get("ignore_list"); ?></textarea>
			</td>
			<td width="200" valign="top">Write one file or directory path per line. Must be relative to devel directory.</td>
			
		</tr>
<?php /*		<tr>
			<td class="label">Enable SVN:</td>
			<td width="412"><input type="checkbox" name="svn_enabled" <?php if ($settings->get("svn_enabled")) {echo 'checked="checked"';} ?> /> To use SVN an empty repository must be checked out at the development server root directory.</td>
		</tr> */ ?>
		<tr>
			<td class="label">&nbsp;</td>
			<td><input type="submit" value="Gem"/></td>
		</tr>
	</table>
</form>
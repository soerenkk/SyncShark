<?php
require_once "init.php";		

if (!empty($_POST)) {
	if (isset($_POST['action'])) {
		if ($_POST['action'] == 'updateDevFiles') {
			$excude_string = '';
			if (strpos($settings->get("devel_dir"), $settings->get("production_dir")) === 0) { // dev in production dir
				$rel_path = substr($settings->get("devel_dir"), strlen($settings->get("production_dir")));
				if (substr($rel_path, 0, 1) != '/') {
					$rel_path = '/' . $rel_path;
				}
				$excude_string = '--exclude "'.$rel_path.'" ';
			}
			$cmd = $settings->get("rsync") . ' '.$excude_string.'-r '.$settings->get("production_dir").' ' . $settings->get("devel_dir");
			exec($cmd, $result);
			echo '{}';		
		}
		if ($_POST['action'] == 'updateDevDb') {
			$cmd = 'mysqldump -u '.$settings->get('production_db_user').' --password='.$settings->get('production_db_pass').' '.$settings->get('production_db_name').' | mysql -u '.$settings->get('devel_db_user').' --password='.$settings->get('devel_db_pass').' -h '.$settings->get('devel_db_host').' '.$settings->get('devel_db_name');
			exec($cmd, $result);
			echo '{}';		
		}
		exit;		
	} else {
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
}

require "header.php";
?>
<h1>Settings</h1>

<form action="settings.php" method="post">
	<table>
		<tr>
			<td class="headline">Production</td>
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

<h1>Actions</h1>
<script>
	var updateDevFiles = function(button) {
		if (confirm('Copying all files from production to development.')) {
			$(button).replaceWith(' Working... Please wait')
			jQuery.post(window.location.pathname, {'action': 'updateDevFiles'}, function(result) {
				if (result.error) {
					alert('Error: ' + result.error);
				} else {
					window.location.reload(true);
				}
			}, "json");
		}
	}
	var updateDevDb = function(button) {
		if (confirm('Copying all data and structure from production to development.')) {
			$(button).replaceWith(' Working... Please wait')
			jQuery.post(window.location.pathname, {'action': 'updateDevDb'}, function(result) {
				if (result.error) {
					alert('Error: ' + result.error);
				} else {
					window.location.reload(true);
				}
			}, "json");
		}
	}
</script>
<p>Copy all files from production directory to development directory <input type="button" onclick="updateDevFiles(this)" value="Update development files"/></p>
<p>Copy all data and structure from production database to development database<input type="button" onclick="updateDevDb(this)" value="Update development database"/></p>

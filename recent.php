<?php
require "header.php";
?>
<script>
	jQuery(document).ready(function() {
		updateTables();
	});
</script>
<h1>Recent changed files in dev</h1>

<table class="content" width="100%">
	<thead>
		<tr>
			<th>Timestamp</th>
			<th>Path</th>
		</tr>
	</thead>

<?

	//$cmd = 'find '.$settings->get("devel_dir").' -regex \'.*\.\('.implode('\|', explode(',',$settings->get("recent_ext"))).'\)\' -type f -printf \'%T@ %p\n\' | sort -n | tail -50';
	//$cmd = 'rsync -r --list-only --exclude "/SyncShark" . | sed -r "s/^.{23}//" | sort -n';
	
	$excude_string = "";
	foreach (explode("\n", $settings->get("ignore_list")) as $e) {
		$e = trim($e);
		if ($e != "") {
			$excude_string .= '--exclude "'.$e.'" ';
		}
	}
	
	$cmd = $settings->get("rsync") . ' '.$excude_string.'-r --list-only '.$settings->get("devel_dir").' | sed -r "s/^.{23}//" | sort -n';

	exec($cmd, $result);
	$result = array_reverse($result);
	foreach ($result as $r) {
		$parts = explode(' ', $r);
		$time = $parts[0] . ' ' . $parts[1];
		$path = $parts[2];
		if (is_dir($settings->get("devel_dir") . $path)) {
			continue;
		}
?>
	<tr>
		<td width="200"><?=$time?></td>
		<td class="varname"><?=$path?></td>
	</tr>
<?		
	}
	
?>


</table>

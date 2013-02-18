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

	$cmd = 'find '.$settings->get("devel_dir").' -regex \'.*\.\('.implode('\|', explode(',',$settings->get("recent_ext"))).'\)\' -type f -printf \'%T@ %p\n\' | sort -n | tail -50';
	exec($cmd, $result);
	$result = array_reverse($result);
	foreach ($result as $r) {
		$parts = explode(' ', $r);
		$time_parts = explode('.', $parts[0]);
		$time = $time_parts[0];
		$path = substr($r, strlen($parts[0]) + 1);
		if (strpos($path, 'SyncShark') !== false) {
			continue;
		}
?>
	<tr>
		<td><?=secsToDateAndTime($time)?></td>
		<td class="varname"><?=$path?></td>
	</tr>
<?		
	}
	
?>


</table>

<?php
require "header.php";
?>
<script>
	jQuery(document).ready(function() {
		updateTables();
	});
</script>
<h1>Log</h1>

<table class="content" width="100%">
	<thead>
		<tr>
			<th>Timestamp</th>
			<th>Path</th>
			<th>Action</th>
			<th>Username</th>
		</tr>
	</thead>
<?
$log_file = 'history/SyncShark.log';
if (!file_exists($log_file)) {
	$log = file_get_contents($log_file);
	$lines = explode("\n", $log);
	
	foreach ($lines as $line) {
		if (empty($line)) {
			continue;
		}
		$l = (array)json_decode($line);
		$result = '<tr class="file-line">';
		$result .= '<td>'.secsToDateAndTime($l['created']).'</td>';
		$result .= '<td class="varname" style="padding-left:0;">'.$l['path'].'</td>';
		$result .= '<td>'.$l['action'].'</td>';
		$result .= '<td>'.$l['username'].'</td>';
		
		$result .= '</tr>';
		echo $result;
	}
}

?>
</table>
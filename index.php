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

<h1>Files</h1>
<script>
function createCloseableBox(content, tr) {
	var new_tr = $('<tr class="new-content"><td colspan="'+tr.children().size()+'"><div style="position:relative;"></div></td></tr>');
	var container = new_tr.find('div');
	var close_button = $('<div class="closebutton"></div>');
	close_button.click(function(){
		new_tr.remove();
	});
	container.append(close_button);
	container.append(content);
	return new_tr;
}

function showContent(button, action, path, args) {
	if (!args) {
		args = {};
	}
	args["action"] = action;
	args["path"] = path;
	$.post("actions.php", args, function(result) {
		var parent = $(button).parent();
		var tr = $(button).parents('tr:first');
		tr.after(createCloseableBox(result, tr));
		updateTables()
	}, "html")
}

function copy_file(src, dest, path, log_type) {
	$.post("actions.php", {"action": "copy", "src": src, "dest": dest, "path": path, "log_type": log_type}, function(result) {
		window.location.reload();
	}, "json")
}

function create_dir(dir, path) {
	$.post("actions.php", {"action": "create_dir", "dir": dir, "path": path}, function(result) {
		window.location.reload();
	}, "json")
}

$(document).ready(function() {
	$(".view-new-updated").click(function(){
		$(".file-line-uptodate").hide();
		$('tr.new-content').remove();
		updateTables();
	});
	$(".view-all").click(function(){
		$(".file-line-uptodate").show();	
		$('tr.new-content').remove();
		updateTables();
	});
	
	$(".view-new-updated").click();
});
</script>
<?php
$file_info = checkFiles();
echo $file_info["status"];
echo "<br><br>";
echo '<div style="font-size:15px;color:#747474;">Show: <label><input class="view-new-updated" type="radio" name="sex" value="male" /> New/updated files</label><label><input class="view-all" type="radio" name="sex" value="female" /> All files</label></div>';
echo "<br>";
echo $file_info["html"];




?>
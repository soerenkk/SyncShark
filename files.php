<?php
require "header.php";
?>
<h1>Files</h1>

<script>
function createCloseableBox(content) {
	var container = $('<div style="position:relative;"></div>');
	var close_button = $('<div class="closebutton"></div>');
	close_button.click(function(){
		container.remove();
	});
	container.append(close_button);
	container.append(content);
	return container;
}

function showContent(button, action, path, args) {
	if (!args) {
		args = {};
	}
	args["action"] = action;
	args["path"] = path;
	$.post("actions.php", args, function(result) {
		var parent = $(button).parent();
		parent.after(createCloseableBox(result));
//		parent.find(".info").html(result);
	}, "html")
}

function copy_file(src, dest, path) {
	$.post("actions.php", {"action": "copy", "src": src, "dest": dest, "path": path}, function(result) {
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
	});
	$(".view-all").click(function(){
		$(".file-line-uptodate").show();	
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
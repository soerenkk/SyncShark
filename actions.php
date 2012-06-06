<?php
	require "init.php";
	
	function appendToActionLog($action, $path, $data = array()) {
		$history_dir = "history/";
		if (!file_exists($history_dir)) {
			mkdir($history_dir, 0777, true);
		}

		$file = $history_dir."SyncShark.log";
		$data['action'] = $action;
		$data['path'] = $path;
		$data['created'] = time();
		$data['username'] = $_COOKIE['username'];
		$message = json_encode($data);
		$message .= "\n";
		file_put_contents($file, $message, FILE_APPEND | LOCK_EX);
	}
	
	switch ($_POST["action"]) {
		case "copy":
			$data = array();
			if (file_exists($_POST["dest"]) && is_file($_POST["dest"])) {
				$history_dir = dirname($_POST["path"]);
				if ($history_dir == ".") {
					$history_dir = "";
				} else {
					$history_dir = $history_dir . "/";				
				}
				$history_dir = "history/" . $history_dir ;
				if (!file_exists($history_dir)) {
					mkdir($history_dir, 0777, true);
				}
				
				$OrgTime = filemtime($_POST["dest"]);
				$HistoryFile = $history_dir . basename($_POST["path"]) . ".sharkhistory_" . time();
				copy($_POST["dest"], $HistoryFile);
				touch($HistoryFile, $OrgTime);
				$data['history'] = $HistoryFile;
			}

			$OrgTime = filemtime($_POST["src"]);
			copy($_POST["src"], $_POST["dest"]);
			touch($_POST["dest"], $OrgTime);
			appendToActionLog($_POST['log_type'], $_POST["path"], $data);
			echo '{"success": "true"}';
			break;
		case "create_dir":
			mkdir($_POST["dir"]);
			appendToActionLog('create_dir', $_POST["path"]);
			echo '{"success": "true"}';
			break;
		case "get_content":
			if (isImage($_POST["path"])) {
				echo '<div class="infobox">';
				echo '<img src="dev/'. $_POST["path"] .'" width="200" />';
				echo '</div>';
				break;
			}
			$cmd = "cat " . $settings->get("devel_dir") . $_POST["path"];
			exec($cmd, $content);
			echo '<div class="infobox">';
			$line = 1;
			foreach ($content as $c) {
				echo '<span class="linenumber green">'.$line.": </span>".utf8htmlentities($c) . "<br>";
				$line++;
			}
			echo '</div>';
			break;
		case "get_history_content":
			if (isImage($_POST["path"])) {
				echo '<div class="infobox">';
				echo '<img src="history/'. $_POST["path"] .".sharkhistory_".$_POST["revision"].'" width="200" />';
				echo '</div>';
				break;
			}
			echo '<div class="infobox">';
			$path = "history/".$_POST["path"].".sharkhistory_".$_POST["revision"];
			echo nl2br(utf8htmlentities(file_get_contents($path)));
//			$line = 1;
//			foreach ($content as $c) {
//				echo '<span class="linenumber green">'.$line.": </span>".utf8htmlentities($c) . "<br>";
//				$line++;
//			}
			echo '</div>';
			break;
		case "get_history":
			echo '<div class="infobox">';
			$path = $_POST["path"];
			$files = getHistoryFiles($path);
			$files = array_reverse($files);
			$last_file = $settings->get("production_dir") . $path;
			echo '<table class="content">';
			echo '<thead><tr>';
			echo 	'<th width="100%">Timestamp</th>';
			echo 	'<th>Actions</th>';
			echo '</tr></thead>';

			foreach ($files as $f) {
				$parts = explode(".sharkhistory_", $f);
				$revision = (int)$parts[1];
				echo '<tr class="file-line"><td class="varname uptodate-file">' . secsToDateAndTime($revision) .'</td><td class="buttons">';
				echo '<div class="button showcontent" onclick="showContent(this, \'get_history_content\', \''.$path.'\', {\'revision\': '.$revision.'});">Show content</div>';
				if (!isImage($parts[0])) {
					echo '<div class="button showcontent" onclick="showContent(this, \'get_diff\', \''.$f.'\', {\'other\': \''.$last_file.'\'});">Show diff</div>';
				}
				echo '</td></tr>';
				$last_file = $f;
			}
			echo '</table>';
			echo '</div>';
			break;
		case "get_diff":
			if (isImage($_POST["path"])) {
				echo '<div class="infobox">';
				echo 'Old:<img src="../'. $_POST["path"] .'" width="200" style="vertical-align:middle;" />';
				echo 'New:<img src="dev/'. $_POST["path"] .'" width="200" style="vertical-align:middle;" />';
				echo '</div>';
				break;
			}
			$file1 =  $settings->get("production_dir") . $_POST["path"];
			$file2 =  $settings->get("devel_dir") . $_POST["path"];
			if (isset($_POST["other"])) {
				$file1 = $_POST["path"];
				$file2 = $_POST["other"];
			}
			$cmd = "diff " . $file1 . " " . $file2;
			exec($cmd, $content);
			echo '<div class="infobox">';
			$first_box = true;
			foreach ($content as $c) {
				$c = trim($c);
				$first_char = substr($c, 0, 1);
				if ($first_char == "<") {
					echo '<span class="linenumber red">'.$prod_line.': </span><span class="grey">'.utf8htmlentities(substr($c, 2)) . "</span><br>";
					$prod_line++;
				
				} else if ($first_char == ">") {
					echo '<span class="linenumber green">'.$dev_line.": </span>".utf8htmlentities(substr($c, 2)) . "<br>";
					$dev_line++;
				} else if ($first_char == "-") {
				} else if ($first_char == "\\") {
				} else {
					if (!$first_box) {
						echo '</div>';
					}
					$first_box = false;
					echo '<div class="infobox">';
					$delimiters = array("a", "d", "c");
					foreach ($delimiters as $d) {
						if (strpos($c, $d) !== false) {
							$line_numbers = explode($d, $c);
						}
					}
					$prod_line_parts = (explode(",", $line_numbers[0]));
					$prod_line = $prod_line_parts[0];
					$dev_line_parts = (explode(",", $line_numbers[1]));
					$dev_line = $dev_line_parts[0];
				}
			}
			echo '    </div>';
			echo '</div>';
			break;
		default:
			echo "unknown action";
	}


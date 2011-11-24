<?php
require "class.settings.php";
require "class.dbconnection.php";

$settings = new Settings();

// set defaults
$sync_dir = dirname($_SERVER["SCRIPT_FILENAME"]) . "/";
$settings->setDefault("production_dir", dirname($sync_dir) . "/");
$settings->setDefault("devel_dir", $sync_dir . "dev/");

exec("which rsync", $rsync_path);
$settings->setDefault("rsync", $rsync_path[0]);
$settings->setDefault("ignore_list", ".svn");

function utf8htmlentities($string) {
        return htmlentities($string, ENT_QUOTES, "UTF-8");
}

function getHistoryFiles($r) {
	return glob("history/" . $r . ".sharkhistory_*");
}

function secsToDateAndTime($secs) {
	    return date("d-m-Y H:i:s", $secs);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    $start  = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}


function isImage($path) {
	$exts = array(".jpg", ".gif", ".png");
	$path_lower = strtolower($path);
	
	foreach ($exts as $ext) {
		if (endsWith($path_lower, $ext)) {
			return true;
		}
	}
	return false;
}

function filterLines($lines, $filters) {
	$result = array();
	foreach ($lines as $line) {
		$add = true;
		foreach ($filters as $filter) {
			if (strpos($line, $filter) === 0 || trim($line) == "") {
				$add = false;
			}
		}
		if ($add) {
			$result[] = $line;
		}
	}
	return $result;
}

function checkFiles() {
	$html = "";
	global $settings;
	$RSYNC_SPAM = array(
		"skipping non-regular",
		"sending incremental file list",
		"building file list",
		"sent ",
		"[sender]",
		"total size is",
		"delta-transmission ",
		"total: ",
	);
	
	$excude_string = "";
	foreach (explode("\n", $settings->get("ignore_list")) as $e) {
		$e = trim($e);
		if ($e != "") {
			$excude_string .= '--exclude "'.$e.'" ';
		}
	}
	
	$cmd = $settings->get("rsync") . " ".$excude_string."-rnvvc ".$settings->get("devel_dir")." ".$settings->get("production_dir");
	//echo $cmd;
	exec($cmd, $result);
	$differences = 0;
	
	$result = filterLines($result, $RSYNC_SPAM);
	$uptodate_text = " is uptodate";
	foreach ($result as $r) {		
		if (strpos($r, $uptodate_text) !== false) {
			$r = substr($r, 0, strlen($r) - strlen($uptodate_text));
			$uptodate = true;
		} else {
			$uptodate = false;
			$differences++;		
		}
		if ($uptodate && count(getHistoryFiles($r)) == 0) {
			continue;
		}
		
		$dev_file = $settings->get("devel_dir") . $r;
		$prod_file = $settings->get("production_dir") . $r;
		$file_line_class = "";
		if (file_exists($prod_file)) {
			if ($uptodate) {
				$type = "uptodate-file";
				$file_line_class = "file-line-uptodate";
			} else {
				$type = "updated-file";	
			}
		} else {
			$parent_exists = file_exists(dirname($prod_file));
			if (is_dir($dev_file)) {
				$type = "new-dir";			
			} else {
				$type = "new-file";
			}
		}
		$html .= '<div class="file-line '.$file_line_class.'">';
		$html .= '<div class="varname '.$type.'">'.$r . '</div><div class="buttons">';
		if ($type == "new-dir") {
			if ($parent_exists) {
				$html .= '<div class="button" onclick="create_dir(\''.$prod_file.'\', \''.$r.'\')">Opret</div>';
			}
		}
		if ($type == "new-file") {
			$html .= '<div class="button showcontent" onclick="showContent(this, \'get_content\', \''.$r.'\');">Show content</div>';
			if ($parent_exists) {
				$html .= '<div class="button" onclick="copy_file(\''.$dev_file.'\', \''.$prod_file.'\',  \''.$r.'\')">Create</div>';
			}
		}
		if ($type == "updated-file") {
			$html .= '<div class="showcontent button" onclick="showContent(this, \'get_diff\', \''.$r.'\');">Show diff</div>';
			$html .= '<div class="button" onclick="copy_file(\''.$dev_file.'\', \''.$prod_file.'\',  \''.$r.'\')">Update</div>';
			$html .= '<div class="button" onclick="copy_file(\''.$prod_file.'\', \''.$dev_file.'\')">Revert</div>';
			if (count(getHistoryFiles($r)) > 0) {
				$html .= '<div class="showhistory button" onclick="showContent(this, \'get_history\', \''.$r.'\');">History</div>';			
			}
		}
		if ($type == "uptodate-file") {
			$html .= '<div class="showcontent button" onclick="showContent(this, \'get_content\', \''.$r.'\');">Show content</div>';
			if (count(getHistoryFiles($r)) > 0) {
				$html .= '<div class="showhistory button" onclick="showContent(this, \'get_history\', \''.$r.'\');">History</div>';			
			}
		}
		$html .= '</div><div class="info"></div></div>'."\n";
	}
	if ($differences > 0) {
		$status = '<a href="files.php"><div class="warning">'.$differences. ' differences detected in files.</div></a>';
	} else {
		$status = '<div class="success">Files are synchronized.</div>';
	}
	
	return array("html" => $html, "status" => $status);
}


function checkDatabases($print = true) {
	global $settings;
	$dev_db = new DBConnection($settings->get("devel_db_host"), $settings->get("devel_db_name"), $settings->get("devel_db_user"), $settings->get("devel_db_pass"));
	$table_rows = $dev_db->execute("SHOW TABLES");
	if ($table_rows === false) {
		$status = '<div class="warning">Could not connect to devel database. Check connection parameters in <a href="settings.php">settings</a>.</div>';
		return array("html" => "", "status" => $status);
	}
	$dev_tables = array();
	foreach ($table_rows as $row) {
		$dev_tables[] = $row[0];
	}
	
	$prod_db = new DBConnection($settings->get("production_db_host"), $settings->get("production_db_name"), $settings->get("production_db_user"), $settings->get("production_db_pass"));
	$table_rows = $prod_db->execute("SHOW TABLES");
	if ($table_rows === false) {
		$status = '<div class="warning">Could not connect to production database. Check connection parameters in <a href="settings.php">settings</a>.</div>';
		return array("html" => "", "status" => $status);
	}
	$html = "";
	$prod_tables = array();
	foreach ($table_rows as $row) {
		$prod_tables[] = $row[0];
	}
	
	$warnings = 0;
	foreach ($prod_tables as $t) {
		if (in_array($t, $dev_tables)) {
			$rows = $dev_db->execute("SHOW COLUMNS FROM `".$t."`");
			$dev_columns = array();
			foreach($rows as $row) {
				$dev_columns[$row[0]] = $row;
			}
			$rows = $prod_db->execute("SHOW COLUMNS FROM `".$t."`");
			$prod_columns = array();
			foreach($rows as $row) {
				$prod_columns[$row[0]] = $row;
			}
			foreach ($prod_columns as $c => $row) {
				if (!array_key_exists($c, $dev_columns)) {
					$html .= '<div class="file-line"><div class="varname warning-small" title="Missing field">Column '.$t.'.'.$c.' does not exist in development database!</div></div>';
					$warnings++;
				} else {
					$key_lists = array("Type" => "Type", "Null" => "Allow Null", "Default" => "Default", "Extra" => "Extra");
					$prod_row = $prod_columns[$c];
					$dev_row = $dev_columns[$c];
					foreach ($key_lists as $key => $readable_key) {
						if ($prod_row[$key] != $dev_row[$key]) {
							$html .= '<div class="file-line"><div class="varname warning-small" title="Different attribute">Column '.$t.'.'.$c.'\'s attribute '.$readable_key.' is different. Production: '.$prod_row[$key].' Development: '.$dev_row[$key].'</div></div>';
							$warnings++;
						}
					}
				}
			}
			
			// indexes
			$rows = $dev_db->execute("SHOW INDEXES FROM `".$t."`");
			$dev_columns = array();
			foreach($rows as $row) {
				$dev_columns["Index name: " . $row["Key_name"] ." Column: ". $row["Column_name"]] = $row;
			}
			$rows = $prod_db->execute("SHOW INDEXES FROM `".$t."`");
			$prod_columns = array();
			foreach($rows as $row) {
				$prod_columns["Index name: " . $row["Key_name"] ." Column: ". $row["Column_name"]] = $row;
			}
			
			foreach ($prod_columns as $c => $row) {
				if (!array_key_exists($c, $dev_columns)) {
					$html .= '<div class="file-line"><div class="varname warning-small" title="New index">'.$t.' '.$c.' does not exist in development database!</div></div>';
					$warnings++;
				} else {
					$key_lists = array("Non_unique" => "Non_unique", "Seq_in_index" => "Seq_in_index", "Index_type" => "Index_type", "Null" => "Null");
					$prod_row = $prod_columns[$c];
					$dev_row = $dev_columns[$c];
					foreach ($key_lists as $key => $readable_key) {
						if ($prod_row[$key] != $dev_row[$key]) {
							$html .= '<div class="file-line"><div class="varname warning-small" title="Different attribute">Table: '.$t.' '.$c.' Attribute: '.$readable_key.' is different. Production: '.$prod_row[$key].' Development: '.$dev_row[$key].'</div></div>';
							$warnings++;
						}
					}
				}
			}
		} else {
			$html .= '<div class="file-line"><div class="varname warning-small" title="Missing table">Table '.$t.' does not exist in development database!</div></div>';
			$warnings++;
		}
	}

	foreach ($dev_tables as $t) {
		if (in_array($t, $prod_tables)) {
			$rows = $dev_db->execute("SHOW COLUMNS FROM `".$t."`");
			$dev_columns = array();
			foreach($rows as $row) {
				$dev_columns[$row[0]] = $row;
			}
			$rows = $prod_db->execute("SHOW COLUMNS FROM `".$t."`");
			$prod_columns = array();
			foreach($rows as $row) {
				$prod_columns[$row[0]] = $row;
			}
			foreach ($dev_columns as $c => $row) {
				if (!array_key_exists($c, $prod_columns)) {
					$null_default = "";
					if ($row["Null"] == "YES" && $row["Default"] == "") {
						$null_default = " DEFAULT NULL";
					} else {
						if ($row["Null"] == "NO") {
							$null_default = " NOT NULL";
						}
						if ($row["Default"] != "") {
							$null_default .= " DEFAULT '".$row["Default"]."'";
						}
						
					}
					$html .= '<div class="file-line"><div class="varname new-column" title="New field">'.$t.'.'.$c.' <span class="small grey">ALTER TABLE '.$t. ' ADD COLUMN '.$c.' '.$row["Type"].$null_default.';</span></div></div>';
					$warnings++;
				}
			}
			
			//indexes
			$rows = $dev_db->execute("SHOW INDEXES FROM `".$t."`");
			$dev_columns = array();
			foreach($rows as $row) {
				$dev_columns[] = "Index name: " . $row["Key_name"] ." Column: ". $row["Column_name"];
			}
			$rows = $prod_db->execute("SHOW INDEXES FROM `".$t."`");
			$prod_columns = array();
			foreach($rows as $row) {
				$prod_columns[] = "Index name: " . $row["Key_name"] ." Column: ". $row["Column_name"];
			}
			foreach ($dev_columns as $c) {
				if (!in_array($c, $prod_columns)) {
					$html .= '<div class="file-line"><div class="varname index" title="New index">'.$t.' '.$c.'</div></div>';
					$warnings++;
				}
			}

			
		} else {
			$html .= '<div class="file-line"><div class="varname new-table" title="New table">'.$t.'</div></div>';
			$warnings++;
		}
	}
	if ($warnings > 0) {
		$status = '<a href="databases.php"><div class="warning">'.$warnings . ' differences detected in databases.</div></a>';
	} else {
		$status = '<div class="success">Databases are synchronized.</div>';
	}
	return array("html" => $html, "status" => $status);
}


?>
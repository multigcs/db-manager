<?php

$DATABASE = "";
$TABLE = "";

include("config.inc");
include("html.php");

// read DATABASE from POST or GET
if ($_POST['database'] != "") {
	$DATABASE = $_POST['database'];
} else if ($_GET['database'] != "") {
	$DATABASE = $_GET['database'];
}

// read TABLE from POST or GET
if ($_POST['table'] != "") {
	$TABLE = $_POST['table'];
} else if ($_GET['table'] != "") {
	$TABLE = $_GET['table'];
}

$db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DATABASE);
if ($db->connect_errno > 0) {
	die('Unable to connect to database [' . $db->connect_error . ']');
}

html_page_head("Database-Manager: $DATABASE @ $DB_HOST");
html_sidebar_head();

// read DATABASES from database
$DATABASES = array();
html_sidebar_sub_head("Databases", "", "fa-desktop", $TABLE == "");
if ($result = $db->query("SHOW DATABASES;")) {
	while ($row = $result->fetch_assoc()) {
		$DATABASES[] = $row['Database'];
		html_sidebar_entry(ucwords($row['Database'], "_ "), "?database=" . $row['Database'], "fa-desktop", $DATABASE == $row['Database']);
	}
}
html_sidebar_sub_foot();

// read TABLES from database
$TABLES = array();
$TABLE_COLS = array();
html_sidebar_sub_head("Tables", "", "fa-dashboard", $TABLE != "");
if ($result = $db->query("SHOW TABLES;")) {
	while ($row = $result->fetch_assoc()) {
		$key = "Tables_in_" . $DATABASE;
		$TABLES[] = $row[$key];
		if ($TABLE == "") {
			$TABLE = $row[$key];
		}
		html_sidebar_entry(ucwords($row[$key], "_ "), "?database=$DATABASE&table=" . $row[$key], "fa-dashboard", $TABLE == $row[$key]);
		if ($result2 = $db->query("SHOW COLUMNS FROM " . $row[$key] . ";")) {
			while ($row2 = $result2->fetch_assoc()) {
				if (preg_match("/_id/", $row2['Field'])) {
					$TABLE_COLS[$row[$key]][$row2['Field']] = $row2['Field'];
				}
			}
		}
	}
}
html_sidebar_sub_foot();
html_sidebar_foot();

// read COLS from database
$COLS = array();
if ($result = $db->query("SHOW COLUMNS FROM $TABLE;")) {
	while ($row = $result->fetch_assoc()) {
		$COLS[] = $row;
	}
}

// read VALS from POST-Values
$VALS = array();
foreach ($COLS as $COL) {
	$key = "val_" . $COL['Field'];
	$VALS[$COL['Field']] = $_POST[$key];
}

// Save or Update database
if ($_POST['do_cancel'] == "Cancel") {
	$VALS['id'] = "";
} else if ($_POST['do_save'] == "Save") {
	if ($VALS['id'] == "-1") {
		$N = 0;
		$SQL = "INSERT INTO $TABLE (";
		foreach ($COLS as $COL) {
			if (preg_match("/auto_increment/", $COL['Extra'])) {
			} else if (preg_match("/^varchar/", $COL['Type']) || preg_match("/^text/", $COL['Type'])) {
				if ($N != 0) {
					$SQL .= ", ";
				}
				$SQL .= $COL['Field'];
				$N++;
			} else if (preg_match("/^int/", $COL['Type'])) {
				if ($N != 0) {
					$SQL .= ", ";
				}
				$SQL .= $COL['Field'];
				$N++;
			}
		}
		$SQL .= ") VALUES (";
		$N = 0;
		foreach ($COLS as $COL) {
			if (preg_match("/auto_increment/", $COL['Extra'])) {
			} else if (preg_match("/^varchar/", $COL['Type']) || preg_match("/^text/", $COL['Type'])) {
				if ($N != 0) {
					$SQL .= ", ";
				}
				$SQL .= "'" . $VALS[$COL['Field']] . "'";
				$N++;
			} else if (preg_match("/^int/", $COL['Type'])) {
				if ($N != 0) {
					$SQL .= ", ";
				}
				$SQL .= "'" . $VALS[$COL['Field']] . "'";
				$N++;
			}
		}
		$SQL .= ");";
		if ($result = $db->query($SQL)) {
			$VALS['id'] = $db->insert_id;
#			echo "SAVED (" . $VALS['id'] . ")<BR>";
		}
	} else {
		$N = 0;
		$SQL = "UPDATE $TABLE SET ";
		foreach ($COLS as $COL) {
			if (preg_match("/auto_increment/", $COL['Extra'])) {
			} else if (preg_match("/^varchar/", $COL['Type']) || preg_match("/^text/", $COL['Type'])) {
				if ($N != 0) {
					$SQL .= ", ";
				}
				$SQL .= $COL['Field'] . "='" . $VALS[$COL['Field']] . "'";
				$N++;
			} else if (preg_match("/^int/", $COL['Type'])) {
				if ($N != 0) {
					$SQL .= ", ";
				}
				$SQL .= $COL['Field'] . "='" . $VALS[$COL['Field']] . "'";
				$N++;
			}
		}
		$SQL .= " WHERE id='" . $VALS['id'] . "';";
		if ($result = $db->query($SQL)) {
#			echo "UPDATED<BR>";
		}
	}
} else if ($_GET['ID'] != "") {
	$VALS['id'] = $_GET['ID'];
}


html_main_head("Table " . ucwords($TABLE, "_ "), "");

// read VALS from database
if ($VALS['id'] != "") {
	if ($VALS['id'] != "-1") {
		if ($result = $db->query("SELECT * FROM $TABLE WHERE id='" . $VALS['id'] . "';")) {
			$VALS = array();
			while ($row = $result->fetch_assoc()) {
				foreach ($row as $key => $value) {
					$VALS[$key] = $value;
				}
			}
		}
	}
	// show form
	html_panel_head(ucwords($TABLE, "_ ") . " (edit)");
	html_form_head("?", "POST");

	foreach ($COLS as $COL) {
		if (preg_match("/auto_increment/", $COL['Extra'])) {
		} else if (preg_match("/^varchar/", $COL['Type']) || preg_match("/^text/", $COL['Type'])) {
			html_form_input($COL['Field'], "val_" . $COL['Field'], $VALS[$COL['Field']]);
		} else if (preg_match("/^int/", $COL['Type'])) {
			echo $COL['Field'] . ":</BR>";
			if (preg_match("/_id/", $COL['Field'])) {
				echo "<SELECT size=\"1\" name=\"val_" . $COL['Field'] . "\">";
				$LINK_TABLE = explode("_", $COL['Field'])[0];
				if ($result = $db->query("SELECT * FROM $LINK_TABLE;")) {
					while ($row = $result->fetch_assoc()) {
						$linkid = $row['id'];
						foreach ($row as $key => $value) {
							if ($key != "id") {
								if ($linkid == $VALS[$COL['Field']]) {
									echo " <OPTION value=\"$linkid\" selected>$linkid: $value</OPTION>";
								} else {
									echo " <OPTION value=\"$linkid\">$linkid: $value</OPTION>";
								}
								break;
							}
						}
					}
				}
				echo "</SELECT>";
				echo "</BR>";
			} else {
				html_form_input($COL['Field'], "val_" . $COL['Field'], $VALS[$COL['Field']]);
			}
		}
	}
	html_form_input("", "val_id", $VALS['id'], "", "hidden");
	html_form_input("", "table", $TABLE, "", "hidden");
	html_form_button("do_save", "Save");
	html_form_button("do_cancel", "Cancel");

	html_form_foot();
	html_panel_foot();

} else {
	if ($_GET['search_col'] != "" && $_GET['search_str'] != "") {
		if (preg_match("/%/", $_GET['search_str'])) {
			$SQL = "SELECT * FROM $TABLE WHERE " . $_GET['search_col'] . " like '" . $_GET['search_str'] . "';";
		} else {
			$SQL = "SELECT * FROM $TABLE WHERE " . $_GET['search_col'] . "='" . $_GET['search_str'] . "';";
		}
	} else {
		$SQL = "SELECT * FROM $TABLE;";
	}
	if ($result = $db->query($SQL)) {
		html_panel_head(ucwords($TABLE, "_ ") . " (" . mysqli_num_rows($result) . ")");
		echo "<A href=\"?database=$DATABASE&table=$TABLE&ID=-1\">ADD</A><BR>";
		html_table_head();
		$N = 0;
		while ($row = $result->fetch_assoc()) {
			if ($N == 0) {
				echo "<TR>";
				echo "<TH>ACTION</TH>";
				foreach ($row as $key => $value) {
					if ($key != "id") {
						$LINK_TITLE = str_replace("_id", "", $key);
						echo "<TH>" . ucwords($LINK_TITLE, "_ ") . "</TH>";
					}
				}
				foreach ($TABLE_COLS as $LINK_TABLE => $COLS) {
					foreach ($COLS as $COL) {
						if ($COL == $TABLE . "_id") {
							$LINK_TITLE = str_replace($TABLE . "_has_", "", $LINK_TABLE);
							$LINK_TITLE = str_replace("_has_" . $TABLE, "", $LINK_TITLE);
							echo "<TH>" . ucwords($LINK_TITLE, "_ ") . "</TH>";
						}
					}
				}
				echo "</TR>";
			}
			echo "<TR>";
			$CN = 0;
			$RID = -1;
			foreach ($row as $key => $value) {
				if ($key == "id") {
					$RID = $value;
					echo "<TD><A href=\"?database=$DATABASE&table=$TABLE&ID=$RID\">EDIT</A></TD>";
				} else {
					if (preg_match("/_id/", $key)) {
						$LINK_TABLE = explode("_", $key)[0];
						if ($result2 = $db->query("SELECT * FROM $LINK_TABLE WHERE id='" . $value . "';")) {
							while ($row2 = $result2->fetch_assoc()) {
								foreach ($row2 as $key2 => $value2) {
									if ($key2 != "id") {
										if ($linkid == $VALS[$COL['Field']]) {
											break;
										}
									}
								}
							}
						}
						echo "<TD><A href=\"?database=$DATABASE&table=$LINK_TABLE&search_col=ID&search_str=$value\">$value2</A></TD>";
					} else {
						echo "<TD>$value</TD>";
					}
				}
				$CN++;
			}
			foreach ($TABLE_COLS as $LINK_TABLE => $COLS) {
				foreach ($COLS as $COL) {
					if ($COL == $TABLE . "_id") {
						$NUM = 0;
						if ($result2 = $db->query("SELECT id FROM $LINK_TABLE WHERE $COL='$RID';")) {
							$NUM = mysqli_num_rows($result2);
						}
						echo "<TD><A href=\"?database=$DATABASE&table=$LINK_TABLE&search_col=$COL&search_str=$RID\">#$NUM</A></TD>";
					}
				}
			}
			echo "</TR>";
			$N++;
		}
		html_table_foot();
	}
	html_panel_foot();
}

html_main_foot();
html_page_foot();

?>

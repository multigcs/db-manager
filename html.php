<?php

function html_page_head ($title = "title") {
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $title; ?></title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/basic.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body>
    <div id="wrapper">
<?php
}

function html_page_foot () {
?>
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/jquery.metisMenu.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>
<?php
}

function html_sidebar_head () {
?>
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
<?php
}

function html_sidebar_entry ($title = "title", $link = "#link", $icon = "", $active = false) {
?>
                    <li>
                        <?php if ($active) {$active = "active-menu";} else {$active = "";}; echo "<a class=\"$active\" href=\"$link\"><i class=\"fa $icon\"></i>$title</a>"; ?>
                    </li>
<?php
}

function html_sidebar_foot () {
?>
                </ul>
            </div>
        </nav>
<?php
}

function html_main_head ($title = "side - title", $comment = "side - comment") {
?>
    <div id="page-wrapper">
        <div id="page-inner">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="page-head-line"><?php echo $title; ?></h1>
                    <?php if ($comment != "") {echo "<h1 class=\"page-subhead-line\">$comment</h1>";} ?>
                </div>
            </div>
<?php
}

function html_main_foot () {
?>
        </div>
    </div>
<?php
}

function html_panel_head ($title = "panel - title") {
?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php echo $title; ?>
                        </div>
                        <div class="panel-body">
<?php
}

function html_panel_foot () {
?>
                        </div>
                    </div>
                </div>
            </div>
<?php
}

function html_table_head () {
?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
<?php
}

function html_table_foot () {
?>
                                </table>
                            </div>
<?php
}

function html_form_head ($action = "", $method = "") {
?>
                            <form role="form" action="<?php echo $action; ?>" method="<?php echo $method; ?>">
<?php
}

function html_form_input ($title, $name, $value, $help = "", $type = "text") {
	if ($type == "hidden") {
?>
                                <input class="form-control" type="<?php echo $type; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
<?php
	} else {
?>
                                <div class="form-group">
                                    <label><?php echo $title; ?></label>
                                    <input class="form-control" type="<?php echo $type; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
                                    <?php if ($help != "") {echo "<p class=\"help-block\">$help</p>";} ?>
                                </div>
<?php
	}
}

function html_form_button ($name, $value = "") {
?>
                                <button type="submit" class="btn btn-info" name="<?php echo $name; ?>" value="<?php echo $value; ?>"><?php echo $value; ?></button>
<?php
}

function html_form_foot () {
?>
                            </form>
<?php
}

?>

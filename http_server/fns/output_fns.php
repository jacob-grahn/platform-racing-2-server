<?php

//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function output_header($title='', $mod=false) {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PR2 Hub - <?php echo $title; ?></title>
    <link href="//pr2hub.com/pr2hub.css" rel="stylesheet" type="text/css"/>
	<?php if($mod) { ?>
	<script src="http://code.jquery.com/jquery-latest.min.js"></script>
	<script src="http://malsup.github.com/jquery.form.js"></script>
	<?php } ?>
</head>

<body>

<div id="container">
	
	<div id="header">
	</div>

	<div id="body">
			<div class="content">

<?php

}



//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function output_mod_header($title='') {
	output_header($title, true);
}


function output_footer() {
?>

		</div>
	</div>

	<div id="footer">
		<ul class="footer_links">
			<li><a href="//pr2hub.com/backups">Backups</a></li>
			<li><a href="https://jiggmin2.com/forums/showthread.php?tid=19">Folding@Home</a></li>
			<li><a href="https://jiggmin2.com">Jiggmin2.com</a></li>
			<li><a href="https://jiggmin2.com/forums/showthread.php?tid=385">Rules</a></li>
		</ul>
	</div>
</div>

<?php
}


function output_mod_navigation() {
?>

<p>
<b><a href="http://pr2hub.com/mod/reported_messages.php">Reported Messages</a> - <a href="http://pr2hub.com/mod/player_search.php">Player Search</a> - <a href="http://pr2hub.com/bans/bans.php">Ban Log</a></b>
</p>
<p>---</p>

<?php
}
?>

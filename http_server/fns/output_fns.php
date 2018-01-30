<?php


//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function output_header($title='', $formatting_for_mods=false, $formatting_for_admins=false) {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PR2 Hub - <?php echo $title; ?></title>
    <link href="//pr2hub.com/pr2hub.css" rel="stylesheet" type="text/css"/>
	<?php if($formatting_for_mods) { ?>
		<script src="https://code.jquery.com/jquery-latest.min.js"></script>
		<script src="https://malsup.github.com/jquery.form.js"></script>
	<?php } ?>
</head>

<body>

<div id="container">

	<div id="header">
	</div>

	<div id="body">
			<?php if(basename($_SERVER['PHP_SELF']) == "index.php") { ?>
				<div class="above_game_ad">
					<script type="text/javascript"><!--
						google_ad_client = "ca-pub-1320797949858618";
						/* Above Game */
						google_ad_slot = "8735302549";
						google_ad_width = 728;
						google_ad_height = 90;
						//-->
					</script>
					<script type="text/javascript"
					src="//pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>
				</div>
			<?php } ?>
			<div class="content">

<?php
	if($formatting_for_mods) {
		output_mod_navigation($formatting_for_admins);
	}
}


function output_footer() {
?>

		</div>
	</div>

	<div id="footer">
		<ul class="footer_links">
			<li><a href="//pr2hub.com/backups">Backups</a></li>
			<li><a href="https://jiggmin2.com/forums/showthread.php?tid=19">Folding@Home</a></li>
			<li><a href="//pr2hub.com/terms_of_use.php">Terms of Use</a></li>
			<li><a href="https://jiggmin2.com/forums/showthread.php?tid=385">Rules</a></li>
		</ul>
	</div>
</div>

<?php
}


function output_mod_navigation($formatting_for_admins=true) {
?>

	<p>
		<b>
			<a href="//pr2hub.com/mod/reported_messages.php">Reported Messages</a>
			-
			<a href="//pr2hub.com/mod/player_search.php">Player Search</a>
			-
			<a href="//pr2hub.com/bans/bans.php">Ban Log</a>
			-
			<a href="//pr2hub.com/mod/mod_log.php">Mod Action Log</a>
			<?php if($formatting_for_admins) { ?>
			<br>
			<a href="//pr2hub.com/admin/player_deep_info.php">Update Account</a>
			-
			<a href="//pr2hub.com/admin/guild_deep_info.php">Update Guild</a>
			-
			<a href="//pr2hub.com/admin/set_campaign.php">Set Custom Campaign</a>
			-
			<a href="//pr2hub.com/admin/admin_log.php">Admin Action Log</a>
			<?php } ?>
			
		</b>
	</p>
	<p>---</p>

<?php
}
?>
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
	<?php if(basename($_SERVER['PHP_SELF']) == "index.php") { ?>
		<link rel="preload" href="https://adservice.google.com/adsid/integrator.js?domain=pr2hub.com" as="script">
		<script src="https://pagead2.googlesyndication.com/pub-config/r20160913/ca-pub-1320797949858618.js"></script>
		<script type="text/javascript" src="https://adservice.google.com/adsid/integrator.js?domain=pr2hub.com"></script>
	<?php } ?>
	<?php if($mod) { ?>
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
					<script type="text/javascript" src="//pagead2.googlesyndication.com/pagead/show_ads.js"></script>
					<ins id="aswift_0_expand" style="display:inline-table;border:none;height:90px;margin:0;padding:0;position:relative;visibility:visible;width:728px;background-color:transparent;">
						<ins id="aswift_0_anchor" style="display:block;border:none;height:90px;margin:0;padding:0;position:relative;visibility:visible;width:728px;background-color:transparent;">
							<iframe width="728" height="90" frameborder="0" marginwidth="0" marginheight="0" vspace="0" hspace="0" allowtransparency="true" scrolling="no" allowfullscreen="true" onload="var i=this.id,s=window.google_iframe_oncopy,H=s&amp;&amp;s.handlers,h=H&amp;&amp;H[i],w=this.contentWindow,d;try{d=w.document}catch(e){}if(h&amp;&amp;d&amp;&amp;(!d.body||!d.body.firstChild)){if(h.call){setTimeout(h,0)}else if(h.match){try{h=s.upd(h,i)}catch(e){}w.location.replace(h)}}" id="aswift_0" name="aswift_0" style="left:0;position:absolute;top:0;width:728px;height:90px;"></iframe>
						</ins>
					</ins>
				</div>
			<?php } ?>
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
			<li><a href="//pr2hub.com/terms-of-use.php">Terms of Use</a></li>
			<li><a href="https://jiggmin2.com/forums/showthread.php?tid=385">Rules</a></li>
		</ul>
	</div>
</div>

<?php
}


function output_mod_navigation() {
?>

<p>
<b><a href="//pr2hub.com/mod/reported_messages.php">Reported Messages</a> - <a href="//pr2hub.com/mod/player_search.php">Player Search</a> - <a href="//pr2hub.com/bans/bans.php">Ban Log</a></b>
</p>
<p>---</p>

<?php
}
?>

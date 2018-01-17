<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Platform Racing 2</title>
    <link href="pr2hub.css" rel="stylesheet" type="text/css"/>
</head>

<body>
	
<div id="container">

	<div id="header">
	</div>

	<div id="body">
		
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

		<br />

		<div id="artifact">

			<center>

			<img src="artifact.png">

            <?php
            $file = file_get_contents("https://pr2hub.com/files/artifact_hint.txt");
            $decode = json_decode($file);
                echo htmlspecialchars($decode->hint);
            if ($decode->finder_name == "") {
                echo "<i><b><br /> The Artifact has not been found yet!</i>";
            } else {
                echo "<br /> The first person to find this artifact was " . htmlspecialchars($decode->finder_name) . "!!</i>";
            }
            ?>

            </center>

        </div>

        <br />
			
		<div class="content">			
			<div class="game_holder">
				<embed width="550" height="400" src="//cdn.jiggmin.com/games/platform-racing-2/platform-racing-2-loader-v13.swf" type="application/x-shockwave-flash"></embed>
			</div>
		</div>
		
	</div>
	
	<div id="footer">
		<ul class="footer_links">
			<li><a href="//pr2hub.com/backups" target="_blank">Backups</a></li>
			<li><a href="https://jiggmin2.com/forums/showthread.php?tid=19" target="_blank">Folding@Home</a></li>
			<li><a href="https://jiggmin2.com" target="_blank">Jiggmin2.com</a></li>
			<li><a href="https://jiggmin2.com/forums/showthread.php?tid=385" target="_blank">Rules</a></li>
		</ul>
	</div>
	
</div>

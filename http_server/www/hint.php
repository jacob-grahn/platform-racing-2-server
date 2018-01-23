<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Platform Racing 2 Hint</title>
    <link href="hint.css" rel="stylesheet" type="text/css"/>
</head>

<body>
	
<div id="container">
	<div id="body">
		<div id="artifact">
			<center>
				<img src="img/artifact_hint.png"></img>
				<br/>
				<img src="img/artifact.png" width="80px" height="70px"></img>
		<br/>
                <?php
		try {
                    $file = file_get_contents("https://pr2hub.com/files/artifact_hint.txt");
                    $decode = json_decode($file);
                    echo htmlspecialchars($decode->hint) . "<br/>";
                    if ($decode->finder_name == "") {
                        echo "<i><b><br /> The Artifact has not been found yet!</i>";
                    }
		else
		{
                        echo "<br /> The first person to find this artifact was " . htmlspecialchars($decode->finder_name) . "!!</i>";
                    }
		}
		catch (Exception $msg){
			$error_message = htmlspecialchars("Error: " . $msg->getMessage());
			echo $error_message;	
		}
                ?>
            </center>
        </div>
    </div>
</body>

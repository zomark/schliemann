<?php
	header('Content-Type: text/html');
	require 'authenticate.php';
	require 'dbpdo.php';
	$user = sc_get_user($_SERVER);
	if(!$user) {
		die("Authentication failed");
	}
	try {
		$db = new MyDB();
		if(!$db){
			echo "<p>".$db->lastErrorMsg()."</p>";
		} 
		$sqlMedia = "select name, type, items_id "
			."from media "
			."where items_id = ".$_GET["id"];
		$resultMedia = $db->query($sqlMedia);
	}
	catch(Exception $e) {
		echo "<p>Error:".$e->getMessage()."</p>";
	}
?>

<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>The Schliemann Correspondence</title>
	<!-- Banner font -->
	<link href='http://fonts.googleapis.com/css?family=Marcellus+SC' rel='stylesheet' type='text/css'/>
	<!-- jqueryui styles -->
	<link href="css/custom-theme/jquery-ui-1.10.4.custom.min.css" rel="stylesheet"/>
	<!-- zoom styles -->
	<link rel="stylesheet" type="text/css" href="css/jquery.jqzoom.css">
	<!--link href="css/zoomple.css" rel="stylesheet"/-->
	<!-- ASCSA styles -->
	<link href="http://www.ascsa.edu.gr/index.php?css=ascsa/site_css.v.1387875407" media="all" type="text/css" rel="stylesheet"/>
	<!-- Local styles and overwrites -->
	<link href="css/main.css" rel="stylesheet"/>
	<link href="css/item.css" rel="stylesheet"/>
	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<link rel="icon" type="image/x-icon" href="favicon.ico" />
	<!-- jquery and jqueryui scripts -->
	<script src="js/jquery-1.10.2.js"></script>
	<script src="js/jquery-ui-1.10.4.custom.min.js"></script>
	<script src="js/jquery.tablesorter.min.js"></script>
	<!-- gallery scripts -->
	<script src="js/jquery.galleriffic.js"></script>
	<script src="js/jquery.opacityrollover.js"></script>
	<!-- zoom scripts -->
	<!--script type="text/javascript" src="jquery.jqzoom-core.js"></script-->
	<!--script src="js/zoomple.js"></script-->
	<!-- Local script, initializes and the jquery components and handles events -->
	<script src="js/schliemann-item.js"></script>
</head>
<body>
	<div class="container">
		<!-- Banner -->
		<div class="top">
			<a href="http://www.ascsa.edu.gr/">
				<img width="464" height="100" border="0" style="float:left;" 
					alt="The American School of Classical Studies at Athens" 
					src="http://www.ascsa.edu.gr/html/images/logo.jpg">
			</a>
			<div class="toplink">
				<a href="">Heinrich Schliemann's correspondence</a>
			</div>
			<!-- Dummy content to mimick ascsa pages -->
			<ul class="tactical"></ul>
			<ul id="nav" class="navigation">
				<li><a href="http://www.ascsa.edu.gr/index.php/archives/heinrich-schliemann-finding-aid/">ACSCA Finding Aids</a>
				<li><a href="doc/schliemann_correspondence_doc.pdf">/&nbsp;Project Documentation</a>
				<li>/&nbsp;Welcome, <span id="username"><?php echo $user;?></span></li>
			</ul>
			<ul class="gateway"></ul>
		</div>
		
		<!-- Main contents -->
		<div class="middle">
			<h1><?php echo $_GET["id"];?></h1>
			<p>[Under construction]</p>
			<div id="gallery" class="gallery-content">
				<div id="controls" class="controls"></div>
				<div class="slideshow-container">
					<div id="loading" class="loader"></div>
					<div id="slideshow" class="slideshow"></div>
				</div>
				<div id="caption" class="caption-container"></div>
			</div>
			<div id="thumbs" class="gallery-navigation">
				<ul class="gallery-thumbs noscript">
<?php
	if($resultMedia) {
		$pagenum = 0;
		foreach($resultMedia as $row) {
			$name = $row[0];
			$pagenum++;
			//Derive subfolder from filename
			$subfolder = implode("-", array_slice(explode("-", $name), 0, -1));
			//Filename
			$pathThumb = "media/thumbnails/$subfolder/$name.jpg";
			//Hires filename
			$pathHires = "media/hires/$subfolder/$name.jpg";
			//Write li element
			echo "<li>";
			echo "<a class=\"gallery-thumb\" name=\"$name\" href=\"$pathHires\" title=\"$pagenum\">";
			echo "<img src=\"$pathThumb\" alt=\"$name\"/>";
			echo "</a>";
			echo "<div class=\"gallery-caption\">";
			echo "<div class=\"image-title\">$pagenum</div>";
			echo "</div>";
			echo "</li>";
		}
		$resultMedia->closeCursor();
	}
?>
				</ul>
			</div>
		</div>
		<div class="footer">
		</div>
	</div>
</body>
</html>


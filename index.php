<?php
require 'authenticate.php';
$user = sc_get_user($_SERVER);
if(!$user) {
	die("Authentication failed");
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
	<!-- ASCSA styles -->
	<link href="http://www.ascsa.edu.gr/index.php?css=ascsa/site_css.v.1387875407" media="all" type="text/css" rel="stylesheet"/>
	<!-- Local styles and overwrites -->
	<link href="css/main.css" rel="stylesheet"/>
	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<link rel="icon" type="image/x-icon" href="favicon.ico" />
	<!-- jquery and jqueryui scripts -->
	<script src="js/jquery-1.10.2.js"></script>
	<script src="js/jquery-ui-1.10.4.custom.min.js"></script>
	<script src="js/jquery.tablesorter.min.js"></script>
	<!-- Local script, initializes and the jquery components and handles events -->
	<script src="js/schliemann.js"></script>
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
			
			<div class="about-bg">
				<h1>Heinrich Schliemann's correspondence</h1>

				<!-- Input controls -->
				<div class="query">
					<!-- Date range -->
					<div id="section-dates" class="section dates">
						<button id="reset-dates" class="button-reset">Reset</button>
						<div class="section-contents">
							<div class="date date_from">
								<h3>Written between...</h3>
								<input type="text" name ="date_from" id="date_from"/>
								<button id="show_from">Show calendar</button>
								<button id="reset_from">Reset</button>
								<button id="copy_from">Set as end date</button>
							</div>
							<div class="date date_to">
								<h3>...and:</h3>
								<input type="text" name ="date_to" id="date_to"/>
								<button id="show_to">Show calendar</button>
								<button id="reset_to">Reset</button>
								<button id="copy_to">Set as start date</button>
							</div>
							<div style="clear: both;"></div>
							<div id="slider_container">
								<div id="slider_dates"></div>
							</div>
						</div>
						<div style="clear: both;"></div>
					</div>

					<!-- Correspondent -->
					<div id="section-correspondent" class="section">
						<button id="reset-dates" class="button-reset">Reset</button>
						<div class="section-contents">
							<h3>Correspondent:</h3>
							<div class="option">
								<input type="text" name ="partner" id="partner"/>
								<button id="all_correspondents">Show all</button>
								<button id="reset_correspondents">Reset</button>
							</div>
							<div class="inout">
								<div id="inout_buttons">
									<input type="radio" name="letterType" id="letterType_out" value="out"/>
									<label for="letterType_out">outgoing</label>
									<input type="radio" name="letterType" id="letterType_in" value="in"/>
									<label for="letterType_in">incoming</label>
									<input type="radio" name="letterType" id="letterType_both" value="both" checked="checked"/>
									<label for="letterType_both">both</label>
								</div>
								<div class="location">
									<div class="option" id="copybook">
										<label id="label_copybook" for="num_copybook">Copybook:</label>
										<select name="copybook" id="num_copybook" size="1">
										</select>
									</div>
									<div class="option" id="box">
										<label id="label_box" for="num_box">Box:</label>
										<select name="box" id="num_box" size="1">
										</select>
									</div>
								</div>
							</div>
						</div>
						<div style="clear: both;"></div>
					</div>

					<!-- Place -->
					<div id="section-place" class="section">
						<button id="reset-dates" class="button-reset">Reset</button>
						<div class="section-contents">
							<h3>Place:</h3>
							<button id="reset-places" class="button-reset">Reset</button>
							<div class="option">
								<input type="text" name ="place" id="place"/>
								<button id="all_places">Show all</button>
								<button id="reset_places">Reset</button>
							</div>
						</div>
						<div style="clear: both;"></div>
					</div>

					<!-- Options -->
					<div id="section-options" class="section">
						<button id="reset-dates" class="button-reset">Reset</button>
						<div class="section-contents">
							<h3>Options:</h3>
							<input type="checkbox" name="check_digitized" id="check_digitized"/>
							<label id="label_digitized" for="check_digitized">Available online</label>
						</div>
						<div style="clear: both;"></div>
					</div>

				</div>
				
				<!-- Context-sensitive help texts -->
				<div class="help-area">
					<div class="help" id="help-dates">
						<p>Select the date range you are interested in. Possible correspondent and place 
						choices will be constrained accordingly. The <button class="button_reset">&#160;</button> button will clear the 
						parameter. The <button class="button_right">&#160;</button> and <button class="button_left">&#160;</button> 
						buttons will copy the currently selected date from one field to the other.</p>
					</div>
					<div class="help" id="help-correspondant">
						<p>Enter the first letters of the correspondent's surname. A list of possible names
						will pop up, constrained by the other search criteria you defined. The asterisk (*) wildcard is supported.
						Click the <button class="button_down">&#160;</button> button to display a list of all possible choices. 
						The <button class="button_reset">&#160;</button> button will clear the 
						parameter.
						</p>
					</div>
					<div class="help" id="help-place">
						<p>Enter the first letters of the letter's origin (e.g. &quot;Athens&quot;).
						A list of valid names will pop up, constrained by the other search criteria you defined.
						The asterisk (*) wildcard is supported. Click the <button class="button_down">&#160;</button> button to display a list of all possible choices. 
						The <button class="button_reset">&#160;</button> button will clear the 
						parameter.</p>
					</div>
					<div class="help" id="help-media">
						<p>Selecting this option will display only items with digitized material available online on this web page
						(click the result table's ID column to access these materials). 
						This does not reflect the availability of scanned letters and copybooks on the Gennadios site which
						concerns a much larger range of items.
						</p>
					</div>
				</div>
				<div style="clear: both;"></div>
				
				<!-- Results section -->
				<div class="section">
					<a href="javascript:void" id="query_items">Display results</a>
					<a href="#" id="download_items">Download</a>
					<!-- AJAX activity indicator -->
					<div id="loading_container">&#160;
						<div id="loading">&#160;</div>
					</div>
					<!-- Container for search results -->
					<div class="results" id="results"/>
					<div style="clear: both;"></div>
				</div>
			</div>
		</div>
		<div class="footer">
		</div>
	</div>
</body>
</html>


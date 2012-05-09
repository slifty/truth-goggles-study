<?PHP
	
	// Check to make sure the browser is supported (isn't Internet Explorer)
	if(!isset($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== false) {
		header('Location: unsupported_browser.php');
		exit();
	}
	
	
	// Set a cookie so we can be sure the user viewed this information
	session_start();
	$_SESSION['landing'] = true;
?>

<html>
	<head>
		<title>Truth Goggles User Study</title>
		<link rel="stylesheet" href="styles/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
		<link rel="stylesheet" href="styles/index.css" type="text/css" media="screen" title="no title" charset="utf-8">
		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-29964553-2']);
			_gaq.push(['_trackPageview']);
			(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
	</head>
	<body>
		<div id="content">
			<h1>Welcome to the Truth Goggles user study</h1>
			<p>Truth Goggles is a credibility layer for the Internet being developed at the MIT Media Lab as part of a master's thesis by Dan Schultz.  It attempts to connect the dots between content on your screen and the work done by fact checking organizations.  The tool is designed to guide users through the process of critical media consumption by identifying moments where it is especially important to think carefully.</p>
			<p>In order to increase the effectiveness of Truth Goggles the researchers would like to better understand how you would use it, how well it works, and what could be improved.</p>
			<p><strong>Please take a moment to read the following important points:</strong></p>
			<ul>
				<li>Participation is voluntary</li>
				<li>You may decline to answer any or all questions</li>
				<li>This study will take approximately <strong>30 minutes</strong> of your time</li>
				<li>You may stop participating at any point without any adverse consequences</li>
				<li>Your confidentiality is assured</li>
			</ul>
			<p>If you continue you will be asked a few questions and shown a series of articles.  At the end of the study you will be asked a short set of questions about your overall experience.  Some of these questions will be open ended.  Participants are asked to avoid providing personally identifiable information in their responses.</p>
			
			<div id="continue"><a href="study.php">Continue to the study *</a></div>
			<p>*<em>by clicking this link you understand that participation is voluntary, you may decline to answer any or all questions, you may stop participating at any point without any adverse consequences, and that your confidentiality is assured.<em></p>
	</body>
</html>
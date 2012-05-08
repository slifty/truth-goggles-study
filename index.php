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
	</head>
	<body>
		<div>
			<p>Truth Goggles is a credibility layer for the Internet being developed at the MIT Media Lab as part of a master's thesis by Dan Schultz.  It attempts to connect the dots between content on your screen and the work done by fact checking organizations.  The tool is designed to guide users through the process of critical media consumption by identifying moments where it is especially important to think carefully.</p>
			<p>In order to increase the effectiveness of Truth Goggles the researchers would like to better understand how you would use it, how well it works, and what could be improved.</p>
			<p>Please take a moment to read the following important points:</p>
			<ul>
				<li>Participation is voluntary</li>
				<li>You may decline to answer any or all questions</li>
				<li>This study will take approximately 20 minutes or less of your time</li>
				<li>You may stop participating at any point without any adverse consequences</li>
				<li>Your confidentiality is assured</li>
			</ul>
			<p>If you decide to continue, you will be asked a few questions and shown a series of articles and blog posts.  After reading an article you may be asked a short set of questions about your experience.  At the end of the study you may be asked a short set of questions about your overall experience.  Some of these questions may be open ended.  Participants are asked to avoid providing personally identifiable information in their responses.</p>
			<p><a href="study.php">Link to the study</a>*</p>
			<p>*<em>by clicking this link you understand that participation is voluntary, you may decline to answer any or all questions, you may stop participating at any point without any adverse consequences, and that your confidentiality is assured.<em></p>
	</body>
</html>
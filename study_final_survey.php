<?PHP
	session_start();
	
	
?>

<html>
	<head>
		<title>Truth Goggles User Study</title>
		<link rel="stylesheet" href="styles/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
		<link rel="stylesheet" href="styles/final_survey.css" type="text/css" media="screen" title="no title" charset="utf-8">
	</head>
	<body>
		<div id="content">
			<h1>Exit Survey</h1>
			<p>Thank you for participating!  Below are some final questions about your experience.</p>
			<p>Please share your opinions as desired, but be sure to avoid providing personally identifiable information.</p>
			<form action="study.php" method="POST">
				<ul>
					<li>
						<label for="q_trust">Did Truth Goggles affect your trust in the content you were reading?  Please explain.</label>
						<textarea id="q_trust" name="q_trust"></textarea>
					</li>
					<li>
						<label for="q_politics">Where would you place yourself on the political spectrum?</label>
						<select id="q_politics" name="q_politics">
							<option value="0"></option>
							<option value="1">Strong conservative</option>
							<option value="2">Moderate conservative</option>
							<option value="3">Independent</option>
							<option value="4">Moderate liberal</option>
							<option value="5">Strong liberal</option>
						</select>
					</li>
					<li>
						<label for="q_experience">How was your reading experience different when Truth Goggles was enabled?</label>
						<textarea id="q_experience" name="q_experience"></textarea>
					</li>
					<li>
						<label for="q_comments">Do you have any additional comments?</label>
						<textarea id="q_comments" name="q_comments"></textarea>
					</li>
					<li>
						<input type="submit" value="Submit" />
					</li>
				</ul>
			</form>
		</div>
	</body>
</html>
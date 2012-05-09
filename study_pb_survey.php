<?PHP
	include("models/Participant.php");
	include("models/Article.php");
	include("models/Claim.php");
	
	session_start();
	$progress = $_SESSION['stage_progress'] - 1;
	$participant = Participant::getObject($_SESSION['participant_id']);
	$claim_order = explode(",",$participant->getClaimOrder1());
	$claim = Claim::getObject($claim_order[$progress]);

?>
<html>
	<head>
		<title>Truth Goggles User Study</title>
		<link rel="stylesheet" href="styles/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
		<link rel="stylesheet" href="styles/belief_survey.css" type="text/css" media="screen" title="no title" charset="utf-8">
		<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="scripts/pietimer.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript">
			$(function() {
				$('#timer').pietimer({
						seconds: 20,
						color: '#cc9999'
					},
					function() {
						$("#skip a").click();
						window.location="study.php?r=time";
					}
				);
				$('#timer').pietimer('start');
			});
		</script>
	</head>
	<body>
		<div id="skip"><a href="study.php">Skip</a></div>
		<div id="content">
			<div id="timer"></div>
			<div id="claim">
				<h1><?PHP echo($claim->getContent()); ?></h1>
			</div>
			<div id="question">
				<h2>On a truth scale of 1-5, where would you rank this statement?</h2>
				<ul id="responses">
					<li><a href="study.php?r=1">1 (False)</a></li>
					<li><a href="study.php?r=2">2</a></li>
					<li><a href="study.php?r=3">3</a></li>
					<li><a href="study.php?r=4">4</a></li>
					<li><a href="study.php?r=5">5 (True)</a></li>
				</ul>
			</div>
		</div>
	</body>
</html>
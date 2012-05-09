<?PHP
	include("conf.php");
	include("models/Participant.php");
	include("models/Article.php");
	include("models/Claim.php");
	
	session_start();
	$progress = $_SESSION['stage_progress'] - 1;
	$participant = Participant::getObject($_SESSION['participant_id']);
	$article_order = explode(",",$participant->getArticleOrder());
	$article = Article::getObject($article_order[$progress]);
	
	$treatment_order = explode(",",$participant->getTreatmentOrder());
	$treatment = $treatment_order[$progress];
?>

<html>
	<head>
		<title>Truth Goggles User Study</title>
		<link rel="stylesheet" href="styles/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
		<link rel="stylesheet" href="styles/articles.css" type="text/css" media="screen" title="no title" charset="utf-8">
	</head>
	<body>
		<div id="skip"><a href="study.php">Skip</a></div>
		<div id="content">
			<?PHP echo($article->getContent()); ?>
			<div id="continue" class="goggles-ignore"><a href="study.php">Continue</a></div>
		</div>
		<?PHP
			global $TRUTH_GOGGLES_CLIENT;
			// Apply the appropriate treatment
			switch($treatment) {
				case 'none':
					break;
				case 'goggles':
					?>
						<script type="text/javascript">
							var goggles_domain='<?PHP echo($TRUTH_GOGGLES_CLIENT);?>';
							var s=document.createElement('script');
							var goggles_prefs = {
								mode_toggle: false,
								default_mode: "goggles",
								track_api: "http://<?PHP echo($_SERVER['HTTP_HOST'].$SITE_ROOT) ?>/study_tracker.php"
							};
							s.type='text/javascript';
							document.body.appendChild(s);
							s.src=goggles_domain+'/goggles_bookmarklet.min.js';
						</script>
					<?PHP
					break;
				case 'highlight':
					?>
						<script type="text/javascript">
							var goggles_domain='<?PHP echo($TRUTH_GOGGLES_CLIENT);?>';
							var s=document.createElement('script');
							var goggles_prefs = {
								mode_toggle: false,
								default_mode: "highlight",
								track_api: "http://<?PHP echo($_SERVER['HTTP_HOST'].$SITE_ROOT) ?>/study_tracker.php"
							};
							s.type='text/javascript';
							document.body.appendChild(s);
							s.src=goggles_domain+'/goggles_bookmarklet.min.js';
						</script>
					<?PHP
					break;
				case 'safe':
					?>
						<script type="text/javascript">
							var goggles_domain='<?PHP echo($TRUTH_GOGGLES_CLIENT);?>';
							var s=document.createElement('script');
							var goggles_prefs = {
								mode_toggle: false,
								default_mode: "safe",
								track_api: "http://<?PHP echo($_SERVER['HTTP_HOST'].$SITE_ROOT) ?>/study_tracker.php"
							};
							s.type='text/javascript';
							document.body.appendChild(s);
							s.src=goggles_domain+'/goggles_bookmarklet.min.js';
						</script>
					<?PHP
					break;
				case 'pick':
					break;
			}
		?>
	</body>
</html>
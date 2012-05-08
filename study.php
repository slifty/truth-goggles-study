<?PHP
	include("models/Participant.php");
	include("models/Article.php");
	
	// This script runs the study.
	// If this is a new user, the study is generated
	// If this is an existing user, their position in the study is calculated and they are redirected to the appropriate page.
	
	session_start();
	
	// Step 1: Make sure the user has viewed the landing page	
	if(!isset($_SESSION['landing']) || $_SESSION['landing'] == false) {
		// The user has not viewed the landing page yet
		header('Location: index.php');
		exit();
	}
	
	// Step 2: Figure out which phase of the study the user is in
	$_SESSION['stage'] = isset($_SESSION['stage'])?$_SESSION['stage']:0;
	$_SESSION['stage_progress'] = isset($_SESSION['stage_progress'])?$_SESSION['stage_progress']:0;
	
	$participant = isset($_SESSION['participant_id'])?Participant::getObject($_SESSION['participant_id']):new Participant();
	
	switch($_SESSION['stage']) {
		case 0: // Initial Instructions
			
			// Generate the study order
			$articles = Article::getAllObjects();
			$article_order = array();
			shuffle($articles);
			foreach($articles as $article)
				array_push($article_order, $article->getItemID());
			
			$treatments = array(0,1,2);
			$treatment_order = array();
			shuffle($treatments);
			foreach($treatments as $index => $treatment) {
				$treatment_order[2 * $index] = $treatment;
				$treatment_order[2 * $index + 1] = $treatment;
			}
			
			// Create the participant
			$participant->setClient($_SERVER['HTTP_USER_AGENT']);
			$participant->setArticleOrder(implode(",",$article_order));
			$participant->setTreatmentOrder(implode(",",$treatment_order));
			
			
			// Move to the next stage
			//$_SESSION['stage'] = 1;
			break;
			
		case 1: // Prior Belief Survey
			
			break;
			
		case 2: // Articles
			break;
			
		case 3: // Post Article Survey
			break;
			
		case 4: // Final Survey
			break;
			
		default:
			break;
	}
	
	$participant->setStage($_SESSION['stage']);
	$participant->setStageProgress($_SESSION['stage_progress']);
	$participant->save();
	$_SESSION['participant_id'] = $participant->getItemID();
	
?>
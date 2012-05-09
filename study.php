<?PHP
	require_once("models/Participant.php");
	require_once("models/Response.php");
	require_once("models/Event.php");
	require_once("models/Article.php");
	require_once("models/Claim.php");
	
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
			
			$claims = Claim::getAllObjects();
			$claim_order_1 = array();
			$claim_order_2 = array();
			
			shuffle($claims);
			foreach($claims as $claim)
				array_push($claim_order_1, $claim->getItemID());
			
			shuffle($claims);
			foreach($claims as $claim)
				array_push($claim_order_2, $claim->getItemID());
			
			$treatments = array('safe','highlight','goggles');
			$treatment_order = array();
			shuffle($treatments);
			foreach($treatments as $index => $treatment) {
				$treatment_order[2 * $index] = $treatment;
				$treatment_order[2 * $index + 1] = $treatment;
			}
			array_unshift($treatment_order, 'none');
			array_unshift($treatment_order, 'none');
			array_push($treatment_order, 'pick');
			array_push($treatment_order, 'pick');
			
			// Create the participant
			$participant->setClient($_SERVER['HTTP_USER_AGENT']);
			$participant->setArticleOrder(implode(",",$article_order));
			$participant->setTreatmentOrder(implode(",",$treatment_order));
			$participant->setClaimOrder1(implode(",",$claim_order_1));
			$participant->setClaimOrder2(implode(",",$claim_order_2));
			
			
			// Move to the next stage
			header('Location: study_initial_instructions.php');
			$_SESSION['stage'] = 1;
			break;
			
		case 1: // Prior Belief Survey Instructions
			$_SESSION['stage'] = 2;
			$_SESSION['stage_progress'] = 0;
			header('Location: study_pb_instructions.php');
			break;
			
		case 2: // Prior Belief Survey
			if($_SESSION['stage_progress'] > 0) {
				// Log a response
				$response = new Response();
				$response->setParticipantID($participant->getItemID());
				$response->setQuestionID($_SESSION['stage']."_".$_SESSION['stage_progress']);
				$response->setContent(isset($_GET['r'])?$_GET['r']:"skip");
				$response->save();
			}
			
			$_SESSION['stage_progress']++;
			$claims = Claim::getAllObjects();
			if(sizeof($claims) < $_SESSION['stage_progress']) {
				$_SESSION['stage'] = 3;
				$_SESSION['stage_progress'] = 0;
				header('Location: study.php');
				exit();
			}
			
			header('Location: study_pb_survey.php');
			break;
			
		case 3: // Articles Instructions
			$_SESSION['stage'] = 4;
			$_SESSION['stage_progress'] = 0;
			header('Location: study_article_instructions.php');
			break;
		
		case 4: // Articles
			if(isset($_GET['q_interface'])) {
				$participant->setTreatmentOrder(
					preg_replace('/pick/',$_GET['q_interface'],$participant->getTreatmentOrder())
				);
				$participant->save();
				
				$response = new Response();
				$response->setParticipantID($participant->getItemID());
				$response->setQuestionID('q_interface');
				$response->setContent($_POST['q_interface']);
				$response->save();
			}
			
			$_SESSION['stage_progress']++;
			$articles = Article::getAllObjects();
			if(sizeof($articles) < $_SESSION['stage_progress']) {
				$_SESSION['stage'] = 5;
				$_SESSION['stage_progress'] = 0;
				header('Location: study.php');
				exit();
			}
			
			$treatment_order = explode(",",$participant->getTreatmentOrder());
			$treatment = $treatment_order[$_SESSION['stage_progress'] - 1];
			
			if($treatment == "pick") {
				$_SESSION['stage_progress']--;
				header('Location: study_article_select.php');
			} else {
				header('Location: study_article.php');
			}
			break;
			
		case 5: // Post Article Survey Instructions
			$_SESSION['stage'] = 6;
			$_SESSION['stage_progress'] = 0;
			header('Location: study_pa_instructions.php');
			break;

		case 6: // Post Article Survey
			if($_SESSION['stage_progress'] > 0) {
				// Log a response
				$response = new Response();
				$response->setParticipantID($participant->getItemID());
				$response->setQuestionID($_SESSION['stage']."_".$_SESSION['stage_progress']);
				$response->setContent(isset($_GET['r'])?$_GET['r']:"skip");
				$response->save();
			}
			
			$_SESSION['stage_progress']++;
			$claims = Claim::getAllObjects();
			if(sizeof($claims) < $_SESSION['stage_progress']) {
				$_SESSION['stage'] = 7;
				$_SESSION['stage_progress'] = 0;
				header('Location: study.php');
				exit();
			}
			
			header('Location: study_pa_survey.php');
			break;
			
		case 7: // Final Survey
			$_SESSION['stage'] = 8;
			header('Location: study_final_survey.php');
			break;
			
		case 8: // Thanks
			if(isset($_POST['q_trust'])) {
				$response = new Response();
				$response->setParticipantID($participant->getItemID());
				$response->setQuestionID('q_trust');
				$response->setContent($_POST['q_trust']);
				$response->save();
			}
			if(isset($_POST['q_politics'])) {
				$response = new Response();
				$response->setParticipantID($participant->getItemID());
				$response->setQuestionID('q_politics');
				$response->setContent($_POST['q_politics']);
				$response->save();
			}
			if(isset($_POST['q_experience'])) {
				$response = new Response();
				$response->setParticipantID($participant->getItemID());
				$response->setQuestionID('q_experience');
				$response->setContent($_POST['q_experience']);
				$response->save();
			}
			if(isset($_POST['q_comments'])) {
				$response = new Response();
				$response->setParticipantID($participant->getItemID());
				$response->setQuestionID('q_comments');
				$response->setContent($_POST['q_comments']);
				$response->save();
			}
			
			
			header('Location: study_thanks.php');
			break;

		default:
			break;
	}
	
	$participant->setStage($_SESSION['stage']);
	$participant->setStageProgress($_SESSION['stage_progress']);
	$participant->save();
	$_SESSION['participant_id'] = $participant->getItemID();
	
	$event = new Event();
	$event->setParticipantID($participant->getItemID());
	$event->setType("progress");
	$event->setData($_SESSION['stage']."_".$_SESSION['stage_progress']);
	$event->save();
	
?>
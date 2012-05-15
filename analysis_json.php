<?PHP
	require_once("models/Participant.php");
	require_once("models/Response.php");
	require_once("models/Event.php");
	require_once("models/Article.php");
	require_once("models/Claim.php");
	
	$participants = Participant::getAllObjects();
	$participant_blacklist = array(50, 89); // The responses that are known to be invalid
	$participant_cutoff = 224; // The id of the final participant
	$claim_blacklist = array(14); // The claims that are known to be invalid
	$claim_objects = Claim::getAllObjects();
	$article_objects = Article::getAllObjects();
	
	$articles = array();
	foreach($article_objects as $article) {
		$articles[$article->getItemID()] = $article;
	}
	
	?>
{
 "total": <?PHP echo(sizeof($participants)); ?>,
 "omitted": <?PHP echo(sizeof($participant_blacklist)); ?>,
 "participants": [
	<?PHP
	$participant_responses = array();
	$participant_counter = 0;
	foreach($participants as $participant) {
		if(in_array($participant->getItemID(), $participant_blacklist))
			continue;
		if($participant->getItemID() > $participant_cutoff)
			continue;
			
		echo((++$participant_counter>1)?",":"");
		
		$events = Event::getObjectsByParticipantID($participant->getItemID());
		$treatment_order = explode(',', $participant->getTreatmentOrder());
		$article_order = explode(',', $participant->getArticleOrder());
		$claim_order_1 = explode(',', $participant->getClaimOrder1());
		$claim_order_2 = explode(',', $participant->getClaimOrder2());
		$claim_results = array();
		$responses = array();
		$more_count = 0;
		
		foreach(Response::getObjectsByParticipantID($participant->getItemID()) as $response)
			$responses[$response->getQuestionID()] = $response;
		
		$participant_responses[$participant->getItemID()] = $responses;
		
		foreach($claim_objects as $claim) {
			if(in_array($claim->getItemID(), $claim_blacklist))
				continue;
			$claims[$claim->getItemID()] = array(
				"claim" => $claim,
				"response_1" => "",
				"response_2" => "",
				"treatment" => ""
			);
		}
		
		foreach($claim_order_1 as $x => $claim_id) {
			if(in_array($claim_id, $claim_blacklist))
				continue;
			$claims[$claim_id]['response_1'] = isset($responses['2_'.($x + 1)])?$responses['2_'.($x + 1)]->getContent():"";
		}
		
		foreach($claim_order_2 as $x => $claim_id) {
			if(in_array($claim_id, $claim_blacklist))
				continue;
			$claims[$claim_id]['response_2'] = isset($responses['6_'.($x + 1)])?$responses['6_'.($x + 1)]->getContent():"";
		}
		
		foreach($claims as $claim_id => $claim) {
			if($claim["claim"]->getArticleID() != 0) {
				$article = $articles[$claim["claim"]->getArticleID()];
				$claims[$claim_id]['treatment'] = $treatment_order[array_search($article->getItemID(),$article_order)];
			} else {
				$claims[$claim_id]['treatment'] = "control";
			}
		}
		
		foreach($events as $event)
			if($event->getType() == "more")
				$more_count++;
		?>
  {
   "participant_id": <?PHP echo(DBConn::clean($participant->getItemID())); ?>,
   "ideology": <?PHP echo(DBConn::clean(isset($participant_responses[$participant->getItemID()]["q_politics"])?$participant_responses[$participant->getItemID()]["q_politics"]->getContent():0));?>,
   "stage": <?PHP echo(DBConn::clean($participant->getStage())); ?>,
   "stage_progress": <?PHP echo(DBConn::clean($participant->getStageProgress())); ?>,
   "claims": [
<?PHP
					$claim_counter = 0;
					foreach($claims as $claim) {
						echo((++$claim_counter>1)?",":"");
						?>
    {
     "claim":<?PHP echo($claim["claim"]->toJSON()); ?>,
     "response_1":<?PHP echo(DBConn::clean($claim["response_1"])); ?>,
     "response_2":<?PHP echo(DBConn::clean($claim["response_2"])); ?>,
     "treatment":<?PHP echo(DBConn::clean($claim["treatment"])); ?>
    }
<?PHP
					}
				?>
   ],
   "more_count":<?PHP echo(DBConn::clean($more_count));?>
  }
		<?PHP
	}
	?>
 ]
}
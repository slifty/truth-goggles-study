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
	
	$participant_responses = array();
	$participant_counter = 0;
	
	$d = "";
	$d .= "uid,";
	$d .= "treatment,";
	$d .= "claimVerdict,";
	$d .= "preRating,";
	$d .= "postRating,";
	$d .= "inaccuracySaturationPre,";
	$d .= "inaccuracySaturationPost,";
	$d .= "inaccuracyDistancePre,";
	$d .= "inaccuracyDistancePost,";
	$d .= "inaccuracyValuePre,";
	$d .= "inaccuracyValuePost,";
	$d .= "biasSaturationPre,";
	$d .= "biasSaturationPost,";
	$d .= "isOverPre,";
	$d .= "isOverPost,";
	$d .= "isOverPossible,";
	$d .= "isUnderPre,";
	$d .= "isUnderPost,";
	$d .= "isUnderPossible,";
	$d .= "isExactPre,";
	$d .= "isExactPost,";
	$d .= "drift,";
	$d .= "absoluteDrift,";
	$d .= "isBackfire,";
	$d .= "isBackfirePossible,";
	$d .= "isIntended,";
	$d .= "\n";
	
	foreach($participants as $participant) {
		if(in_array($participant->getItemID(), $participant_blacklist))
			continue;
		if($participant->getItemID() > $participant_cutoff)
			continue;
		if($participant->getStage() != 8)
			continue;
		
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
	
		foreach($claims as $claim) {
			if($claim['response_1'] == 0 || $claim['response_2'] == 0)
				continue
			
			$inaccuracy_saturation_pre = "";
			if($claim['response_1'] != 0) {
				$num = abs($claim['response_1'] - $claim['claim']->getVerdict());
				$denom = max(abs($claim['claim']->getVerdict() - 1),abs($claim['claim']->getVerdict() - 5));
				$inaccuracy_saturation_pre = $num / $denom;
			}
			
			$inaccuracy_saturation_post = "";
			if($claim['response_2'] != 0) {
				$num = abs($claim['response_2'] - $claim['claim']->getVerdict());
				$denom = max(abs($claim['claim']->getVerdict() - 1),abs($claim['claim']->getVerdict() - 5));
				$inaccuracy_saturation_post = $num / $denom;
			}
			
			$inaccuracy_distance_pre = "";
			if($claim['response_1'] != 0)
				$inaccuracy_distance_pre = abs((int)$claim['response_1'] - (int)$claim['claim']->getVerdict());
			
			$inaccuracy_distance_post = "";
			if($claim['response_2'] != 0)
				$inaccuracy_distance_post = abs((int)$claim['response_2'] - (int)$claim['claim']->getVerdict());
			
			$inaccuracy_value_pre = "";
			if($claim['response_1'] != 0)
				$inaccuracy_value_pre = (int)$claim['response_1'] - (int)$claim['claim']->getVerdict();
			
			$inaccuracy_value_post = "";
			if($claim['response_2'] != 0)
				$inaccuracy_value_post = (int)$claim['response_2'] - (int)$claim['claim']->getVerdict();
			
			$bias_saturation_pre = "";
			if($claim['response_1'] != 0) {
				if((int)$claim['claim']->getVerdict() > (int)$claim['response_1'])
					$bias_saturation_pre = abs((int)$claim['response_1'] - (int)$claim['claim']->getVerdict()) / abs((int)$claim['claim']->getVerdict() - 1);
				if((int)$claim['claim']->getVerdict() < (int)$claim['response_1'])
					$bias_saturation_pre = abs((int)$claim['response_1'] - (int)$claim['claim']->getVerdict()) / abs((int)$claim['claim']->getVerdict() - 5);
			}
			
			$bias_saturation_post = "";
			if($claim['response_2'] != 0) {
				if((int)$claim['claim']->getVerdict() > (int)$claim['response_2'])
					$bias_saturation_post = abs((int)$claim['response_2'] - (int)$claim['claim']->getVerdict()) / abs((int)$claim['claim']->getVerdict() - 1);
				if((int)$claim['claim']->getVerdict() < (int)$claim['response_2'])
					$bias_saturation_post = abs((int)$claim['response_2'] - (int)$claim['claim']->getVerdict()) / abs((int)$claim['claim']->getVerdict() - 5);
			}
			
			$is_over_pre = "";
			if($claim['response_1'] != 0)
				$is_over_pre = ((int)$claim['response_1'] > (int)$claim['claim']->getVerdict())?1:0;
			
			$is_over_post = "";
			if($claim['response_2'] != 0)
				$is_over_post = ((int)$claim['response_2'] > (int)$claim['claim']->getVerdict())?1:0;
			
			$is_over_possible = ((int)$claim['claim']->getVerdict() < 5)?1:0;
			
			$is_under_pre = "";
			if($claim['response_1'] != 0)
				$is_under_pre = ((int)$claim['response_1'] < (int)$claim['claim']->getVerdict())?1:0;
			
			$is_under_post = "";
			if($claim['response_2'] != 0)
				$is_under_post = ((int)$claim['response_2'] < (int)$claim['claim']->getVerdict())?1:0;
			
			$is_under_possible = ((int)$claim['claim']->getVerdict() > 1)?1:0;
			
			$is_exact_pre = "";
			if($claim['response_1'] != 0)
				$is_exact_pre = ((int)$claim['response_1'] == (int)$claim['claim']->getVerdict())?1:0;
			
			$is_exact_post = "";
			if($claim['response_2'] != 0)
				$is_exact_post = ((int)$claim['response_2'] == (int)$claim['claim']->getVerdict())?1:0;
			
			$drift = "";	
			if($claim['response_1'] != 0 && $claim['response_2'] != 0)
				$drift = (int)$claim['response_2'] - (int)$claim['response_1'];
				
			$is_backfire = "";	
			if($claim['response_1'] != 0 && $claim['response_2'] != 0) {
				$is_backfire = (((int)$claim['response_1'] < (int)$claim['claim']->getVerdict() && (int)$claim['response_2'] < (int)$claim['response_1'])
							||  ((int)$claim['response_1'] > (int)$claim['claim']->getVerdict() && (int)$claim['response_2'] > (int)$claim['response_1']))?1:0;
			}
			
			$is_backfire_possible = "";	
			if($claim['response_1'] != 0 && $claim['response_2'] != 0)
				$is_backfire_possible = (int)$claim['response_1'] != 1 && (int)$claim['response_1'] != 5;
				
			$is_intended = "";	
			if($claim['response_1'] != 0 && $claim['response_2'] != 0) {
				$is_intended = ((((int)$claim['response_1'] < (int)$claim['claim']->getVerdict() && (int)$claim['response_2'] > (int)$claim['response_1'])
							||  ((int)$claim['response_1'] > (int)$claim['claim']->getVerdict() && (int)$claim['response_2'] < (int)$claim['response_1']))
							&&  abs($claim['response_1'] - $claim['claim']->getVerdict()) < abs($claim['response_2'] - $claim['claim']->getVerdict()))?1:0;
			}
			
			$d .= "".$participant->getItemID().","; // uid
			$d .= "".$claim['treatment'].","; // treatment
			$d .= "".($claim['claim']->getVerdict() - 3).","; // verdict
			$d .= "".($claim['response_1'] - 3).","; // pre Rating
			$d .= "".($claim['response_2'] - 3).","; // post Rating
			$d .= "".$inaccuracy_saturation_pre.","; // Inaccuracy Saturation Pre
			$d .= "".$inaccuracy_saturation_post.","; // Inaccuracy Saturation Post
			$d .= "".$inaccuracy_distance_pre.","; // Inaccuracy Distance Pre
			$d .= "".$inaccuracy_distance_post.","; // Inaccuracy Distance Post
			$d .= "".$inaccuracy_value_pre.","; // Inaccuracy Value Pre
			$d .= "".$inaccuracy_value_post.","; // Inaccuracy Value Post
			$d .= "".$bias_saturation_pre.","; // Bias Saturation Pre
			$d .= "".$bias_saturation_post.","; // Bias Saturation Post
			$d .= "".$is_over_pre.","; // isOver Pre
			$d .= "".$is_over_post.","; // isOver Post
			$d .= "".$is_over_possible.","; // isOverPossible
			$d .= "".$is_under_pre.","; // isUnder Pre
			$d .= "".$is_under_post.","; // isUnder Post
			$d .= "".$is_under_possible.","; // isUnderPossible
			$d .= "".$is_exact_pre.","; // isExact Pre
			$d .= "".$is_exact_post.","; // isExact Post
			$d .= "".$drift.","; // Drift
			$d .= "".abs($drift).","; // Absolute Drift
			$d .= "".$is_backfire.","; // isBackfire
			$d .= "".$is_backfire_possible.","; // isBackfirePossible
			$d .= "".$is_intended.","; // isIntended
			$d .= "\n";
		}
	}
	
	$f_name = "data.csv";
	if(file_exists($f_name))
		unlink($f_name);
	
	$f_data = fopen($f_name,'w');
	fwrite($f_data, $d);
	fclose($f_data);
?>

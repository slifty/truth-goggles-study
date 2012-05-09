<?PHP
	require_once("models/Participant.php");
	require_once("models/Event.php");
	session_start();
	
	if(!isset($_SESSION['participant_id']))
		exit();
	
	$participant = Participant::getObject($_SESSION['participant_id']);
	
	$event = new Event();
	
	$event = new Event();
	$event->setParticipantID($participant->getItemID());
	$event->setType(isset($_GET['t'])?$_GET['t']:"goggles_unknown");
	$event->setData(isset($_GET['d'])?$_GET['d']:"");
	$event->save();
	
?>
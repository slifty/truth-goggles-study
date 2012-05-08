<?php
###
# Info:
#  Last Updated 2011
#  Daniel Schultz
#
###

require_once("conf.php");
require_once("DBConn.php");
require_once("FactoryObject.php");
require_once("JSONObject.php");

class Participant extends FactoryObject implements JSONObject {
	
	# Constants
	
	
	# Static Variables
	
	
	# Instance Variables
	private $client; // string
	private $referral; // string
	private $articleOrder; // string
	private $treatmentOrder; // string
	private $stage; // int
	private $stageProgress; // int
	private $dateCreated; //timestamp
	
	
	# Caches
	
	
	# FactoryObject Methods
	protected static function gatherData($objectString, $start=FactoryObject::LIMIT_BEGINNING, $length=FactoryObject::LIMIT_ALL) {
		$data_arrays = array();
		
		// Load an empty object
		if($objectString === FactoryObject::INIT_EMPTY) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['client'] = "";
			$data_array['referral'] = "";
			$data_array['article_order'] = "";
			$data_array['treatment_order'] = "";
			$data_array['stage'] = 0;
			$data_array['stage_progress'] = 0;
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Load a default object
		if($objectString === FactoryObject::INIT_DEFAULT) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['client'] = "";
			$data_array['referral'] = "";
			$data_array['article_order'] = "";
			$data_array['treatment_order'] = "";
			$data_array['stage'] = 0;
			$data_array['stage_progress'] = 0;
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Set up for lookup
		$mysqli = DBConn::connect();
		
		// Load the object data
		$query_string = "SELECT participants.id AS itemID,
							   participants.client AS client,
							   participants.referral AS referral,
							   participants.article_order AS article_order,
							   participants.treatment_order AS treatment_order,
							   participants.stage AS stage,
							   participants.stage_progress AS stage_progress,
							   unix_timestamp(participants.date_created) as dateCreated
						  FROM participants
						 WHERE participants.id IN (".$objectString.")";
		if($length != FactoryObject::LIMIT_ALL) {
			$query_string .= "
						 LIMIT ".DBConn::clean($start).",".DBConn::clean($length);
		}
		
		$result = $mysqli->query($query_string)
			or print($mysqli->error);
		
		while($resultArray = $result->fetch_assoc()) {
			$data_array = array();
			$data_array['itemID'] = $resultArray['itemID'];
			$data_array['client'] = $resultArray['client'];
			$data_array['referral'] = $resultArray['referral'];
			$data_array['article_order'] = $resultArray['article_order'];
			$data_array['treatment_order'] = $resultArray['treatment_order'];
			$data_array['stage'] = $resultArray['stage'];
			$data_array['stage_progress'] = $resultArray['stage_progress'];
			$data_array['dateCreated'] = $resultArray['dateCreated'];
			$data_arrays[] = $data_array;
		}
		
		$result->free();
		return $data_arrays;
	}
	
	public function load($data_array) {
		parent::load($data_array);
		$this->client = isset($data_array["client"])?$data_array["client"]:"";
		$this->referral = isset($data_array["referral"])?$data_array["referral"]:"";
		$this->articles = isset($data_array["article_order"])?$data_array["article_order"]:"";
		$this->treatments = isset($data_array["treatment_order"])?$data_array["treatment_order"]:"";
		$this->stage = isset($data_array["stage"])?$data_array["stage"]:0;
		$this->stage_progress = isset($data_array["stage_progress"])?$data_array["stage_progress"]:0;
		$this->dateCreated = isset($data_array["dateCreated"])?$data_array["dateCreated"]:0;
	}
	
	
	# JSONObject Methods
	public function toJSON() {
		$json = '{
			"id": '.DBConn::clean($this->getItemID()).',
			"client": '.DBConn::clean($this->getClient()).',
			"referral": '.DBConn::clean($this->getReferral()).',
			"article_order": '.DBConn::clean($this->getArticleOrder()).',
			"treatment_order": '.DBConn::clean($this->getTreatmentOrder()).',
			"stage": '.DBConn::clean($this->getStage()).',
			"stage_progress": '.DBConn::clean($this->getStageProgress()).',
			"date_created": '.DBConn::clean($this->getDateCreated()).'
		}';
		return $json;
	}
	
	
	# Data Methods
	public function validate() {
		return true;
	}
	
	public function save() {
		if(!$this->validate()) return;
		
		$mysqli = DBConn::connect();
		
		if($this->isUpdate()) {
			// Update an existing record
			$query_string = "UPDATE participants
							   SET participants.client = ".DBConn::clean($this->getClient()).",
								   participants.referral = ".DBConn::clean($this->getReferral()).",
								   participants.article_order = ".DBConn::clean($this->getArticleOrder()).",
								   participants.treatment_order = ".DBConn::clean($this->getTreatmentOrder()).",
								   participants.stage = ".DBConn::clean($this->getStage()).",
								   participants.stage_progress = ".DBConn::clean($this->getStageProgress())."
							 WHERE participants.id = ".DBConn::clean($this->getItemID());
							
			$mysqli->query($query_string) or print($mysqli->error);
		} else {
			// Create a new record
			$query_string = "INSERT INTO participants
								   (participants.id,
									participants.client,
									participants.referral,
									participants.article_order,
									participants.treatment_order,
									participants.stage,
									participants.stage_progress,
									participants.date_created)
							VALUES (0,
									".DBConn::clean($this->getClient()).",
									".DBConn::clean($this->getReferral()).",
									".DBConn::clean($this->getArticleOrder()).",
									".DBConn::clean($this->getTreatmentOrder()).",
									".DBConn::clean($this->getStage()).",
									".DBConn::clean($this->getStageProgress()).",
									NOW())";
			
			$mysqli->query($query_string) or print($mysqli->error);
			$this->setItemID($mysqli->insert_id);
		}
		
		// Parent Operations
		return parent::save();
	}
	
	public function delete() {
		parent::delete();
		$mysqli = DBConn::connect();
		
		// Delete this record
		$query_string = "DELETE FROM participants
							  WHERE participants.id = ".DBConn::clean($this->getItemID());
		$mysqli->query($query_string);
	}
	
	
	# Getters
	public function getClient() { return $this->client; }
	
	public function getReferral() { return $this->referral; }
	
	public function getArticleOrder() { return $this->articleOrder; }
	
	public function getTreatmentOrder() { return $this->treatmentOrder; }
	
	public function getStage() { return $this->stage; }
	
	public function getStageProgress() { return $this->stageProgress; }
	
	public function getDateCreated() { return $this->dateCreated; }
	
	
	# Setters
	public function setClient($str) { $this->client = $str; }
	
	public function setReferral($str) { $this->referral = $str; }
	
	public function setArticleOrder($str) { $this->articleOrder = $str; }

	public function setTreatmentOrder($str) { $this->treatmentOrder = $str; }

	public function setStage($str) { $this->stage = $str; }

	public function setStageProgress($str) { $this->stageProgress = $str; }
	
	
	# Static Methods
	
	
}

?>
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
require_once("Participant.php");

class Response extends FactoryObject implements JSONObject {
	
	# Constants
	
	
	# Static Variables
	
	
	# Instance Variables
	private $participantID; // int
	private $questionID; // string
	private $content; // string
	private $dateCreated; //timestamp
	
	
	# Caches
	private $participant
	
	
	# FactoryObject Methods
	protected static function gatherData($objectString, $start=FactoryObject::LIMIT_BEGINNING, $length=FactoryObject::LIMIT_ALL) {
		$data_arrays = array();
		
		// Load an empty object
		if($objectString === FactoryObject::INIT_EMPTY) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['participantID'] = 0;
			$data_array['questionID'] = "";
			$data_array['content'] = "";
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Load a default object
		if($objectString === FactoryObject::INIT_DEFAULT) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['participantID'] = 0;
			$data_array['questionID'] = "";
			$data_array['content'] = "";
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Set up for lookup
		$mysqli = DBConn::connect();
		
		// Load the object data
		$query_string = "SELECT responses.id AS itemID,
							   responses.participant_id AS participantID,
							   responses.question_id AS questionID,
							   responses.content AS content,
							   unix_timestamp(responses.date_created) as dateCreated
						  FROM responses
						 WHERE responses.id IN (".$objectString.")";
		if($length != FactoryObject::LIMIT_ALL) {
			$query_string .= "
						 LIMIT ".DBConn::clean($start).",".DBConn::clean($length);
		}
		
		$result = $mysqli->query($query_string)
			or print($mysqli->error);
		
		while($resultArray = $result->fetch_assoc()) {
			$data_array = array();
			$data_array['itemID'] = $resultArray['itemID'];
			$data_array['participantID'] = $resultArray['participantID'];
			$data_array['questionID'] = $resultArray['questionID'];
			$data_array['content'] = $resultArray['content'];
			$data_array['dateCreated'] = $resultArray['dateCreated'];
			$data_arrays[] = $data_array;
		}
		
		$result->free();
		return $data_arrays;
	}
	
	public function load($data_array) {
		parent::load($data_array);
		$this->type = isset($data_array["participantID"])?$data_array["participantID"]:0;
		$this->type = isset($data_array["questionID"])?$data_array["questionID"]:"";
		$this->type = isset($data_array["content"])?$data_array["content"]:"";
		$this->dateCreated = isset($data_array["dateCreated"])?$data_array["dateCreated"]:0;
	}
	
	
	# JSONObject Methods
	public function toJSON() {
		$json = '{
			"id": '.DBConn::clean($this->getItemID()).',
			"participant": '.$this->getParticipant()->toJSON().',
			"questionID": '.DBConn::clean($this->getQuestionID()).',
			"content": '.DBConn::clean($this->getContent()).',
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
			$query_string = "UPDATE responses
							   SET responses.participant_id = ".DBConn::clean($this->getParticipantID()).",
								   responses.question_id = ".DBConn::clean($this->getQuestionID()).",
								   responses.content = ".DBConn::clean($this->getContent()).",
							 WHERE responses.id = ".DBConn::clean($this->getItemID());
							
			$mysqli->query($query_string) or print($mysqli->error);
		} else {
			// Create a new record
			$query_string = "INSERT INTO responses
								   (responses.id,
									responses.participant_id,
									responses.question_id,
									responses.content,
									responses.date_created)
							VALUES (0,
									".DBConn::clean($this->getParticipantID()).",
									".DBConn::clean($this->getQuestionID()).",
									".DBConn::clean($this->getContent()).",
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
		$query_string = "DELETE FROM responses
							  WHERE responses.id = ".DBConn::clean($this->getItemID());
		$mysqli->query($query_string);
	}
	
	
	# Getters
	public function getParticipantID() { return $this->participantID; }

	public function getQuestionID() { return $this->questionID; }

	public function getContent() { return $this->content; }

	public function getDateCreated() { return $this->dateCreated; }

	public function getParticipant() {
		if($this->participant != null)
			return $this->participant;
		return $this->participant = Participant::getObject($this->getParticipantID());
	}
	
	
	# Setters
	public function setParticipantID($int) { $this->participantID = $str; }
	
	public function setQuestionID($str) { $this->questionID = $str; }
	
	public function setContent($str) { $this->content = $str; }
	
	
	# Static Methods
	
}

?>
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

class Event extends FactoryObject implements JSONObject {
	
	# Constants
	
	
	# Static Variables
	
	
	# Instance Variables
	private $participantID; // int
	private $type; // string
	private $data; // string
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
			$data_array['type'] = "";
			$data_array['data'] = "";
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Load a default object
		if($objectString === FactoryObject::INIT_DEFAULT) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['participantID'] = 0;
			$data_array['type'] = "";
			$data_array['data'] = "";
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Set up for lookup
		$mysqli = DBConn::connect();
		
		// Load the object data
		$query_string = "SELECT events.id AS itemID,
							   events.participant_id AS participantID,
							   events.type AS type,
							   events.data AS data,
							   unix_timestamp(events.date_created) as dateCreated
						  FROM events
						 WHERE events.id IN (".$objectString.")";
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
			$data_array['type'] = $resultArray['type'];
			$data_array['data'] = $resultArray['data'];
			$data_array['dateCreated'] = $resultArray['dateCreated'];
			$data_arrays[] = $data_array;
		}
		
		$result->free();
		return $data_arrays;
	}
	
	public function load($data_array) {
		parent::load($data_array);
		$this->participantID = isset($data_array["participantID"])?$data_array["participantID"]:0;
		$this->type = isset($data_array["type"])?$data_array["type"]:"";
		$this->data = isset($data_array["data"])?$data_array["data"]:"";
		$this->dateCreated = isset($data_array["dateCreated"])?$data_array["dateCreated"]:0;
	}
	
	
	# JSONObject Methods
	public function toJSON() {
		$json = '{
			"id": '.DBConn::clean($this->getItemID()).',
			"participant": '.$participant->toJSON().',
			"type": '.DBConn::clean($this->getContent()).',
			"data": '.DBConn::clean($this->getData()).',
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
			$query_string = "UPDATE events
							   SET events.participant_id = ".DBConn::clean($this->getParticipantID()).",
								   events.type = ".DBConn::clean($this->getType()).",
								   events.data = ".DBConn::clean($this->getData())."
							 WHERE events.id = ".DBConn::clean($this->getItemID());
							
			$mysqli->query($query_string) or print($mysqli->error);
		} else {
			// Create a new record
			$query_string = "INSERT INTO events
								   (events.id,
									events.participant_id,
									events.type,
									events.data,
									events.date_created)
							VALUES (0,
									".DBConn::clean($this->getParticipantID()).",
									".DBConn::clean($this->getType()).",
									".DBConn::clean($this->getData()).",
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
		$query_string = "DELETE FROM events
							  WHERE events.id = ".DBConn::clean($this->getItemID());
		$mysqli->query($query_string);
	}
	
	
	# Getters
	public function getParticipantID() { return $this->participantID; }
	
	public function getType() { return $this->type; }
	
	public function getData() { return $this->data; }

	public function getDateCreated() { return $this->dateCreated; }

	public function getParticipant() {
		if($this->participant != null)
			return $this->participant;
		return $this->participant = Participant::getObject($this->getParticipantID());
	}
	
	
	# Setters
	public function setParticipantID($int) { $this->participantID = $int; }
	
	public function setType($str) { $this->type = $str; }
	
	public function setData($str) { $this->data = $str; }
	
	
	# Static Methods
	public static function getObjectsByParticipantID($participantID) {
		$query_string = "SELECT events.id as itemID 
						   FROM events
						  WHERE events.participant_id = ".DBConn::clean($participantID);
		return Claim::getObjects($query_string);
	}
	
}

?>
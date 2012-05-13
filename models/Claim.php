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

class Claim extends FactoryObject implements JSONObject {
	
	# Constants
	
	
	# Static Variables
	
	
	# Instance Variables
	private $articleID; // int
	private $content; // string
	private $verdict; // int
	private $dateCreated; //timestamp
	
	
	# Caches
	
	
	# FactoryObject Methods
	protected static function gatherData($objectString, $start=FactoryObject::LIMIT_BEGINNING, $length=FactoryObject::LIMIT_ALL) {
		$data_arrays = array();
		
		// Load an empty object
		if($objectString === FactoryObject::INIT_EMPTY) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['articleID'] = 0;
			$data_array['content'] = "";
			$data_array['verdict'] = 0;
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Load a default object
		if($objectString === FactoryObject::INIT_DEFAULT) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['articleID'] = 0;
			$data_array['content'] = "";
			$data_array['verdict'] = 0;
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Set up for lookup
		$mysqli = DBConn::connect();
		
		// Load the object data
		$query_string = "SELECT claims.id AS itemID,
							   claims.article_id AS articleID,
							   claims.content AS content,
							   claims.verdict AS verdict,
							   unix_timestamp(claims.date_created) as dateCreated
						  FROM claims
						 WHERE claims.id IN (".$objectString.")";
		if($length != FactoryObject::LIMIT_ALL) {
			$query_string .= "
						 LIMIT ".DBConn::clean($start).",".DBConn::clean($length);
		}
		
		$result = $mysqli->query($query_string)
			or print($mysqli->error);
		
		while($resultArray = $result->fetch_assoc()) {
			$data_array = array();
			$data_array['itemID'] = $resultArray['itemID'];
			$data_array['articleID'] = $resultArray['articleID'];
			$data_array['content'] = $resultArray['content'];
			$data_array['verdict'] = $resultArray['verdict'];
			$data_array['dateCreated'] = $resultArray['dateCreated'];
			$data_arrays[] = $data_array;
		}
		
		$result->free();
		return $data_arrays;
	}
	
	public function load($data_array) {
		parent::load($data_array);
		$this->articleID = isset($data_array["articleID"])?$data_array["articleID"]:0;
		$this->content = isset($data_array["content"])?$data_array["content"]:"";
		$this->verdict = isset($data_array["verdict"])?$data_array["verdict"]:0;
		$this->dateCreated = isset($data_array["dateCreated"])?$data_array["dateCreated"]:0;
	}
	
	
	# JSONObject Methods
	public function toJSON() {
		$json = '{
					"id": '.DBConn::clean($this->getItemID()).',
					"article_id": '.DBConn::clean($this->getArticleID()).',
					"content": '.DBConn::clean($this->getContent()).',
					"verdict": '.DBConn::clean($this->getVerdict()).',
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
			$query_string = "UPDATE claims
							   SET claims.article_id = ".DBConn::clean($this->getArticleID()).",
								   claims.content = ".DBConn::clean($this->getContent()).",
								   claims.verdict = ".DBConn::clean($this->getVerdict())."
							 WHERE claims.id = ".DBConn::clean($this->getItemID());
							
			$mysqli->query($query_string) or print($mysqli->error);
		} else {
			// Create a new record
			$query_string = "INSERT INTO claims
								   (claims.id,
									claims.article_id,
									claims.content,
									claims.verdict,
									claims.date_created)
							VALUES (0,
									".DBConn::clean($this->getArticleID()).",
									".DBConn::clean($this->getContent()).",
									".DBConn::clean($this->getVerdict()).",
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
		$query_string = "DELETE FROM claims
							  WHERE claims.id = ".DBConn::clean($this->getItemID());
		$mysqli->query($query_string);
	}
	
	
	# Getters
	public function getArticleID() { return $this->articleID; }
	
	public function getContent() { return $this->content; }
	
	public function getVerdict() { return $this->verdict; }

	public function getDateCreated() { return $this->dateCreated; }
	
	
	# Setters
	public function setArticleID($int) { $this->articleID = $int; }
	
	public function setContent($str) { $this->content = $str; }
	
	public function setVerdict($int) { $this->verdict = $int; }
	
	
	# Static Methods
	public static function getObjectByContent($content) {
		$query_string = "SELECT claims.id as itemID 
						   FROM claims
						  WHERE claims.content LIKE ".DBConn::clean($content);
		return array_pop(Claim::getObjects($query_string));
	}
	
	public static function getAllObjects() {
		return Claim::getObjects("select claims.id from claims");
	}
	
}

?>
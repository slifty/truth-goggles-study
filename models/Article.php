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

class Article extends FactoryObject implements JSONObject {
	
	# Constants
	
	
	# Static Variables
	
	
	# Instance Variables
	private $content; // string
	private $dateCreated; //timestamp
	
	
	# Caches
	
	
	# FactoryObject Methods
	protected static function gatherData($objectString, $start=FactoryObject::LIMIT_BEGINNING, $length=FactoryObject::LIMIT_ALL) {
		$data_arrays = array();
		
		// Load an empty object
		if($objectString === FactoryObject::INIT_EMPTY) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['content'] = "";
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Load a default object
		if($objectString === FactoryObject::INIT_DEFAULT) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['content'] = "";
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Set up for lookup
		$mysqli = DBConn::connect();
		
		// Load the object data
		$query_string = "SELECT articles.id AS itemID,
							   articles.content AS content,
							   unix_timestamp(articles.date_created) as dateCreated
						  FROM articles
						 WHERE articles.id IN (".$objectString.")";
		if($length != FactoryObject::LIMIT_ALL) {
			$query_string .= "
						 LIMIT ".DBConn::clean($start).",".DBConn::clean($length);
		}
		
		$result = $mysqli->query($query_string)
			or print($mysqli->error);
		
		while($resultArray = $result->fetch_assoc()) {
			$data_array = array();
			$data_array['itemID'] = $resultArray['itemID'];
			$data_array['content'] = $resultArray['content'];
			$data_array['dateCreated'] = $resultArray['dateCreated'];
			$data_arrays[] = $data_array;
		}
		
		$result->free();
		return $data_arrays;
	}
	
	public function load($data_array) {
		parent::load($data_array);
		$this->content = isset($data_array["content"])?$data_array["content"]:"";
		$this->dateCreated = isset($data_array["dateCreated"])?$data_array["dateCreated"]:0;
	}
	
	
	# JSONObject Methods
	public function toJSON() {
		$json = '{
			"id": '.DBConn::clean($this->getItemID()).',
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
			$query_string = "UPDATE articles
							   SET articles.content = ".DBConn::clean($this->getContent())."
							 WHERE articles.id = ".DBConn::clean($this->getItemID());
							
			$mysqli->query($query_string) or print($mysqli->error);
		} else {
			// Create a new record
			$query_string = "INSERT INTO articles
								   (articles.id,
									articles.content,
									articles.date_created)
							VALUES (0,
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
		$query_string = "DELETE FROM articles
							  WHERE articles.id = ".DBConn::clean($this->getItemID());
		$mysqli->query($query_string);
	}
	
	
	# Getters
	public function getContent() { return $this->content; }

	public function getDateCreated() { return $this->dateCreated; }
	
	
	# Setters
	public function setContent($str) { $this->content = $str; }
	
	
	# Static Methods
	public static function getAllObjects() {
		return Article::getObjects("select articles.id from articles");
	}
}

?>
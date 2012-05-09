<?php
set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once("conf.php");
require_once("models/DBConn.php");

// Get connection
$mysqli = DBConn::connect();
if(!$mysqli || $mysqli->connect_error) {
	echo("Could not connect to DB.  Did you follow the install instructions in README?\n");
	die();
}

// Look up installed version
$result = $mysqli->query("select appinfo.version as version
				  			from appinfo");

if(!$result || $result->num_rows == 0)
	$version = 0;
else {
	$resultArray = $result->fetch_assoc();
	$version = $resultArray['version'];
	$result->free();
}

echo("Current Version: ".$version."\n");
switch($version) {
	case 0: // Never installed before
		echo("Fresh Install...\n");
		echo("Creating appinfo table\n");
		$mysqli->query("CREATE TABLE appinfo (version varchar(8))") or print($mysqli->error);
		$mysqli->query("INSERT INTO appinfo (version) values('1');") or print($mysqli->error);
			
	case 1: // First update
		echo("Creating events table\n");
		$mysqli->query("CREATE TABLE events (id int auto_increment primary key,
											participant_id int,
											type varchar(64),
											data text,
											date_created datetime)") or print($mysqli->error);
		echo("Creating participants table\n");
		$mysqli->query("CREATE TABLE participants (id int auto_increment primary key,
											client text,
											referral text,
											article_order text,
											treatment_order text,
											stage int,
											stage_progress int,
											date_created datetime)") or print($mysqli->error);
		echo("Creating articles table\n");
		$mysqli->query("CREATE TABLE articles (id int auto_increment primary key,
											content text,
											date_created datetime)") or print($mysqli->error);
		echo("Creating claims table\n");
		$mysqli->query("CREATE TABLE claims (id int auto_increment primary key,
											content text,
											date_created datetime)") or print($mysqli->error);
		echo("Creating responses table\n");
		$mysqli->query("CREATE TABLE responses (id int auto_increment primary key,
											participant_id int,
											question_id varchar(16),
											content text,
											date_created datetime)") or print($mysqli->error);
		
		echo("Updating app version\n");
		$mysqli->query("UPDATE appinfo set version ='2';") or print($mysqli->error);
		
	case 2:
		echo("Updating participants table\n");
		$mysqli->query("ALTER TABLE participants
					      ADD COLUMN claim_order_1 text AFTER treatment_order,
					      ADD COLUMN claim_order_2 text AFTER claim_order_1") or print($mysqli->error);
		
		echo("Updating app version\n");
		$mysqli->query("UPDATE appinfo set version ='3';") or print($mysqli->error);
		
	default:
		echo("Finished updating the schema\n");
}
?>
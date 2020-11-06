<?php
	require_once("../../config/Database.php");
	require_once("../../models/Office.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Invalid Request.")
	);

	if( (!isset($_GET['officeCode'])) || (intval($_GET['officeCode'])<=0) ){
		exit(json_encode($finalResponse));
	}

	error_reporting(0);

	$db = new Database();

	$db = $db->connect();

	$office = new Office($db);

	$office->officeCode = $_GET['officeCode'];

	$office->details();

	if(!$office->valid){
		$finalResponse['message'] = array("Office {$office->officeCode} does not exist");
		exit(json_encode($finalResponse));
	}

	$officeDetailsTxt = "{$office->officeCode} @ {$office->city}";

	//Check if office has existing employees and exit if present
	$office->countEmployees();

	if($office->employees){
		$finalResponse['message'] = array("Office $officeDetailsTxt has {$office->employees} active employees. Please delete these employees and try again.");

		exit(json_encode($finalResponse));
	}

	// Run DELETE query and exit if it fails
	$office->delete();
	if(!$office->valid){
		$finalResponse['message'] = array("Failed to delete customer {$officeDetailsTxt}");
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully deleted customer {$officeDetailsTxt}")
	);

	exit(json_encode($finalResponse));
?>
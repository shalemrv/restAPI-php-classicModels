<?php
	require_once("../../config/Database.php");
	require_once("../../models/Office.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Invalid Request.")
	);

	$db = new Database();
	$db = $db->connect();

	$params = json_decode(file_get_contents("php://input"), true);

	$office = new Office($db);
	$office->officeCode = $params['officeCode'];

	// Check if Customer Exists and exit if not
	$office->details();
	if(!$office->valid){
		$finalResponse['message'] = array("Office {$office->officeCode} does not exist");
		exit(json_encode($finalResponse));
	}
	
	$office->city 			= $params['city'];
	$office->phone 			= $params['phone'];
	$office->addressLine1 	= $params['addressLine1'];
	$office->addressLine2 	= $params['addressLine2'];
	$office->state 			= $params['state'];
	$office->country 		= $params['country'];
	$office->postalCode 	= $params['postalCode'];
	$office->territory 		= $params['territory'];

	$officeDetailsTxt = "{$office->officeCode} @ {$office->city}";

	$office->errors[] = "Cannot update details of customer $officeDetailsTxt";

	// Check if data is valid and exit if not
	$office->validate();
	if(!$office->valid){
		$finalResponse['message'] = $office->errors;
		exit(json_encode($finalResponse));
	}

	// Run update query and exit if it fails
	$office->update();
	if(!$office->valid){
		$finalResponse['message'] = $office->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully updated customer $officeDetailsTxt.")
	);

	exit(json_encode($finalResponse));
?>
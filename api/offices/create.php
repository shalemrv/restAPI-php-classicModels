<?php
	require_once("../../config/Database.php");
	require_once("../../models/Office.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$db = new Database();
	$db = $db->connect();

	$office = new Office($db);

	$params = json_decode(file_get_contents("php://input"), true);
	
	$office->officeCode		= $params['officeCode'];
	$office->city			= $params['city'];
	$office->phone			= $params['phone'];
	$office->addressLine1	= $params['addressLine1'];
	$office->addressLine2	= $params['addressLine2'];
	$office->state			= $params['state'];
	$office->country		= $params['country'];
	$office->postalCode		= $params['postalCode'];
	$office->territory		= $params['territory'];

	// Check if data is valid and exit if not
	$office->validate();
	if(!$office->valid){
		$finalResponse['message'] = $office->errors;
		exit(json_encode($finalResponse));
	}

	// Check if same name/company exist and exit if duplicate
	$office->isDuplicate();
	if(!$office->valid){
		$finalResponse['message'] = $office->errors;
		exit(json_encode($finalResponse));
	}

	// Run INSERT query and exit if it fails
	$office->create();
	if(!$office->valid){
		$finalResponse['message'] = $office->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully created new office {$office->officeCode} @ {$office->city}."
	);

	exit(json_encode($finalResponse));
?>
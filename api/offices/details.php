<?php
	require_once("../../config/Database.php");
	require_once("../../models/Office.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	if( (!isset($_GET['officeCode'])) || (intval($_GET['officeCode'])<=0) ){
		exit(json_encode($finalResponse));
	}

	$db = new Database();

	$db = $db->connect();

	$office = new Office($db);

	$office->officeCode = $_GET['officeCode'];

	$office->details();

	if(!$office->valid){
		$finalResponse['message'] = "Cannot retrieve details for the office code {$_GET['officeCode']}";
		exit(json_encode($finalResponse));
	}

	$officeDetails = array(
		"officeCode"	=> $office->officeCode,
		"city"			=> $office->city,
		"phone"			=> $office->phone,
		"addressLine1"	=> $office->addressLine1,
		"addressLine2"	=> $office->addressLine2,
		"state"			=> $office->state,
		"country"		=> $office->country,
		"postalCode"	=> $office->postalCode,
		"territory"		=> $office->territory
	);

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully retrieved office details of {$_GET['officeCode']}.",
		"result"	=> $officeDetails
	);

	exit(json_encode($finalResponse));
?>
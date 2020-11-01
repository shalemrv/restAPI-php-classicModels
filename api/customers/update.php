<?php
	require_once("../../config/Database.php");
	require_once("../../models/Customer.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$db = new Database();
	$db = $db->connect();

	$params = json_decode(file_get_contents("php://input"), true);

	$customer = new Customer($db);
	$customer->customerNumber = $params['customerNumber'];

	// Check if Customer Exists and exit if not
	$customer->details();
	if(!$customer->valid){
		$finalResponse['message'] = "Customer {$customer->customerNumber} does not exist";
		exit(json_encode($finalResponse));
	}
	
	$customer->customerName 			= $params['customerName'];
	$customer->contactLastName 			= $params['contactLastName'];
	$customer->contactFirstName 		= $params['contactFirstName'];
	$customer->phone 					= $params['phone'];
	$customer->addressLine1 			= $params['addressLine1'];
	$customer->addressLine2 			= $params['addressLine2'];
	$customer->city 					= $params['city'];
	$customer->state 					= $params['state'];
	$customer->postalCode 				= $params['postalCode'];
	$customer->country 					= $params['country'];
	$customer->salesRepEmployeeNumber 	= $params['salesRepEmployeeNumber'];
	$customer->creditLimit 				= $params['creditLimit'];

	$customerDetailsTxt = "{$customer->customerNumber} - {$customer->contactFirstName} {$customer->contactLastName}.";

	$customer->errors[] = "Cannot update details of customer $customerDetailsTxt";

	// Check if data is valid and exit if not
	$customer->validate();
	if(!$customer->valid){
		$finalResponse['message'] = $customer->errors;
		exit(json_encode($finalResponse));
	}

	// Run update query and exit if it fails
	$customer->update();
	if(!$customer->valid){
		$finalResponse['message'] = $customer->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully updated customer $customerDetailsTxt."
	);

	exit(json_encode($finalResponse));
?>
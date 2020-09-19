<?php
	require("../../config/Database.php");
	require("../../models/Customer.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$db = new Database();
	$db = $db->connect();

	$customer = new Customer($db);

	$params = json_decode(file_get_contents("php://input"), true);
	
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

	// Check if data is valid and exit if not
	$customer->validate();
	if(!$customer->valid){
		$finalResponse['message'] = $customer->errors;
		exit(json_encode($finalResponse));
	}

	// Check if same name/company exist and exit if duplicate
	$customer->isDuplicate();
	if(!$customer->valid){
		$finalResponse['message'] = $customer->errors;
		exit(json_encode($finalResponse));
	}

	// Check if selected sales rep exists and exit if false
	$customer->employeeExists();
	if(!$customer->valid){
		$finalResponse['message'] = $customer->errors;
		exit(json_encode($finalResponse));
	}

	// Run INSERT query and exit if it fails
	$customer->create();
	if(!$customer->valid){
		$finalResponse['message'] = $customer->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully created new customer {$customer->contactFirstName} {$customer->contactLastName}."
	);

	exit(json_encode($finalResponse));
?>
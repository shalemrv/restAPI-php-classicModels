<?php
	require("../../config/Database.php");
	require("../../models/Order.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$db = new Database();
	$db = $db->connect();

	$params = json_decode(file_get_contents("php://input"), true);

	$order = new Order($db);
	$order->customerNumber = $params['customerNumber'];

	// Check if Customer Exists and exit if not
	$order->details();
	if(!$order->valid){
		$finalResponse['message'] = "Customer {$order->customerNumber} does not exist";
		exit(json_encode($finalResponse));
	}
	
	$order->customerName 			= $params['customerName'];
	$order->contactLastName 			= $params['contactLastName'];
	$order->contactFirstName 		= $params['contactFirstName'];
	$order->phone 					= $params['phone'];
	$order->addressLine1 			= $params['addressLine1'];
	$order->addressLine2 			= $params['addressLine2'];
	$order->city 					= $params['city'];
	$order->state 					= $params['state'];
	$order->postalCode 				= $params['postalCode'];
	$order->country 					= $params['country'];
	$order->salesRepEmployeeNumber 	= $params['salesRepEmployeeNumber'];
	$order->creditLimit 				= $params['creditLimit'];

	$customerDetailsTxt = "{$order->customerNumber} - {$order->contactFirstName} {$order->contactLastName}.";

	$order->errors[] = "Cannot update details of customer $customerDetailsTxt";

	// Check if data is valid and exit if not
	$order->validate();
	if(!$order->valid){
		$finalResponse['message'] = $order->errors;
		exit(json_encode($finalResponse));
	}

	// Run update query and exit if it fails
	$order->update();
	if(!$order->valid){
		$finalResponse['message'] = $order->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully updated customer $customerDetailsTxt."
	);

	exit(json_encode($finalResponse));
?>
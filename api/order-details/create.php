<?php
	require_once("../../config/Database.php");
	require_once("../../models/OrderDetails.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$db = new Database();
	$db = $db->connect();

	$orderDetails = new OrderDetails($db);

	$params = json_decode(file_get_contents("php://input"), true);
	
	$orderDetails->orderNumber 		= $params['orderNumber'];
	$orderDetails->productCode 		= $params['productCode'];
	$orderDetails->quantityOrdered 	= $params['quantityOrdered'];
	$orderDetails->priceEach 		= $params['priceEach'];

	// Check if data is valid and exit if not
	$orderDetails->validate();
	if(!$orderDetails->valid){
		$finalResponse['message'] = $orderDetails->errors;
		exit(json_encode($finalResponse));
	}

	// Check if same duplicate exist and exit if duplicate
	$orderDetails->isDuplicate();
	if(!$orderDetails->valid){
		$finalResponse['message'] = $orderDetails->errors;
		exit(json_encode($finalResponse));
	}

	// Check if Order exists and exit if not
	$orderDetails->orderExists();
	if(!$orderDetails->valid){
		$finalResponse['message'] = $orderDetails->errors;
		exit(json_encode($finalResponse));
	}

	// Check if selected Product exists and exit if not
	$orderDetails->productExists();
	if(!$orderDetails->valid){
		$finalResponse['message'] = $orderDetails->errors;
		exit(json_encode($finalResponse));
	}

	// Run INSERT query and exit if it fails
	$orderDetails->create();
	if(!$orderDetails->valid){
		$finalResponse['message'] = $orderDetails->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully added order details to {$orderDetails->orderNumber}."
	);

	exit(json_encode($finalResponse));
?>
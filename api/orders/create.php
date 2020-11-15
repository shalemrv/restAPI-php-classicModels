<?php
	require_once("../../config/Database.php");
	require_once("../../models/Order.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Invalid Request.")
	);

	$db = new Database();
	$db = $db->connect();

	$order = new Order($db);

	$params = json_decode(file_get_contents("php://input"), true);
	
	$order->orderDate 			= $params['orderDate'];
	$order->requiredDate 		= $params['requiredDate'];
	$order->shippedDate 		= (isset($params['shippedDate']))? $params['shippedDate'] : "";
	$order->status 				= $params['status'];
	$order->comments 			= (isset($params['comments']))? $params['comments'] : "";
	$order->customerNumber 		= $params['customerNumber'];
	$order->orderDetailsParams	= $params['orderDetails'];

	//Get new order number
	$order->getNewOrderNumber();

	// Check if data is valid and exit if not
	$order->validate();
	if(!$order->valid){
		$finalResponse['message'] = $order->errors;
		exit(json_encode($finalResponse));
	}

	// Check if selected sales rep exists and exit if false
	$order->customerExists();
	if(!$order->valid){
		$finalResponse['message'] = $order->errors;
		exit(json_encode($finalResponse));
	}

	// Check if order details is empty and exit if true
	if(!sizeof($order->orderDetailsList)){
		$finalResponse['message'] = "Order details cannot be empty.";
		exit(json_encode($finalResponse));
	}

	// Run INSERT query and exit if it fails
	$order->create();
	if(!$order->valid){
		$finalResponse['message'] = $order->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully created new order {$order->orderNumber}.")
	);

	exit(json_encode($finalResponse));
?>
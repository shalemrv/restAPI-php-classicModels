<?php
	require_once("../../config/Database.php");
	require_once("../../models/OrderDetails.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$params = json_decode(file_get_contents("php://input"), true);

	if( (!isset($params['orderNumber'])) || (intval($params['orderNumber'])<=0) ){
		exit(json_encode($finalResponse));
	}
	
	if( (!isset($params['productCode'])) || (strlen($params['productCode'])<5) ){
		exit(json_encode($finalResponse));
	}

	$db = new Database();
	$db = $db->connect();

	$orderDetails = new OrderDetails($db);

	$orderDetails->orderNumber = $params['orderNumber'];
	$orderDetails->productCode = $params['productCode'];

	// Check if Order details exist and exit if not
	$orderDetails->details();
	if(!$orderDetails->valid){
		$finalResponse['message'] = $this->errors;
		exit(json_encode($finalResponse));
	}
	
	$orderDetails->quantityOrdered 	= $params['quantityOrdered'];
	$orderDetails->priceEach 		= $params['priceEach'];

	// Check if data is valid and exit if not
	$orderDetails->validate();
	if(!$orderDetails->valid){
		$finalResponse['message'] = $orderDetails->errors;
		exit(json_encode($finalResponse));
	}

	// Run update query and exit if it fails
	$orderDetails->update();
	if(!$orderDetails->valid){
		$finalResponse['message'] = $orderDetails->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully updated order details of Order: {$orderDetails->orderNumber} - {$orderDetails->productName}.")
	);

	exit(json_encode($finalResponse));
?>
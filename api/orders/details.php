<?php
	require("../../config/Database.php");
	require("../../models/Order.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	if( (!isset($_GET['orderNumber'])) || (intval($_GET['orderNumber'])<=0) ){
		exit(json_encode($finalResponse));
	}

	$db = new Database();

	$db = $db->connect();

	$order = new Order($db);

	$order->orderNumber = intval($_GET['orderNumber']);

	$order->details();

	if(!$order->valid){
		$finalResponse['message'] = "Cannot retrieve details for the order number {$_GET['orderNumber']}";
		exit(json_encode($finalResponse));
	}

	$orderDetails = array(
		"orderNumber"		=> intval($order->orderNumber),
		"orderDate"			=> $order->orderDate,
		"requiredDate"		=> $order->requiredDate,
		"shippedDate"		=> $order->shippedDate,
		"status"			=> $order->status,
		"comments"			=> $order->comments,
		"customerNumber"	=> intval($order->customerNumber),
		"customerName"		=> $order->customerName
	);

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully retrieved order details of {$_GET['orderNumber']}.",
		"result"	=> $orderDetails
	);

	exit(json_encode($finalResponse));
?>
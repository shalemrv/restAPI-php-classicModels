<?php
	require_once("../../config/Database.php");
	require_once("../../models/OrderDetails.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Failed to retrieve order details.")
	);

	if( (!isset($_GET['orderNumber'])) || (intval($_GET['orderNumber'])<=0) ){
		exit(json_encode($finalResponse));
	}
	
	$db = new Database();

	$db = $db->connect();

	$orderDetails = new OrderDetails($db);

	$orderDetails->orderNumber = intval($_GET['orderNumber']);

	$orderDetailsList = $orderDetails->list();

	if(sizeof($orderDetailsList)){
		$finalResponse = array(
			"complete"	=> true,
			"message"	=> array("Retrieved ".sizeof($orderDetailsList)." order details."),
			"result"	=> $orderDetailsList
		);
	}

	exit(json_encode($finalResponse));
?>
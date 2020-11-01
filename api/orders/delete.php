<?php
	require_once("../../config/Database.php");
	require_once("../../models/Order.php");

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
		$finalResponse['message'] = "Order {$order->orderNumber} does not exist";
		exit(json_encode($finalResponse));
	}

	// Get number of child Items
	$order->countOrderChildren();

	// Run DELETE query and exit if it fails
	$order->delete();
	if(!$order->valid){
		$finalResponse['message'] = "Failed to delete order {$order->orderNumber}";
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully deleted order {$order->orderNumber} and its {$order->orderChildren} child items."
	);

	exit(json_encode($finalResponse));
?>
<?php
	require_once("../../config/Database.php");
	require_once("../../models/Order.php");
	require_once("../../models/OrderDetails.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Failed to retrieve list."
	);

	$db = new Database();

	$db = $db->connect();

	$order			= new Order($db);
	$orderDetails	= new OrderDetails($db);

	$ordersDataset =  $order->list();

	if($ordersDataset->rowCount()==0){
		exit(json_encode($finalResponse));
	}

	$ordersList = array();

	$i = 0;

	while($details = $ordersDataset->fetch(PDO::FETCH_ASSOC)){
		extract($details);
		$i++;

		$orderDetails->orderNumber = $orderNumber;

		$orderDetailsList = $orderDetails->list();

		if(sizeof($orderDetailsList)==0){
			continue;
		}

		array_push(
			$ordersList,
			array(
				"orderNumber"		=> intval($orderNumber),
				"orderDate"			=> $orderDate,
				"requiredDate"		=> $requiredDate,
				"shippedDate"		=> $shippedDate,
				"status"			=> $status,
				"comments"			=> $comments,
				"customerNumber"	=> intval($customerNumber),
				"customerName"		=> $customerName,
				"orderDetails"		=> $orderDetailsList
			)
		);
	}
	
	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Retrieved ".sizeof($ordersList)." orders.",
		"result"	=> $ordersList
	);

	echo(json_encode($finalResponse));
?>
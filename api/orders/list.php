<?php
	require("../../config/Database.php");
	require("../../models/Order.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Failed to retrieve list."
	);

	$db = new Database();

	$db = $db->connect();

	$order = new Order($db);

	$ordersDataset =  $order->list();

	if($ordersDataset->rowCount()==0){
		exit(json_encode($finalResponse));
	}

	$ordersList = array();

	$i = 0;

	while($details = $ordersDataset->fetch(PDO::FETCH_ASSOC)){
		extract($details);
		$i++;
		array_push(
			$ordersList,
			array(
				"orderNumber"			=> intval($orderNumber),
				"orderDate"				=> $orderDate,
				"requiredDate"			=> $requiredDate,
				"shippedDate"			=> $shippedDate,
				"status"				=> $status,
				"comments"				=> $comments,
				"customerNumber"		=> intval($customerNumber),
				"customerName"			=> $customerName
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
<?php
	require("../../config/Database.php");
	require("../../models/Payment.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Failed to retrieve list."
	);
	
	$db = new Database();

	$db = $db->connect();

	$payment = new Payment($db);

	$paymentsDataset =  $payment->list();

	if($paymentsDataset->rowCount()==0){
		exit(json_encode($finalResponse));
	}

	$paymentsList = array();

	$i = 0;

	while($paymentDetails = $paymentsDataset->fetch(PDO::FETCH_ASSOC)){
		extract($paymentDetails);
		$i++;
		array_push(
			$paymentsList,
			array(
				"customerNumber"	=> $customerNumber,
				"customerName"		=> $customerName,
				"checkNumber"		=> $checkNumber,
				"paymentDate"		=> $paymentDate,
				"amount"			=> $amount
			)
		);
	}
	
	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Retrieved ".sizeof($paymentsList)." payments.",
		"result"	=> $paymentsList
	);

	echo(json_encode($finalResponse));
?>
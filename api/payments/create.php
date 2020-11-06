<?php
	require_once("../../config/Database.php");
	require_once("../../models/Payment.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Invalid Request.")
	);

	$db = new Database();
	$db = $db->connect();

	$payment = new Payment($db);

	$params = json_decode(file_get_contents("php://input"), true);
	
	$payment->customerNumber	= $params['customerName'];
	$payment->checkNumber		= $params['contactLastName'];
	$payment->paymentDate		= $params['contactFirstName'];
	$payment->amount			= $params['phone'];

	// Check if selected sales rep exists and exit if false
	$payment->customerExists();
	if(!$payment->valid){
		$finalResponse['message'] = $payment->errors;
		exit(json_encode($finalResponse));
	}

	// Check if same customer & checkNum exists and exit if duplicate
	$payment->isDuplicate();
	if(!$payment->valid){
		$finalResponse['message'] = $payment->errors;
		exit(json_encode($finalResponse));
	}

	// Check if data is valid and exit if not
	$payment->validate();
	if(!$payment->valid){
		$finalResponse['message'] = $payment->errors;
		exit(json_encode($finalResponse));
	}

	// Run INSERT query and exit if it fails
	$payment->create();
	if(!$payment->valid){
		$finalResponse['message'] = $payment->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully created new payment. Customer: {$payment->customerNumber} - CheckNo: {$payment->checkNumber}.")
	);

	exit(json_encode($finalResponse));
?>
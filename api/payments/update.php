<?php
	require("../../config/Database.php");
	require("../../models/Payment.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$db = new Database();
	$db = $db->connect();

	$params = json_decode(file_get_contents("php://input"), true);

	$payment = new Payment($db);

	$payment->customerNumber	= intval($_GET['customerNumber']);
	$payment->checkNumber	= $_GET['checkNumber'];

	// Check if Customer Exists and exit if not
	$payment->customerExists();
	if(!$payment->valid){
		$finalResponse['message'] = "Customer {$payment->customerNumber} does not exist";
		exit(json_encode($finalResponse));
	}

	// Check if payment exists and exit if not
	$payment->details();
	if(!$payment->valid){
		$finalResponse['message'] = "Customer: {$payment->customerNumber} - CheckNo: {$payment->checkNumber}. Payment does not exist.";
		exit(json_encode($finalResponse));
	}
	
	$payment->newCheckNumber 	= $params['checkNumber'];
	$payment->paymentDate 		= $params['paymentDate'];
	$payment->amount 			= floatval($params['amount']);

	$paymentDetailsTxt = "Customer: {$payment->customerNumber} - CheckNo: {$payment->checkNumber}.";

	$payment->errors[] = "Cannot update details of customer $paymentDetailsTxt";

	// Check if data is valid and exit if not
	$payment->validate();
	if(!$payment->valid){
		$finalResponse['message'] = $payment->errors;
		exit(json_encode($finalResponse));
	}

	// Run update query and exit if it fails
	$payment->update();
	if(!$payment->valid){
		$finalResponse['message'] = $payment->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully updated Payment $paymentDetailsTxt."
	);

	exit(json_encode($finalResponse));
?>
<?php
	require("../../config/Database.php");
	require("../../models/Payment.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	if( (!isset($_GET['customerNumber'])) || (intval($_GET['customerNumber'])<=0) ){
		exit(json_encode($finalResponse));
	}

	error_reporting(0);

	$db = new Database();

	$db = $db->connect();

	$payment = new Payment($db);

	$payment->customerNumber	= intval($_GET['customerNumber']);
	$payment->checkNumber		= $_GET['checkNumber'];

	$payment->details();

	if(!$payment->valid){
		$finalResponse['message'] = "Customer: {$payment->customerNumber} - CheckNo: {$payment->checkNumber}. Payment does not exist.";
		exit(json_encode($finalResponse));
	}

	$paymentDetailsTxt = "Customer: {$payment->customerNumber} - CheckNo: {$payment->checkNumber}.";

	// Run DELETE query and exit if it fails
	$payment->delete();
	if(!$payment->valid){
		$finalResponse['message'] = "Failed to delete payment of Amount {$payment->amount} {$paymentDetailsTxt}";
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully deleted payment of Amount {$payment->amount} {$paymentDetailsTxt}"
	);

	exit(json_encode($finalResponse));
?>
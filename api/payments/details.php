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

	$db = new Database();

	$db = $db->connect();

	$payment = new Payment($db);

	$payment->customerNumber = $_GET['customerNumber'];

	$payment->details();

	if(!$payment->valid){
		$finalResponse['message'] = "Cannot retrieve details for the customer number {$_GET['customerNumber']}";
		exit(json_encode($finalResponse));
	}

	$customerDetails = array(
		"customerNumber"			=> $payment->customerNumber,
		"customerName"				=> $payment->customerName,
		"contactLastName"			=> $payment->contactLastName,
		"contactFirstName"			=> $payment->contactFirstName,
		"phone"						=> $payment->phone,
		"addressLine1"				=> $payment->addressLine1,
		"addressLine2"				=> $payment->addressLine2,
		"city"						=> $payment->city,
		"state"						=> $payment->state,
		"postalCode"				=> $payment->postalCode,
		"country"					=> $payment->country,
		"salesRepEmployeeNumber"	=> $payment->salesRepEmployeeNumber,
		"creditLimit"				=> $payment->creditLimit
	);

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully retrieved customer details of {$_GET['customerNumber']}.",
		"result"	=> $customerDetails
	);

	exit(json_encode($finalResponse));
?>
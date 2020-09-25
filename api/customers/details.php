<?php
	require("../../config/Database.php");
	require("../../models/Customer.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	if( (!isset($_GET['customerNumber'])) || (intval($_GET['customerNumber'])<=0) ){
		exit(json_encode($finalResponse));
	}

	$db = new Database();

	$db = $db->connect();

	$customer = new Customer($db);

	$customer->customerNumber = intval($_GET['customerNumber']);

	$customer->details();

	if(!$customer->valid){
		$finalResponse['message'] = "Cannot retrieve details for the customer number {$_GET['customerNumber']}";
		exit(json_encode($finalResponse));
	}

	$customerDetails = array(
		"customerNumber"			=> $customer->customerNumber,
		"customerName"				=> $customer->customerName,
		"contactLastName"			=> $customer->contactLastName,
		"contactFirstName"			=> $customer->contactFirstName,
		"phone"						=> $customer->phone,
		"addressLine1"				=> $customer->addressLine1,
		"addressLine2"				=> $customer->addressLine2,
		"city"						=> $customer->city,
		"state"						=> $customer->state,
		"postalCode"				=> $customer->postalCode,
		"country"					=> $customer->country,
		"salesRepEmployeeNumber"	=> $customer->salesRepEmployeeNumber,
		"salesRepEmployeeName"		=> $customer->salesRepEmployeeName,
		"creditLimit"				=> $customer->creditLimit
	);

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully retrieved customer details of {$_GET['customerNumber']}.",
		"result"	=> $customerDetails
	);

	exit(json_encode($finalResponse));
?>
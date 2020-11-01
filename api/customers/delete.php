<?php
	require_once("../../config/Database.php");
	require_once("../../models/Customer.php");

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

	$customer = new Customer($db);

	$customer->customerNumber = intval($_GET['customerNumber']);

	$customer->details();

	if(!$customer->valid){
		$finalResponse['message'] = "Customer {$customer->customerNumber} does not exist";
		exit(json_encode($finalResponse));
	}

	$customerDetailsTxt = "{$customer->customerNumber} - {$customer->contactFirstName} {$customer->contactLastName}.";

	//Check if customer has existing Orders/Payments and exit if present
	$customer->countOrders();
	$customer->countPayments();

	if($customer->orders || $customer->payments){
		$msg = array();
		if($customer->orders){
			$msg[] = "{$customer->orders} existing order(s)";
		}
		if($customer->payments){
			$msg[] = "{$customer->payments} existing payment(s)";
		}

		$finalResponse['message'] = "Customer {$customer->customerNumber} has ".implode(" and ", $msg).". Please delete these records and try again.";

		exit(json_encode($finalResponse));
	}

	// Run DELETE query and exit if it fails
	$customer->delete();
	if(!$customer->valid){
		$finalResponse['message'] = "Failed to delete customer {$customerDetailsTxt}";
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully deleted customer {$customerDetailsTxt}"
	);

	exit(json_encode($finalResponse));
?>
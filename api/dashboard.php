<?php
	require_once("../config/Database.php");

	require_once("../models/Customer.php");
	require_once("../models/Employee.php");
	require_once("../models/Office.php");
	require_once("../models/Order.php");
	require_once("../models/Payment.php");
	require_once("../models/Product.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Failed to retrieve list.")
	);

	$dashboardDetails = array();
	
	$db = new Database();

	$db = $db->connect();

	$customer	= new Customer($db);
	$employee	= new Employee($db);
	$office		= new Office($db);
	$order		= new Order($db);
	$payment	= new Payment($db);
	$product	= new Product($db);

	
	$dashboardDetails["customers"] = $customer->countRecords();

	$dashboardDetails["employees"] = $employee->countRecords();

	$dashboardDetails["offices"] = $office->countRecords();

	$dashboardDetails["products"] = $product->countRecords();

	$dashboardDetails["orders"] = $order->countRecords();

	$dashboardDetails["payments"] = $payment->countRecords();

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully retrieved dashboard data."),
		"result"	=> $dashboardDetails
	);

	exit(json_encode($finalResponse));


?>
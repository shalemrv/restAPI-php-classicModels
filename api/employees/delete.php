<?php
	require_once("../../config/Database.php");
	require_once("../../models/Employee.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Invalid Request.")
	);

	if( (!isset($_GET['employeeNumber'])) || (intval($_GET['employeeNumber'])<=0) ){
		exit(json_encode($finalResponse));
	}

	$db = new Database();

	$db = $db->connect();

	$employee = new Employee($db);

	$employee->employeeNumber = $_GET['employeeNumber'];

	$employee->details();

	if(!$employee->valid){
		$finalResponse['message'] = array("Employee {$employee->employeeNumber} does not exist");
		exit(json_encode($finalResponse));
	}

	$employeeDetailsTxt = "{$employee->employeeNumber} - {$employee->firstName} {$employee->lastName}.";

	//Check if employee has existing Orders/Payments and exit if present
	$employee->countCustomers();

	if($employee->customers){
		$finalResponse['message'] = array("Employee {$employee->employeeNumber} is linked to {$employee->customers} active customers. Please delete these customers and their orders/payments and try again.");
		exit(json_encode($finalResponse));
	}

	// Run DELETE query and exit if it fails
	$employee->delete();
	if(!$employee->valid){
		$finalResponse['message'] = array("Failed to delete employee {$employeeDetailsTxt}");
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully deleted employee {$employeeDetailsTxt}")
	);

	exit(json_encode($finalResponse));
?>
<?php
	require_once("../../config/Database.php");
	require_once("../../models/Employee.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Invalid Request.")
	);

	$db = new Database();
	$db = $db->connect();

	$employee = new Employee($db);

	$params = json_decode(file_get_contents("php://input"), true);
	
	$employee->lastName 	= $params['lastName'];
	$employee->firstName 	= $params['firstName'];
	$employee->extension 	= $params['extension'];
	$employee->email 		= $params['email'];
	$employee->officeCode 	= $params['officeCode'];
	$employee->reportsTo 	= $params['reportsTo'];
	$employee->jobTitle 	= $params['jobTitle'];

	// Check if data is valid and exit if not
	$employee->validate();
	if(!$employee->valid){
		$finalResponse['message'] = $employee->errors;
		exit(json_encode($finalResponse));
	}

	// Check if same name/company exist and exit if duplicate
	$employee->isDuplicate();
	if(!$employee->valid){
		$finalResponse['message'] = $employee->errors;
		exit(json_encode($finalResponse));
	}

	// Check if selected sales rep exists and exit if false
	$employee->officeExists();
	if(!$employee->valid){
		$finalResponse['message'] = $employee->errors;
		exit(json_encode($finalResponse));
	}


	// Check if reportTo employee exists and exit if false
	$employee->reportingToExists();
	if(!$employee->valid){
		$finalResponse['message'] = $employee->errors;
		exit(json_encode($finalResponse));
	}

	// Run INSERT query and exit if it fails
	$employee->create();
	if(!$employee->valid){
		$finalResponse['message'] = $employee->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully created new employee.  {$employee->employeeNumber} - {$employee->firstName} {$employee->lastName}.")
	);

	exit(json_encode($finalResponse));
?>
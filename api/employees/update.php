<?php
	require("../../config/Database.php");
	require("../../models/Employee.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$db = new Database();
	$db = $db->connect();

	$params = json_decode(file_get_contents("php://input"), true);

	$employee = new Employee($db);
	$employee->employeeNumber = $params['employeeNumber'];

	// Check if Employee Exists and exit if not
	$employee->details();
	if(!$employee->valid){
		$finalResponse['message'] = "Employee {$employee->employeeNumber} does not exist";
		exit(json_encode($finalResponse));
	}
	
	$employee->employeeNumber	= $params['employeeNumber'];
	$employee->firstName		= $params['firstName'];
	$employee->lastName			= $params['lastName'];
	$employee->extension		= $params['extension'];
	$employee->email			= $params['email'];
	$employee->officeCode		= $params['officeCode'];
	$employee->reportsTo		= $params['reportsTo'];
	$employee->jobTitle			= $params['jobTitle'];

	$employeeDetailsTxt = "{$employee->employeeNumber} - {$employee->firstName} {$employee->lastName}.";

	$employee->errors[] = "Cannot update details of employee $employeeDetailsTxt";

	// Check if data is valid and exit if not
	$employee->validate();
	if(!$employee->valid){
		$finalResponse['message'] = $employee->errors;
		exit(json_encode($finalResponse));
	}

	// Run update query and exit if it fails
	$employee->update();
	if(!$employee->valid){
		$finalResponse['message'] = $employee->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully updated employee $employeeDetailsTxt"
	);

	exit(json_encode($finalResponse));
?>
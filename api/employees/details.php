<?php
	require("../../config/Database.php");
	require("../../models/Employee.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
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
		$finalResponse['message'] = "Cannot retrieve details for the employee number {$_GET['employeeNumber']}";
		exit(json_encode($finalResponse));
	}

	$employeeDetails = array(
		"employeeNumber"	=> $employee->employeeNumber,
		"firstName"			=> $employee->firstName,
		"lastName"			=> $employee->lastName,
		"extension"			=> $employee->extension,
		"email"				=> $employee->email,
		"officeCode"		=> $employee->officeCode,
		"reportsTo"			=> $employee->reportsTo,
		"jobTitle"			=> $employee->jobTitle
	);

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully retrieved employee details of {$_GET['employeeNumber']}.",
		"result"	=> $employeeDetails
	);

	exit(json_encode($finalResponse));
?>
<?php
	require("../../config/Database.php");
	require("../../models/Employee.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Failed to retrieve list."
	);
	
	$db = new Database();

	$db = $db->connect();

	$employee = new Employee($db);

	$employeesDataset =  $employee->list();

	if($employeesDataset->rowCount()==0){
		exit(json_encode($finalResponse));
	}

	$employeesList = array();

	$i = 0;

	while($employeeDetails = $employeesDataset->fetch(PDO::FETCH_ASSOC)){
		extract($employeeDetails);
		$i++;
		array_push(
			$employeesList,
			array(
				"employeeNumber"	=> $employeeNumber,
				"firstName"			=> $firstName,
				"lastName"			=> $lastName,
				"extension"			=> $extension,
				"email"				=> $email,
				"officeCode"		=> $officeCode,
				"reportsTo"			=> $reportsTo,
				"jobTitle"			=> $jobTitle
			)
		);
	}
	
	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Retrieved ".sizeof($employeesList)." employees.",
		"result"	=> $employeesList
	);

	echo(json_encode($finalResponse));
?>
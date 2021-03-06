<?php
	require_once("../../config/Database.php");
	require_once("../../models/Employee.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Failed to retrieve list.")
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

	while($details = $employeesDataset->fetch(PDO::FETCH_ASSOC)){
		extract($details);
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
		"message"	=> array("Retrieved ".sizeof($employeesList)." employees."),
		"result"	=> $employeesList
	);

	echo(json_encode($finalResponse));
?>
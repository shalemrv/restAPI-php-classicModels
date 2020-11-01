<?php
	require_once("../../config/Database.php");
	require_once("../../models/Customer.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Failed to retrieve list."
	);
	
	$db = new Database();

	$db = $db->connect();

	$customer = new Customer($db);

	$customersDataset =  $customer->list();

	if($customersDataset->rowCount()==0){
		exit(json_encode($finalResponse));
	}

	$customersList = array();

	$i = 0;

	while($details = $customersDataset->fetch(PDO::FETCH_ASSOC)){
		extract($details);
		$i++;
		array_push(
			$customersList,
			array(
				"customerNumber"			=> intval($customerNumber),
				"customerName"				=> $customerName,
				"contactLastName"			=> $contactLastName,
				"contactFirstName"			=> $contactFirstName,
				"phone"						=> $phone,
				"addressLine1"				=> $addressLine1,
				"addressLine2"				=> $addressLine2,
				"city"						=> $city,
				"state"						=> $state,
				"postalCode"				=> $postalCode,
				"country"					=> $country,
				"salesRepEmployeeNumber"	=> intval($salesRepEmployeeNumber),
				"salesRepEmployeeName"		=> "$firstName $lastName",
				"creditLimit"				=> floatval($creditLimit)
			)
		);
	}
	
	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Retrieved ".sizeof($customersList)." customers.",
		"result"	=> $customersList
	);

	echo(json_encode($finalResponse));
	echo json_last_error_msg(); 
?>
<?php
	require("../../config/Database.php");
	require("../../models/Office.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Failed to retrieve list."
	);
	
	$db = new Database();

	$db = $db->connect();

	$office = new Office($db);

	$officesDataset =  $office->list();

	if($officesDataset->rowCount()==0){
		exit(json_encode($finalResponse));
	}

	$officesList = array();

	$i = 0;

	while($details = $officesDataset->fetch(PDO::FETCH_ASSOC)){
		extract($details);
		$i++;
		array_push(
			$officesList,
			array(
				"officeCode"	=> $officeCode,
				"city"			=> $city,
				"phone"			=> $phone,
				"addressLine1"	=> $addressLine1,
				"addressLine2"	=> $addressLine2,
				"state"			=> $state,
				"country"		=> $country,
				"postalCode"	=> $postalCode,
				"territory"		=> $territory
			)
		);
	}
	
	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Retrieved ".sizeof($officesList)." offices.",
		"result"	=> $officesList
	);

	echo(json_encode($finalResponse));
?>
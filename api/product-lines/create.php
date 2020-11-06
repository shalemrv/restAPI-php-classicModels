<?php
	require_once("../../config/Database.php");
	require_once("../../models/ProductLine.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Invalid Request.")
	);

	$db = new Database();
	$db = $db->connect();

	$productLine = new ProductLine($db);

	$params = json_decode(file_get_contents("php://input"), true);
	
	$productLine->productLine		= $params['productLine'];
	$productLine->textDescription	= $params['textDescription'];
	$productLine->htmlDescription	= $params['htmlDescription'];

	// Check if data is valid and exit if not
	$productLine->validate();
	if(!$productLine->valid){
		$finalResponse['message'] = $productLine->errors;
		exit(json_encode($finalResponse));
	}

	// Check if same name/company exist and exit if duplicate
	$productLine->isDuplicate();
	if(!$productLine->valid){
		$finalResponse['message'] = $productLine->errors;
		exit(json_encode($finalResponse));
	}

	// Run INSERT query and exit if it fails
	$productLine->create();
	if(!$productLine->valid){
		$finalResponse['message'] = $productLine->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully created new Product Line {$productLine->productLine}.")
	);

	exit(json_encode($finalResponse));
?>
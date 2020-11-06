<?php
	require_once("../../config/Database.php");
	require_once("../../models/ProductLine.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Invalid Request.")
	);

	$db = new Database();
	$db = $db->connect();

	$params = json_decode(file_get_contents("php://input"), true);

	$productLine = new ProductLine($db);

	$productLine->productLine		= $params['productLine'];
	
	// Check if Product line Exists and exit if not
	$productLine->details();
	if(!$productLine->valid){
		$finalResponse['message'] = array("Product Line {$productLine->productLine} does not exist.");
		exit(json_encode($finalResponse));
	}
	$productLine->errors[] = "Cannot update details of product line {$productLine->productLine}";

	//Assign updated values
	$productLine->textDescription	= $params['textDescription'];
	$productLine->htmlDescription	= $params['htmlDescription'];

	// Check if data is valid and exit if not
	$productLine->validate();
	if(!$productLine->valid){
		$finalResponse['message'] = $productLine->errors;
		exit(json_encode($finalResponse));
	}

	// Run update query and exit if it fails
	$productLine->update();
	if(!$productLine->valid){
		$finalResponse['message'] = $productLine->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully updated product line {$productLine->productLine}.")
	);

	exit(json_encode($finalResponse));
?>
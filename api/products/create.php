<?php
	require("../../config/Database.php");
	require("../../models/Product.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$db = new Database();
	$db = $db->connect();

	$product = new Product($db);

	$params = json_decode(file_get_contents("php://input"), true);
	
	$product->productCode 			= $params['productCode'];
	$product->productName 			= $params['productName'];
	$product->productLine 			= $params['productLine'];
	$product->productScale 			= $params['productScale'];
	$product->productVendor 		= $params['productVendor'];
	$product->productDescription 	= $params['productDescription'];
	$product->quantityInStock 		= $params['quantityInStock'];
	$product->buyPrice 				= $params['buyPrice'];
	$product->MSRP 					= $params['MSRP'];

	// Check if data is valid and exit if not
	$product->validate();
	if(!$product->valid){
		$finalResponse['message'] = $product->errors;
		exit(json_encode($finalResponse));
	}

	// Check if same name/company exist and exit if duplicate
	$product->isDuplicate();
	if(!$product->valid){
		$finalResponse['message'] = $product->errors;
		exit(json_encode($finalResponse));
	}

	// Check if selected product line exists and exit if false
	$product->productLineExists();
	if(!$product->valid){
		$finalResponse['message'] = $product->errors;
		exit(json_encode($finalResponse));
	}

	// Run INSERT query and exit if it fails
	$product->create();
	if(!$product->valid){
		$finalResponse['message'] = $product->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully created new product {$product->productName}."
	);

	exit(json_encode($finalResponse));
?>
<?php
	require_once("../../config/Database.php");
	require_once("../../models/Product.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	$db = new Database();
	$db = $db->connect();

	$params = json_decode(file_get_contents("php://input"), true);

	$product = new Product($db);
	$product->productCode = $params['productCode'];

	// Check if Customer Exists and exit if not
	$product->details();
	if(!$product->valid){
		$finalResponse['message'] = "Product {$product->productCode} does not exist";
		exit(json_encode($finalResponse));
	}

	$product->productName 			= $params['productName'];
	$product->productLine 			= $params['productLine'];
	$product->productScale 			= $params['productScale'];
	$product->productVendor 		= $params['productVendor'];
	$product->productDescription 	= $params['productDescription'];
	$product->quantityInStock 		= $params['quantityInStock'];
	$product->buyPrice 				= $params['buyPrice'];
	$product->MSRP 					= $params['MSRP'];

	$product->errors[] = "Cannot update details of product - {$product->productCode}.";

	// Check if data is valid and exit if not
	$product->validate();
	if(!$product->valid){
		$finalResponse['message'] = $product->errors;
		exit(json_encode($finalResponse));
	}

	// Run update query and exit if it fails
	$product->update();
	if(!$product->valid){
		$finalResponse['message'] = $product->errors;
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully updated product - {$product->productCode}."
	);

	exit(json_encode($finalResponse));
?>
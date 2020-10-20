<?php
	require("../../config/Database.php");
	require("../../models/Product.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	if( (!isset($_GET['productCode'])) || (strlen($_GET['productCode'])==0) ){
		exit(json_encode($finalResponse));
	}

	$db = new Database();

	$db = $db->connect();

	$product = new Product($db);

	$product->productCode = $_GET['productCode'];

	$product->details();
	if(!$product->valid){
		$finalResponse['message'] = "Product {$product->productCode} does not exist";
		exit(json_encode($finalResponse));
	}

	//Check if product has existing Orders and exit if present
	$product->countOrders();

	if($product->orders){
		$finalResponse['message'] = "Product is present in {$product->orders} orders. Please delete these orders and try again.";
		exit(json_encode($finalResponse));
	}

	// Run DELETE query and exit if it fails
	$product->delete();
	if(!$product->valid){
		$finalResponse['message'] = "Failed to delete product - {$product->productCode}";
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully deleted product - {$product->productCode}"
	);

	exit(json_encode($finalResponse));
?>
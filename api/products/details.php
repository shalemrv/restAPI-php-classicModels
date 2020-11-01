<?php
	require_once("../../config/Database.php");
	require_once("../../models/Product.php");

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
		$finalResponse['message'] = "Cannot retrieve details for the product code {$_GET['productCode']}";
		exit(json_encode($finalResponse));
	}

	$productDetails = array(
		"productCode"			=> $product->productCode,
		"productName"			=> $product->productName,
		"productLine"			=> $product->productLine,
		"productScale"			=> $product->productScale,
		"productVendor"			=> $product->productVendor,
		"productDescription"	=> $product->productDescription,
		"quantityInStock"		=> $product->quantityInStock,
		"buyPrice"				=> $product->buyPrice,
		"MSRP"					=> $product->MSRP
	);

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully retrieved product details of {$_GET['productCode']}.",
		"result"	=> $productDetails
	);

	exit(json_encode($finalResponse));
?>
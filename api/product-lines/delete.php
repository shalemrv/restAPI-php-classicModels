<?php
	require("../../config/Database.php");
	require("../../models/ProductLine.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> "Invalid Request."
	);

	if( (!isset($_GET['productLine'])) || (strlen($_GET['productLine'])<=0) ){
		exit(json_encode($finalResponse));
	}

	$db = new Database();

	$db = $db->connect();

	$productLine = new ProductLine($db);

	$productLine->productLine = $_GET['productLine'];

	$productLine->details();
	if(!$productLine->valid){
		$finalResponse['message'] = "Product Line {$productLine->productLine} does not exist";
		exit(json_encode($finalResponse));
	}

	//Check if Product Line has existing products and exit if present
	$productLine->countProducts();
	if($productLine->products){
		$finalResponse['message'] = "Product Line {$productLine->productLine} has {$productLine->products} active products. Please delete these products and try again.";

		exit(json_encode($finalResponse));
	}

	// Run DELETE query and exit if it fails
	$productLine->delete();
	if(!$productLine->valid){
		$finalResponse['message'] = "Failed to delete product line {$productLine->productLine}";
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> "Successfully deleted product line {$productLine->productLine}"
	);

	exit(json_encode($finalResponse));
?>
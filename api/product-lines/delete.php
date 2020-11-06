<?php
	require_once("../../config/Database.php");
	require_once("../../models/ProductLine.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Invalid Request.")
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
		$finalResponse['message'] = array("Product Line {$productLine->productLine} does not exist");
		exit(json_encode($finalResponse));
	}

	//Check if Product Line has existing products and exit if present
	$productLine->countProducts();
	if($productLine->products){
		$finalResponse['message'] = array("Product Line {$productLine->productLine} has {$productLine->products} active products. Please delete these products and try again.");

		exit(json_encode($finalResponse));
	}

	// Run DELETE query and exit if it fails
	$productLine->delete();
	if(!$productLine->valid){
		$finalResponse['message'] = array("Failed to delete product line {$productLine->productLine}");
		exit(json_encode($finalResponse));
	}

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully deleted product line {$productLine->productLine}")
	);

	exit(json_encode($finalResponse));
?>
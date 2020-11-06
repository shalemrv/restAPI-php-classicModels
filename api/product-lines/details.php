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
		$finalResponse['message'] = array("Cannot retrieve details for the product line {$_GET['productLine']}");
		exit(json_encode($finalResponse));
	}

	$productLineDetails = array(
		"productLine"		=> $productLine->productLine,
		"textDescription"	=> $productLine->textDescription,
		"htmlDescription"	=> $productLine->htmlDescription
	);

	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Successfully retrieved product line details of {$_GET['productLine']}."),
		"result"	=> $productLineDetails
	);

	exit(json_encode($finalResponse));
?>
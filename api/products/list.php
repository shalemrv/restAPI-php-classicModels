<?php
	require_once("../../config/Database.php");
	require_once("../../models/Product.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Failed to retrieve list.")
	);
	
	$db = new Database();

	$db = $db->connect();

	$product = new Product($db);

	$productsDataset =  $product->list();

	if($productsDataset->rowCount()==0){
		exit(json_encode($finalResponse));
	}

	$productsList = array();

	$i = 0;

	while($details = $productsDataset->fetch(PDO::FETCH_ASSOC)){
		extract($details);
		$i++;
		array_push(
			$productsList,
			array(
				"productCode"			=> $productCode,
				"productName"			=> $productName,
				"productLine"			=> $productLine,
				"productScale"			=> $productScale,
				"productVendor"			=> $productVendor,
				"productDescription"	=> $productDescription,
				"quantityInStock"		=> $quantityInStock,
				"buyPrice"				=> floatval($buyPrice),
				"MSRP"					=> floatval($MSRP)
			)
		);
	}
	
	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Retrieved ".sizeof($productsList)." products."),
		"result"	=> $productsList
	);

	echo(json_encode($finalResponse));
?>
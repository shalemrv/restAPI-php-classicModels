<?php
	require_once("../../config/Database.php");
	require_once("../../models/ProductLine.php");

	$finalResponse = array(
		"complete"	=> false,
		"message"	=> array("Failed to retrieve list.")
	);
	
	$db = new Database();

	$db = $db->connect();

	$productLine = new ProductLine($db);

	$productLinesDataset =  $productLine->list();

	if($productLinesDataset->rowCount()==0){
		exit(json_encode($finalResponse));
	}

	$productLinesList = array();

	$i = 0;

	while($details = $productLinesDataset->fetch(PDO::FETCH_ASSOC)){
		extract($details);
		$i++;
		array_push(
			$productLinesList,
			array(
				"productLine"		=> $productLine,
				"textDescription"	=> $textDescription,
				"htmlDescription"	=> $htmlDescription
			)
		);
	}
	
	$finalResponse = array(
		"complete"	=> true,
		"message"	=> array("Retrieved ".sizeof($productLinesList)." product lines."),
		"result"	=> $productLinesList
	);

	echo(json_encode($finalResponse));
?>
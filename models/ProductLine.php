<?php
	require_once("../../config/validate.php");

	class ProductLine{

		use Validate;

		private $conn;
		private $table = "productlines";

		public $valid = false;
		public $errors = array();

		public $products;

		public $productLine;
		public $textDescription;
		public $htmlDescription;

		function __construct($db){
			$this->conn = $db;
		}

		public function validate(){
			$this->valid = true;

			$this->productLine		= $this->cleanse("alphaNumSpace", $this->productLine);
			$this->textDescription	= $this->cleanse("alphaNumSpace", $this->textDescription);
			$this->htmlDescription	= $this->cleanse("sentence", $this->htmlDescription);

			// PRODUCT LINE VALID
			if( !$this->valid("min-char", $this->productLine, 5) ){
				$this->errors[] = "Product line has to be at least 5 characters long.";
				$this->valid = false;	
			}

			// TEXT DESCRIPTION LINE VALID
			if( !$this->valid("min-char", $this->textDescription, 5) ){
				$this->errors[] = "Text Description line has to be at least 5 characters long.";
				$this->valid = false;	
			}

			// HTML DESCRIPTION LINE VALID
			if( !$this->valid("min-char", $this->htmlDescription, 5) ){
				$this->errors[] = "HTML Description ---{$this->htmlDescription}--- line has to be at least 5 characters long.";
				$this->valid = false;	
			}
		}

		public function isDuplicate(){
			$this->valid = true;

			//Same company name exists
			$sameProductLine = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					productLine='{$this->productLine}'
				;
			";

			$sameProductLine = $this->conn->prepare($sameProductLine);

			$sameProductLine->execute();

			if($sameProductLine->rowCount()){
				$this->errors[] = "Same product line already exists. Please update Product line.";
				$this->valid = false;
			}
		}

		public function countProducts(){
			$this->products = "
				SELECT
					COUNT(productCode) as value
				FROM
					products
				WHERE
					productLine='{$this->productLine}'
			";

			$this->products = $this->conn->prepare($this->products);

			$this->products->execute();

			$this->products = $this->products->fetch(PDO::FETCH_ASSOC);
			
			$this->products = intval($this->products['value']);
		}

		public function list(){
			$productLinesDataset = "
				SELECT
					*
				FROM
					{$this->table}
				ORDER BY
					productLine
				;
			";

			$productLinesDataset = $this->conn->prepare($productLinesDataset);
			$productLinesDataset->execute();

			return $productLinesDataset;
		}

		public function details(){
			$this->valid = false;

			$productLineDetails = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					productLine=?
				;
			";

			$productLineDetails = $this->conn->prepare($productLineDetails);

			$productLineDetails->bindParam(1, $this->productLine);

			$productLineDetails->execute();

			if($productLineDetails->rowCount()==0){
				return;
			}
			
			$this->valid = true;

			$productLineDetails = $productLineDetails->fetch(PDO::FETCH_ASSOC);

			extract($productLineDetails);

			$this->textDescription	= $textDescription;
			$this->htmlDescription	= $htmlDescription;
		}
		
		public function create(){

			$this->valid = false;

			$insertRes = "
				INSERT INTO
					{$this->table}
				SET
					productLine=:productLine,
					textDescription=:textDescription,
					htmlDescription=:htmlDescription
				;
			";

			$insertRes = $this->conn->prepare($insertRes);

			$insertRes->bindParam(":productLine",		$this->productLine);
			$insertRes->bindParam(":textDescription",	$this->textDescription);
			$insertRes->bindParam(":htmlDescription",	$this->htmlDescription);

			if($insertRes->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $insertRes->error;
		}

		public function update(){

			$this->valid = false;

			$updateRes = "
				UPDATE
					{$this->table}
				SET
					textDescription=:textDescription,
					htmlDescription=:htmlDescription
				WHERE
					productLine=:productLine
				;
			";

			$updateRes = $this->conn->prepare($updateRes);

			$updateRes->bindParam(":productLine",		$this->productLine);
			$updateRes->bindParam(":textDescription",	$this->textDescription);
			$updateRes->bindParam(":htmlDescription",	$this->htmlDescription);

			if($updateRes->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $updateRes->error;
		}		

		public function delete(){
			$this->valid = false;

			$deleteResult = "
				DELETE FROM
					{$this->table}
				WHERE
					productLine=?
				;
			";

			$deleteResult = $this->conn->prepare($deleteResult);

			$deleteResult->bindParam(1, $this->productLine);

			$deleteResult->execute();

			if($deleteResult->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $deleteResult->error;
		}
	}
?>
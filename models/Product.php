<?php
	
	class Product{

		use Validate;

		private $conn;
		private $table = "products";

		public $valid = false;
		public $errors = array();

		public $orders;

		public $productCode;
		public $productName;
		public $productLine;
		public $productScale;
		public $productVendor;
		public $productDescription;
		public $quantityInStock;
		public $buyPrice;
		public $MSRP;
		
		function __construct($db){
			$this->conn = $db;
		}

		public function validate(){
			$this->valid = true;

			$this->productCode 			= $this->cleanse("alphaNumUnd", $this->productCode);
			$this->productName 			= $this->cleanse("alphaNumSpace", $this->productName);
			$this->productLine 			= $this->cleanse("alphaNumSpace", $this->productLine);
			$this->productScale 		= $this->cleanse("ratio", $this->productScale);
			$this->productVendor 		= $this->cleanse("alphaNumSpace", $this->productVendor);
			$this->productDescription 	= $this->cleanse("removeHtmlTags", $this->productDescription);
			$this->quantityInStock 		= $this->cleanse("num", $this->quantityInStock);
			$this->buyPrice 			= floatval($this->buyPrice);
			$this->MSRP 				= floatval($this->MSRP);

			// PRODUCT CODE VALID
			if( !$this->valid("min-char", $this->productCode, 5) ){
				$this->errors[] = "Product Code has to be at least 5 characters long.";
				$this->valid = false;	
			}

			// PRODUCT NAME VALID
			if( !$this->valid("min-char", $this->productName, 5) ){
				$this->errors[] = "Product name has to be at least 5 characters long.";
				$this->valid = false;	
			}

			// PRODUCT SCALE VALID
			if( !$this->valid("min-char", $this->productScale, 3) ){
				$this->errors[] = "Product Scale has to be at least 3 charaters long.";
				$this->valid = false;	
			}

			// PRODUCT VENDOR VALID
			if( !$this->valid("min-char", $this->productVendor, 6) ){
				$this->errors[] = "Product vendor has to be at least 6 characters long.";
				$this->valid = false;	
			}

			// PRODUCT DESCRIPTION VALID
			if( !$this->valid("min-char", $this->productDescription, 6) ){
				$this->errors[] = "Product description has to be at least 6 characters long.";
				$this->valid = false;
			}

			// QUAMTITY VALID
			if( $this->quantityInStock < 0 ){
				$this->errors[] = "Quantity in stock cannot be less than 0.";
				$this->valid = false;
			}

			// BUY PRICE VALID
			if( $this->buyPrice <= 0 ){
				$this->errors[] = "Buying Price has to be more than 0.";
				$this->valid = false;
			}

			// MSRP VALID
			if( $this->MSRP <= 0 ){
				$this->errors[] = "MSRP has to be more than 0.";
				$this->valid = false;
			}
		}

		public function isDuplicate(){
			$this->valid = true;

			//Same company name exists
			$sameProduct = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					productCode='{$this->productCode}'
				;
			";

			$sameProduct = $this->conn->prepare($sameProduct);

			$sameProduct->execute();

			if($sameProduct->rowCount()){
				$this->errors[] = "Product with same name already exists. Please update Product name.";
				$this->valid = false;
			}
		}

		public function productLineExists(){
			$this->valid = false;

			//New sales rep number is valid name exists
			$productLinesDataset = "
				SELECT
					*
				FROM
					productlines
				WHERE
					productLine='{$this->productLine}'
				;
			";

			$productLinesDataset = $this->conn->prepare($productLinesDataset);

			$productLinesDataset->execute();

			if($productLinesDataset->rowCount()){
				$this->valid = true;
				return;
			}	
			
			$this->errors[] = "Invalid Product Line.";
		}

		public function countOrders(){
			$this->orders = "
				SELECT
					COUNT(DISTINCT orderNumber) as value
				FROM
					orderdetails
				WHERE
					productCode='{$this->productCode}'
			";

			$this->orders = $this->conn->prepare($this->orders);

			$this->orders->execute();

			$this->orders = $this->orders->fetch(PDO::FETCH_ASSOC);
			
			$this->orders = intval($this->orders['value']);
		}

		public function countRecords(){
			$recordsCount = "
				SELECT
					COUNT(*) as value
				FROM
					{$this->table}
				;
			";

			$recordsCount = $this->conn->prepare($recordsCount);

			$recordsCount->execute();

			$recordsCount = $recordsCount->fetch(PDO::FETCH_ASSOC);
			
			$recordsCount = intval($recordsCount['value']);

			return $recordsCount;
		}

		public function list(){
			$productsDataset = "SELECT * FROM {$this->table};";

			$productsDataset = $this->conn->prepare($productsDataset);
			$productsDataset->execute();

			return $productsDataset;
		}

		public function details(){
			$productDetails = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					productCode=?
				;
			";

			$productDetails = $this->conn->prepare($productDetails);

			$productDetails->bindParam(1, $this->productCode);

			$productDetails->execute();

			if($productDetails->rowCount()==0){
				return;
			}
			
			$this->valid = true;

			$productDetails = $productDetails->fetch(PDO::FETCH_ASSOC);

			extract($productDetails);

			$this->productName			= $productName;
			$this->productLine			= $productLine;
			$this->productScale			= $productScale;
			$this->productVendor		= $productVendor;
			$this->productDescription	= $productDescription;
			$this->quantityInStock		= $quantityInStock;
			$this->buyPrice				= floatval($buyPrice);
			$this->MSRP					= floatval($MSRP);
		}
		
		public function create(){

			$this->valid = false;

			$insertRes = "
				INSERT INTO
					{$this->table}
				SET
					productCode=:productCode,
					productName=:productName,
					productLine=:productLine,
					productScale=:productScale,
					productVendor=:productVendor,
					productDescription=:productDescription,
					quantityInStock=:quantityInStock,
					buyPrice=:buyPrice,
					MSRP=:MSRP
				;
			";

			$insertRes = $this->conn->prepare($insertRes);

			$insertRes->bindParam(":productCode", 			$this->productCode);
			$insertRes->bindParam(":productName", 			$this->productName);
			$insertRes->bindParam(":productLine",			$this->productLine);
			$insertRes->bindParam(":productScale",			$this->productScale);
			$insertRes->bindParam(":productVendor",			$this->productVendor);
			$insertRes->bindParam(":productDescription",	$this->productDescription);
			$insertRes->bindParam(":quantityInStock",		$this->quantityInStock);
			$insertRes->bindParam(":buyPrice",				$this->buyPrice);
			$insertRes->bindParam(":MSRP",					$this->MSRP);

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
					productName=:productName,
					productLine=:productLine,
					productScale=:productScale,
					productVendor=:productVendor,
					productDescription=:productDescription,
					quantityInStock=:quantityInStock,
					buyPrice=:buyPrice,
					MSRP=:MSRP
				WHERE
					productCode=:productCode
				;
			";

			$updateRes = $this->conn->prepare($updateRes);

			$updateRes->bindParam(":productCode", 			$this->productCode);
			$updateRes->bindParam(":productName", 			$this->productName);
			$updateRes->bindParam(":productLine",			$this->productLine);
			$updateRes->bindParam(":productScale",			$this->productScale);
			$updateRes->bindParam(":productVendor",			$this->productVendor);
			$updateRes->bindParam(":productDescription",	$this->productDescription);
			$updateRes->bindParam(":quantityInStock",		$this->quantityInStock);
			$updateRes->bindParam(":buyPrice",				$this->buyPrice);
			$updateRes->bindParam(":MSRP",					$this->MSRP);

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
					productCode=?
				;
			";

			$deleteResult = $this->conn->prepare($deleteResult);

			$deleteResult->bindParam(1, $this->productCode);

			$deleteResult->execute();

			if($deleteResult->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $deleteResult->error;
		}
	}
?>
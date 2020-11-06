<?php
	
	class OrderDetails{

		use Validate;

		private $conn;
		private $table = "orderdetails";

		public $valid = false;
		public $errors = array();

		public $payments;

		public $orderNumber;
		public $productCode;
		public $quantityOrdered;
		public $priceEach;
		public $orderLineNumber;

		public $productName;
		public $productLine;
		
		function __construct($db){
			$this->conn = $db;
		}

		public function validate(){
			$this->valid = true;

			$this->orderNumber		= intval($this->orderNumber);
			$this->productCode		= $this->cleanse("alphaNumUnd", $this->productCode);
			$this->quantityOrdered	= intval($this->quantityOrdered);
			$this->priceEach		= floatval($this->priceEach);
			$this->orderLineNumber	= intval($this->orderLineNumber);

			// UNIT PRICE VALID
			if($this->priceEach < 1){
				$this->errors[] = "Unit Price has to be greater than 1.";
				$this->valid = false;	
			}

			// QUANTITY ORDERED VALID
			if($this->quantityOrdered < 1){
				$this->errors[] = "Quantity Ordered has to be greater than 0.";
				$this->valid = false;	
			}
		}

		public function isDuplicate(){
			$this->valid = true;

			//Same company name exists
			$sameOrderItem = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					orderNumber='{$this->orderNumber}'
					AND
					productCode='{$this->productCode}'
				;
			";

			$sameOrderItem = $this->conn->prepare($sameOrderItem);

			$sameOrderItem->execute();

			if($sameOrderItem->rowCount()){
				$this->errors[] = "Product already exists in the order. Please update quantity.";
				$this->valid = false;
			}
		}

		public function orderExists(){
			$this->valid = false;

			//Order number exists
			$ordersDataset = "
				SELECT
					*
				FROM
					orders
				WHERE
					orderNumber='{$this->orderNumber}'
				;
			";

			$ordersDataset = $this->conn->prepare($ordersDataset);

			$ordersDataset->execute();

			if($ordersDataset->rowCount()){
				$this->valid = true;
				return;
			}	
			
			$this->errors[] = "Invalid Order number.";
		}

		public function productExists(){
			$this->valid = false;

			//New sales rep number is valid name exists
			$productsDataset = "
				SELECT
					*
				FROM
					products
				WHERE
					productCode='{$this->productCode}'
				;
			";

			$productsDataset = $this->conn->prepare($productsDataset);

			$productsDataset->execute();

			if($productsDataset->rowCount()){
				$this->valid = true;
				return;
			}	
			
			$this->errors[] = "Invalid Product.";
		}

		public function getOrderLineNumber(){
			$newOrderLineNumber = $this->conn->prepare("
				SELECT
					MAX(productCode) AS currentNumber
				FROM
					{$this->table}
				WHERE
					orderNumber='{$this->orderNumber}'
			");

			$newOrderLineNumber->execute();

			$newOrderLineNumber = $newOrderLineNumber->fetch(PDO::FETCH_ASSOC);

			$newOrderLineNumber = intval($newOrderLineNumber['currentNumber']) + 1;

			return $newOrderLineNumber;
		}

		public function list(){
			$orderDetailsDataset = "
				SELECT
					o.*,
					p.*
				FROM
					{$this->table} o
					INNER JOIN
					products p
					ON
					o.productCode=p.productCode
				WHERE
					o.orderNumber='{$this->orderNumber}'
				ORDER BY
					o.orderLineNumber
				;
			";

			$orderDetailsDataset = $this->conn->prepare($orderDetailsDataset);
			$orderDetailsDataset->execute();

			$orderDetailsList = array();

			while($details = $orderDetailsDataset->fetch(PDO::FETCH_ASSOC)){
				extract($details);

				array_push(
					$orderDetailsList,
					array(
						"productCode"		=> $productCode,
						"productName"		=> $productName,
						"productLine"		=> $productLine,
						"quantityOrdered"	=> $quantityOrdered,
						"priceEach"			=> $priceEach,
						"orderLineNumber"	=> $orderLineNumber
					)
				);
			}

			return $orderDetailsList;
		}

		public function details(){
			$this->valid = false;

			//Same company name exists
			$orderDetails = "
				SELECT
					o.*,
					p.*
				FROM
					{$this->table} o
					INNER JOIN
					products p
					ON
					o.productCode=p.productCode
				WHERE
					o.orderNumber='{$this->orderNumber}'
					AND
					o.productCode='{$this->productCode}'
				;
			";

			$orderDetails = $this->conn->prepare($orderDetails);

			$orderDetails->execute();

			if(!$orderDetails->rowCount()){
				$this->errors[] = "Product doesn't exist in the order.";
				return;
			}
			
			$this->valid = true;

			$orderDetails = $orderDetails->fetch(PDO::FETCH_ASSOC);

			extract($orderDetails);

			$this->productCode		= $productCode;
			$this->quantityOrdered	= intval($quantityOrdered);
			$this->priceEach		= floatval($priceEach);
			$this->orderLineNumber	= intval($orderLineNumber);
			$this->productName		= $productName;
			$this->productLine		= $productLine;
		}
		
		public function create(){

			$this->valid = false;

			$this->orderLineNumber = $this->getOrderLineNumber();

			$insertRes = "
				INSERT INTO
					{$this->table}
				SET
					orderNumber=:orderNumber,
					productCode=:productCode,
					quantityOrdered=:quantityOrdered,
					priceEach=:priceEach,
					orderLineNumber=:orderLineNumber
				;
			";

			$insertRes = $this->conn->prepare($insertRes);			

			$insertRes->bindParam(":orderNumber",		$this->orderNumber);
			$insertRes->bindParam(":productCode",		$this->productCode);
			$insertRes->bindParam(":quantityOrdered",	$this->quantityOrdered);
			$insertRes->bindParam(":priceEach",			$this->priceEach);
			$insertRes->bindParam(":orderLineNumber",	$this->orderLineNumber);


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
					quantityOrdered=:quantityOrdered,
					priceEach=:priceEach
				WHERE
					orderNumber=:orderNumber
					AND
					productCode=:productCode
				;
			";

			$updateRes = $this->conn->prepare($updateRes);

			$updateRes->bindParam(":orderNumber", 		$this->orderNumber);
			$updateRes->bindParam(":productCode",		$this->productCode);
			$updateRes->bindParam(":quantityOrdered",	$this->quantityOrdered);
			$updateRes->bindParam(":priceEach",			$this->priceEach);

			if($updateRes->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $updateRes->error;
		}		

		public function delete(){
			$this->valid = false;

			if(sizeof($this->list())==1){
				$this->errors[] = "Cannot delete the only order details left. Please delete the entire order.";
				return;
			}

			$deleteResult = "
				DELETE FROM
					{$this->table}
				WHERE
					orderNumber=:orderNumber
					AND
					productCode=:productCode
				;
			";

			$deleteResult = $this->conn->prepare($deleteResult);

			$deleteResult->bindParam(":orderNumber", $this->orderNumber);
			$deleteResult->bindParam(":productCode", $this->productCode);

			$deleteResult->execute();

			if(!$deleteResult->execute()){
				$this->errors[] = $deleteResult->error;
				return;
			}

			$this->valid = true;
			
			$updateLaterOrderLineNumbers = $this->conn->prepare("
				UPDATE
					orderdetails
				SET
					orderLineNumber=orderLineNumber-1
				WHERE
					orderNumber=:orderNumber
					AND
					orderLineNumber>:orderLineNumber
				;
			");

			$updateLaterOrderLineNumbers->bindParam(":orderNumber",		$this->orderNumber);
			$updateLaterOrderLineNumbers->bindParam(":orderLineNumber",	$this->orderLineNumber);

			$updateLaterOrderLineNumbers->execute();			
		}
	}
?>
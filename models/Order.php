<?php
	require_once("../../config/validate.php");

	class Order{

		use Validate;

		private $conn;
		private $table = "orders";

		public $valid = false;
		public $errors = array();

		public $orderChildren;

		public $orderNumber;
		public $orderDate;
		public $requiredDate;
		public $shippedDate;
		public $status;
		public $comments;
		public $customerNumber;
		public $customerName;
		
		function __construct($db){
			$this->conn = $db;
		}

		public function validate(){
			$this->valid = true;

			$this->orderNumber 		= intval($this->orderNumber);
			$this->orderDate 		= $this->cleanse("date", $this->orderDate);
			$this->requiredDate 	= $this->cleanse("date", $this->requiredDate);
			$this->shippedDate 		= $this->cleanse("date", $this->shippedDate);
			$this->status 			= $this->cleanse("alphaNumSpace", $this->status);
			$this->comments 		= $this->cleanse("removeHtmlTags", $this->comments);
			$this->customerNumber 	= intval($this->customerNumber);

			// ORDER DATE FORMAT VALID
			if( !$this->valid("date-format", $this->orderDate) ){
				$this->errors[] = "Invalid Order Date. Date format YYYY-MM-DD.";
				$this->valid = false;	
			}
			// ORDER DATE VALID
			if( !$this->valid("date", $this->orderDate) ){
				$this->errors[] = "Invalid Order Date. Date format YYYY-MM-DD.";
				$this->valid = false;	
			}
			// REQUIRED DATE VALID
			if( !$this->valid("date", $this->requiredDate) ){
				$this->errors[] = "Invalid Required Date. Date format YYYY-MM-DD.";
				$this->valid = false;	
			}

			// SHIPPED DATE VALID
			if(strlen($this->shippedDate)){
				if(!$this->valid("date", $this->shippedDate)){
					$this->errors[] = "Invalid Shipped Date. Date format YYYY-MM-DD.";
					$this->valid = false;
				}
			}
			else{
				$this->shippedDate = null;
			}
			// STATUS VALID
			if( !$this->valid("min-char", $this->status, 5) ){
				$this->errors[] = "Status has to be at least 5 characters long.";
				$this->valid = false;	
			}
		}

		public function customerExists(){
			$this->valid = false;

			//New sales rep number is valid name exists
			$customersDataset = "
				SELECT
					*
				FROM
					customers
				WHERE
					customerNumber='{$this->customerNumber}'
				;
			";

			$customersDataset = $this->conn->prepare($customersDataset);

			$customersDataset->execute();

			if($customersDataset->rowCount()){
				$this->valid = true;
				return;
			}	
			
			$this->errors[] = "Invalid Customer. Please select a Customer from the Customers List.";
		}

		public function countOrderChildren(){
			$this->orderChildren = "
				SELECT
					COUNT(orderNumber) as value
				FROM
					orderdetails
				WHERE
					orderNumber={$this->orderNumber}
			";

			$this->orderChildren = $this->conn->prepare($this->orderChildren);

			$this->orderChildren->execute();

			$this->orderChildren = $this->orderChildren->fetch(PDO::FETCH_ASSOC);
			
			$this->orderChildren = intval($this->orderChildren['value']);
		}

		public function list(){
			$ordersDataset = "
				SELECT
					o.*,

					c.customerName
				FROM
					{$this->table} o
					INNER JOIN
					customers c
					ON
					o.customerNumber=c.customerNumber
				ORDER BY
					o.orderNumber DESC
				;
			";

			$ordersDataset = $this->conn->prepare($ordersDataset);
			$ordersDataset->execute();

			return $ordersDataset;
		}

		public function details(){
			$this->valid = false;

			$orderDetails = "
				SELECT
					o.*,

					c.customerName
				FROM
					{$this->table} o
					INNER JOIN
					customers c
					ON
					o.customerNumber=c.customerNumber
				WHERE
					o.orderNumber=?
				;
			";

			$orderDetails = $this->conn->prepare($orderDetails);

			$orderDetails->bindParam(1, $this->orderNumber);

			$orderDetails->execute();

			if($orderDetails->rowCount()==0){
				return;
			}
			
			$this->valid = true;

			$orderDetails = $orderDetails->fetch(PDO::FETCH_ASSOC);

			extract($orderDetails);

			$this->orderNumber		= $orderNumber;
			$this->orderDate		= $orderDate;
			$this->requiredDate		= $requiredDate;
			$this->shippedDate		= $shippedDate;
			$this->status			= $status;
			$this->comments			= $comments;
			$this->customerNumber	= $customerNumber;
			$this->customerName		= $customerName;
		}
		
		public function create(){

			//Get Latest Order number
			$maxNumber = "
				SELECT
					MAX(orderNumber) AS value
				FROM
					{$this->table}
				;
			";

			$maxNumber = $this->conn->prepare($maxNumber);

			$maxNumber->execute();

			$maxNumber = $maxNumber->fetch(PDO::FETCH_ASSOC);

			$this->orderNumber = intval($maxNumber['value']) + 1;

			$this->valid = false;

			$insertRes = "
				INSERT INTO
					{$this->table}
				SET
					orderNumber=:orderNumber,
					orderDate=:orderDate,
					requiredDate=:requiredDate,
					shippedDate=:shippedDate,
					status=:status,
					comments=:comments,
					customerNumber=:customerNumber
				;
			";

			$insertRes = $this->conn->prepare($insertRes);

			$insertRes->bindParam(":orderNumber", 		$this->orderNumber);
			$insertRes->bindParam(":orderDate", 		$this->orderDate);
			$insertRes->bindParam(":requiredDate",		$this->requiredDate);
			$insertRes->bindParam(":shippedDate",		$this->shippedDate);
			$insertRes->bindParam(":status",			$this->status);
			$insertRes->bindParam(":comments",			$this->comments);
			$insertRes->bindParam(":customerNumber",	$this->customerNumber);

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
					orderDate=:orderDate,
					requiredDate=:requiredDate,
					shippedDate=:shippedDate,
					status=:status,
					comments=:comments,
					customerNumber=:customerNumber
				WHERE
					orderNumber=:orderNumber
				;
			";

			$updateRes = $this->conn->prepare($updateRes);

			$updateRes->bindParam(":orderNumber", 		$this->orderNumber);
			$updateRes->bindParam(":orderDate", 		$this->orderDate);
			$updateRes->bindParam(":requiredDate",		$this->requiredDate);
			$updateRes->bindParam(":shippedDate",		$this->shippedDate);
			$updateRes->bindParam(":status",			$this->status);
			$updateRes->bindParam(":comments",			$this->comments);
			$updateRes->bindParam(":customerNumber",	$this->customerNumber);

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
					orderdetails
				WHERE
					orderNumber=?
				;
			";

			$deleteResult = $this->conn->prepare($deleteResult);

			$deleteResult->bindParam(1, $this->orderNumber);

			if(!$deleteResult->execute()){
				$this->errors[] = "Failed to delete order's child items.";
				$this->errors[] = $deleteResult->error;
				return;
			}

			$deleteResult = "
				DELETE FROM
					{$this->table}
				WHERE
					orderNumber=?
				;
			";

			$deleteResult = $this->conn->prepare($deleteResult);

			$deleteResult->bindParam(1, $this->orderNumber);

			if($deleteResult->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $deleteResult->error;
		}
	}
?>
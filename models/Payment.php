<?php
	/**
	* Customers Modification Class
	*/
	class Payment{

		private $conn;
		private $table = "payments";

		public $valid = false;
		public $errors = array();

		public $payments;

		public $customerNumber;
		public $customerName;
		public $checkNumber;
		public $newCheckNumber;
		public $paymentDate;
		public $amount;
		
		function __construct($db){
			$this->conn = $db;
		}

		public function validate(){
			$this->valid = true;

			// CUSTOMER NUMBER VALID
			if($this->customerNumber<1){
				$this->errors[] = "Amount has to be greater than 0.";
				$this->valid = false;	
			}

			// FIRST NAME VALID
			if(strlen(str_replace(" ", "", $this->checkNumber))<2){
				$this->errors[] = "Check number to be at least 2 characters long.";
				$this->valid = false;	
			}

			// LAST NAME VALID
			if( (strlen(str_replace(" ", "", $this->paymentDate))<10) || (strlen($this->paymentDate)!=10) ){
				$this->errors[] = "Payment date has to be exactly 10 characters long. YYYY-mm-dd E.g.2020-12-31";
				$this->valid = false;
			}

			// PHONE NUMBER VALID
			if($this->amount<1){
				$this->errors[] = "Amount has to be greater than or equal to 1.";
				$this->valid = false;	
			}
		}

		public function isDuplicate(){
			$this->valid = true;

			//Same company name exists
			$samePayments = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					customerNumber='{$this->customerNumber}'
					AND
					checkNumber='{$this->checkNumber}'
				;
			";

			$samePayments = $this->conn->prepare($samePayments);

			$samePayments->execute();

			if($samePayments->rowCount()){
				$this->errors[] = "Payment with same check number already exists. Please update check number.";
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
			
			$this->errors[] = "Invalid sales representative.";
		}

		public function list(){
			$paymentsDataset = "
				SELECT
					p.customerNumber,
					c.customerName,
					p.checkNumber,
					p.paymentDate,
					p.amount
				FROM
					{$this->table} p
					INNER JOIN
					customers c
					ON
					c.customerNumber=p.customerNumber
				ORDER BY
					paymentDate DESC
				;
			";

			$paymentsDataset = $this->conn->prepare($paymentsDataset);
			$paymentsDataset->execute();

			return $paymentsDataset;
		}

		public function details(){
			$paymentDetails = "
				SELECT
					p.customerNumber,
					c.customerName,
					p.checkNumber,
					p.paymentDate,
					p.amount
				FROM
					{$this->table} p
					INNER JOIN
					customers c
					ON
					c.customerNumber=p.customerNumber
				WHERE
					p.customerNumber=?
					AND
					p.checkNumber=?
				;
			";

			$paymentDetails = $this->conn->prepare($paymentDetails);

			$paymentDetails->bindParam(1, $this->customerNumber);
			$paymentDetails->bindParam(2, $this->checkNumber);

			$paymentDetails->execute();

			if($paymentDetails->rowCount()==0){
				return;
			}
			
			$this->valid = true;

			$paymentDetails = $paymentDetails->fetch(PDO::FETCH_ASSOC);

			extract($paymentDetails);

			$this->customerNumber	= intval($customerNumber);
			$this->checkNumber		= $checkNumber;
			$this->paymentDate		= $paymentDate;
			$this->amount			= floatval($amount);
		}
		
		public function create(){

			$this->valid = false;

			$insertRes = "
				INSERT INTO
					{$this->table}
				SET
					customerNumber=:customerNumber,
					checkNumber=:checkNumber,
					paymentDate=:paymentDate,
					amount=:amount
				;
			";

			$insertRes = $this->conn->prepare($insertRes);

			$this->checkNumber 		= htmlspecialchars(strip_tags($this->checkNumber));
			$this->paymentDate 		= htmlspecialchars(strip_tags($this->paymentDate));
			$this->amount 			= htmlspecialchars(strip_tags($this->amount));

			$insertRes->bindParam(":customerNumber",	$this->customerNumber);
			$insertRes->bindParam(":checkNumber",		$this->checkNumber);
			$insertRes->bindParam(":paymentDate",		$this->paymentDate);
			$insertRes->bindParam(":amount",			$this->amount);

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
					checkNumber=:newCheckNumber,
					paymentDate=:paymentDate,
					amount=:amount
				WHERE
					customerNumber=:customerNumber
					AND
					checkNumber=:checkNumber
				;
			";

			$updateRes = $this->conn->prepare($updateRes);

			$this->checkNumber 		= htmlspecialchars(strip_tags($this->checkNumber));
			$this->paymentDate 		= htmlspecialchars(strip_tags($this->paymentDate));
			$this->amount 			= htmlspecialchars(strip_tags($this->amount));

			$updateRes->bindParam(":customerNumber", 	$this->customerNumber);
			$updateRes->bindParam(":checkNumber", 		$this->checkNumber);
			$updateRes->bindParam(":newCheckNumber", 	$this->newCheckNumber);
			$updateRes->bindParam(":paymentDate",		$this->paymentDate);
			$updateRes->bindParam(":amount",			$this->amount);

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
					customerNumber=?
					AND
					checkNumber=?
				;
			";

			$deleteResult = $this->conn->prepare($deleteResult);

			$deleteResult->bindParam(1, $this->customerNumber);
			$deleteResult->bindParam(2, $this->checkNumber);

			$deleteResult->execute();

			if($deleteResult->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $insertRes->error;
		}
	}
?>
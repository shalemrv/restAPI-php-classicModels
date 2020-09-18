<?php
	/**
	* Customers Modification Class
	*/
	class Customer{

		private $conn;
		private $table = "customers";

		public $valid = false;
		public $errors = array();

		public $payments;

		public $customerNumber;
		public $customerName;
		public $contactLastName;
		public $contactFirstName;
		public $phone;
		public $addressLine1;
		public $addressLine2;
		public $city;
		public $state;
		public $postalCode;
		public $country;
		public $salesRepEmployeeNumber;
		public $creditLimit;
		
		function __construct($db){
			$this->conn = $db;
		}

		public function validate(){
			$this->valid = true;

			// FIRST NAME VALID
			if(strlen(str_replace(" ", "", $this->contactFirstName))<2){
				$this->errors[] = "First name has to be at least 2 characters long.";
				$this->valid = false;	
			}

			// LAST NAME VALID
			if(strlen(str_replace(" ", "", $this->contactLastName))<2){
				$this->errors[] = "Last name has to be at least 2 characters long.";
				$this->valid = false;	
			}

			// PHONE NUMBER VALID
			if(strlen(str_replace(" ", "", $this->phone))<6){
				$this->errors[] = "Phone number has to be at least 6 digits long.";
				$this->valid = false;	
			}

			// ADDRESS LINE 1 VALID
			if(strlen(str_replace(" ", "", $this->addressLine1))<6){
				$this->errors[] = "Address line 1 has to be at least 6 characters long.";
				$this->valid = false;	
			}

			// CITY VALID
			if(strlen(str_replace(" ", "", $this->city))<2){
				$this->errors[] = "City has to be at least 2 characters long.";
				$this->valid = false;	
			}

			// COUNTRY VALID
			if(strlen(str_replace(" ", "", $this->country))<4){
				$this->errors[] = "Country has to be at least 4 characters long.";
				$this->valid = false;
			}		
		}

		public function isDuplicate(){
			$this->valid = true;

			//Same company name exists
			$sameCompany = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					customerName='" . addslashes($this->customerName) . "'
				;
			";

			$sameCompany = $this->conn->prepare($sameCompany);

			$sameCompany->execute();

			//Same First AND Last name exists
			$sameNames = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					contactFirstName='" . addslashes($this->contactFirstName) . "'
					AND
					contactLastName='" . addslashes($this->contactLastName) . "'
				;
			";

			$sameNames = $this->conn->prepare($sameNames);

			$sameNames->execute();

			if($sameCompany->rowCount()){
				$this->errors[] = "Customer with same name already exists. Please update Company name.";
				$this->valid = false;
			}

			if($sameNames->rowCount()){
				$this->errors[] = "Customer with same name already exists. Please update first/last name.";
				$this->valid = false;
			}			
		}

		public function countOrders(){
			$this->valid = false;
			
			$this->orders = "
				SELECT
					COUNT(orderNumber) as value
				FROM
					orders
				WHERE
					customerNumber={$this->customerNumber}
			";

			$this->orders = $this->conn->prepare($this->orders);

			$this->orders->execute();

			$this->orders = $this->orders->fetch(PDO::FETCH_ASSOC);
			
			$this->orders = intval($this->orders['value']);
		}

		public function countPayments(){

			$this->payments = "
				SELECT
					COUNT(customerNumber) as value
				FROM
					payments
				WHERE
					customerNumber={$this->customerNumber}
			";

			$this->payments = $this->conn->prepare($this->payments);

			$this->payments->execute();

			$this->payments = $this->payments->fetch(PDO::FETCH_ASSOC);
			
			$this->payments = intval($this->payments['value']);

			$this->errors[] = "Customer {$this->customerNumber} has {$this->payments} existing payment(s). Please delete the orders to delete this customer.";
		}

		public function list(){
			$query = "
				SELECT
					*
				FROM
					{$this->table}
				ORDER BY
					customerNumber DESC
				;
			";

			$pdoRes = $this->conn->prepare($query);
			$pdoRes->execute();

			return $pdoRes;
		}

		public function details(){
			$query = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					customerNumber=?
				;
			";

			$pdoRes = $this->conn->prepare($query);

			$pdoRes->bindParam(1, $this->customerNumber);

			$pdoRes->execute();

			if($pdoRes->rowCount()==0){
				return;
			}
			
			$this->valid = true;

			$customerDetails = $pdoRes->fetch(PDO::FETCH_ASSOC);

			extract($customerDetails);

			$this->customerNumber			= $customerNumber;
			$this->customerName				= $customerName;
			$this->contactLastName			= $contactLastName;
			$this->contactFirstName			= $contactFirstName;
			$this->phone					= $phone;
			$this->addressLine1				= $addressLine1;
			$this->addressLine2				= $addressLine2;
			$this->city						= $city;
			$this->state					= $state;
			$this->postalCode				= $postalCode;
			$this->country					= $country;
			$this->salesRepEmployeeNumber	= $salesRepEmployeeNumber;
			$this->creditLimit				= $creditLimit;
		}
		
		public function create(){

			//Same Customer number exists
			$maxNumber = "
				SELECT
					MAX(customerNumber) AS value
				FROM
					{$this->table}
				;
			";

			$maxNumber = $this->conn->prepare($maxNumber);

			$maxNumber->execute();

			$maxNumber = $maxNumber->fetch(PDO::FETCH_ASSOC);

			$this->customerNumber = intval($maxNumber['value']) + 1;

			$this->valid = false;

			$insertRes = "
				INSERT INTO
					{$this->table}
				SET
					customerNumber=:customerNumber,
					customerName=:customerName,
					contactLastName=:contactLastName,
					contactFirstName=:contactFirstName,
					phone=:phone,
					addressLine1=:addressLine1,
					addressLine2=:addressLine2,
					city=:city,
					state=:state,
					postalCode=:postalCode,
					country=:country,
					salesRepEmployeeNumber=:salesRepEmployeeNumber,
					creditLimit=:creditLimit
				;
			";

			$insertRes = $this->conn->prepare($insertRes);

			$this->customerName 			= htmlspecialchars(strip_tags($this->customerName));
			$this->contactLastName 			= htmlspecialchars(strip_tags($this->contactLastName));
			$this->contactFirstName 		= htmlspecialchars(strip_tags($this->contactFirstName));
			$this->phone 					= htmlspecialchars(strip_tags($this->phone));
			$this->addressLine1 			= htmlspecialchars(strip_tags($this->addressLine1));
			$this->addressLine2 			= htmlspecialchars(strip_tags($this->addressLine2));
			$this->city 					= htmlspecialchars(strip_tags($this->city));
			$this->state 					= htmlspecialchars(strip_tags($this->state));
			$this->postalCode 				= htmlspecialchars(strip_tags($this->postalCode));
			$this->country 					= htmlspecialchars(strip_tags($this->country));
			$this->salesRepEmployeeNumber 	= htmlspecialchars(strip_tags($this->salesRepEmployeeNumber));
			$this->creditLimit 				= htmlspecialchars(strip_tags($this->creditLimit));

			$insertRes->bindParam(":customerNumber", 			$this->customerNumber);
			$insertRes->bindParam(":customerName", 				$this->customerName);
			$insertRes->bindParam(":contactLastName",			$this->contactLastName);
			$insertRes->bindParam(":contactFirstName",			$this->contactFirstName);
			$insertRes->bindParam(":phone",						$this->phone);
			$insertRes->bindParam(":addressLine1",				$this->addressLine1);
			$insertRes->bindParam(":addressLine2",				$this->addressLine2);
			$insertRes->bindParam(":city",						$this->city);
			$insertRes->bindParam(":state",						$this->state);
			$insertRes->bindParam(":postalCode",				$this->postalCode);
			$insertRes->bindParam(":country",					$this->country);
			$insertRes->bindParam(":salesRepEmployeeNumber",	$this->salesRepEmployeeNumber);
			$insertRes->bindParam(":creditLimit",				$this->creditLimit);

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
					customerName=:customerName,
					contactLastName=:contactLastName,
					contactFirstName=:contactFirstName,
					phone=:phone,
					addressLine1=:addressLine1,
					addressLine2=:addressLine2,
					city=:city,
					state=:state,
					postalCode=:postalCode,
					country=:country,
					salesRepEmployeeNumber=:salesRepEmployeeNumber,
					creditLimit=:creditLimit
				WHERE
					customerNumber=:customerNumber
				;
			";

			$updateRes = $this->conn->prepare($updateRes);

			$this->customerName 			= htmlspecialchars(strip_tags($this->customerName));
			$this->contactLastName 			= htmlspecialchars(strip_tags($this->contactLastName));
			$this->contactFirstName 		= htmlspecialchars(strip_tags($this->contactFirstName));
			$this->phone 					= htmlspecialchars(strip_tags($this->phone));
			$this->addressLine1 			= htmlspecialchars(strip_tags($this->addressLine1));
			$this->addressLine2 			= htmlspecialchars(strip_tags($this->addressLine2));
			$this->city 					= htmlspecialchars(strip_tags($this->city));
			$this->state 					= htmlspecialchars(strip_tags($this->state));
			$this->postalCode 				= htmlspecialchars(strip_tags($this->postalCode));
			$this->country 					= htmlspecialchars(strip_tags($this->country));
			$this->salesRepEmployeeNumber 	= htmlspecialchars(strip_tags($this->salesRepEmployeeNumber));
			$this->creditLimit 				= htmlspecialchars(strip_tags($this->creditLimit));

			$updateRes->bindParam(":customerNumber", 			$this->customerNumber);
			$updateRes->bindParam(":customerName", 				$this->customerName);
			$updateRes->bindParam(":contactLastName",			$this->contactLastName);
			$updateRes->bindParam(":contactFirstName",			$this->contactFirstName);
			$updateRes->bindParam(":phone",						$this->phone);
			$updateRes->bindParam(":addressLine1",				$this->addressLine1);
			$updateRes->bindParam(":addressLine2",				$this->addressLine2);
			$updateRes->bindParam(":city",						$this->city);
			$updateRes->bindParam(":state",						$this->state);
			$updateRes->bindParam(":postalCode",				$this->postalCode);
			$updateRes->bindParam(":country",					$this->country);
			$updateRes->bindParam(":salesRepEmployeeNumber",	$this->salesRepEmployeeNumber);
			$updateRes->bindParam(":creditLimit",				$this->creditLimit);

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
				;
			";

			$deleteResult = $this->conn->prepare($deleteResult);

			$deleteResult->bindParam(1, $this->customerNumber);

			$deleteResult->execute();

			if($deleteResult->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $insertRes->error;
		}
	}
?>
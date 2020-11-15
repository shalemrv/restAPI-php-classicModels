<?php
	
	class Office{

		use Validate;

		private $conn;
		private $table = "offices";

		public $valid = false;
		public $errors = array();

		public $employees;

		public $officeCode;
		public $city;
		public $phone;
		public $addressLine1;
		public $addressLine2;
		public $state;
		public $country;
		public $postalCode;
		public $territory;
		
		function __construct($db){
			$this->conn = $db;
		}

		public function validate(){
			$this->valid = true;

			$this->officeCode 		= $this->cleanse("alphaNum", $this->officeCode);
			$this->city 			= $this->cleanse("alphaSpace", $this->city);
			$this->phone 			= $this->cleanse("num", $this->phone);
			$this->addressLine1 	= $this->cleanse("removeHtmlTags", $this->addressLine1);
			$this->addressLine2 	= $this->cleanse("removeHtmlTags", $this->addressLine2);
			$this->state 			= $this->cleanse("alphaSpace", $this->state);
			$this->country 			= $this->cleanse("alphaSpace", $this->country);
			$this->postalCode 		= $this->cleanse("num", $this->postalCode);
			$this->territory 		= $this->cleanse("alphaSpace", $this->territory);

			// OFFICE CODE VALID
			if( !$this->valid("min-char", $this->officeCode, 1) ){
				$this->errors[] = "Office code has to be at least 1 characters long.";
				$this->valid = false;	
			}

			// PHONE NUMBER VALID
			if( !$this->valid("min-char", $this->phone, 6) ){
				$this->errors[] = "Phone number has to be at least 6 digits long.";
				$this->valid = false;	
			}

			// CITY VALID
			if( !$this->valid("min-char", $this->city, 3) ){
				$this->errors[] = "City has to be at least 3 characters long.";
				$this->valid = false;
			}

			// ADDRESS LINE 1 VALID
			if( !$this->valid("min-char", $this->addressLine1, 6) ){
				$this->errors[] = "Address line 1 has to be at least 6 characters long.";
				$this->valid = false;	
			}

			// COUNTRY VALID
			if( !$this->valid("min-char", $this->country, 3) ){
				$this->errors[] = "Country has to be at least 3 characters long.";
				$this->valid = false;
			}

			// POSTAL CODE VALID
			if( !$this->valid("min-char", $this->postalCode, 5) ){
				$this->errors[] = "Postal Code has to be at least 5 characters long.";
				$this->valid = false;
			}

			// TERRITORY VALID
			if( !$this->valid("min-char", $this->territory, 4) ){
				$this->errors[] = "Territory has to be at least 4 characters long.";
				$this->valid = false;
			}
		}

		public function isDuplicate(){
			$this->valid = true;

			//Same company name exists
			$sameOfficeCode = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					officeCode='{$this->officeCode}'
				;
			";

			$sameOfficeCode = $this->conn->prepare($sameOfficeCode);

			$sameOfficeCode->execute();

			if($sameOfficeCode->rowCount()){
				$this->errors[] = "Office with same office code already exists. Please update Office Code.";
				$this->valid = false;
			}
		}

		public function countEmployees(){
			$this->employees = "
				SELECT
					COUNT(employeeNumber) as value
				FROM
					employees
				WHERE
					officeCode={$this->officeCode}
			";

			$this->employees = $this->conn->prepare($this->employees);

			$this->employees->execute();

			$this->employees = $this->employees->fetch(PDO::FETCH_ASSOC);
			
			$this->employees = intval($this->employees['value']);
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
			$officesDataset = "
				SELECT
					*
				FROM
					{$this->table}
				ORDER BY
					city
				;
			";

			$officesDataset = $this->conn->prepare($officesDataset);
			$officesDataset->execute();

			return $officesDataset;
		}

		public function details(){
			$officeDetails = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					officeCode=?
				;
			";

			$officeDetails = $this->conn->prepare($officeDetails);

			$officeDetails->bindParam(1, $this->officeCode);

			$officeDetails->execute();

			if($officeDetails->rowCount()==0){
				return;
			}
			
			$this->valid = true;

			$officeDetails = $officeDetails->fetch(PDO::FETCH_ASSOC);

			extract($officeDetails);

			$this->officeCode	= $officeCode;
			$this->city			= $city;
			$this->phone		= $phone;
			$this->addressLine1	= $addressLine1;
			$this->addressLine2	= $addressLine2;
			$this->state		= $state;
			$this->country		= $country;
			$this->postalCode	= $postalCode;
			$this->territory	= $territory;
		}
		
		public function create(){

			$this->valid = false;

			$insertRes = "
				INSERT INTO
					{$this->table}
				SET
					officeCode=:officeCode,
					city=:city,
					phone=:phone,
					addressLine1=:addressLine1,
					addressLine2=:addressLine2,
					state=:state,
					country=:country,
					postalCode=:postalCode,
					territory=:territory
				;
			";

			$insertRes = $this->conn->prepare($insertRes);

			$insertRes->bindParam(":officeCode",	$this->officeCode);
			$insertRes->bindParam(":city",			$this->city);
			$insertRes->bindParam(":phone",			$this->phone);
			$insertRes->bindParam(":addressLine1",	$this->addressLine1);
			$insertRes->bindParam(":addressLine2",	$this->addressLine2);
			$insertRes->bindParam(":state",			$this->state);
			$insertRes->bindParam(":country",		$this->country);
			$insertRes->bindParam(":postalCode",	$this->postalCode);
			$insertRes->bindParam(":territory",		$this->territory);

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
					city=:city,
					phone=:phone,
					addressLine1=:addressLine1,
					addressLine2=:addressLine2,
					state=:state,
					country=:country,
					postalCode=:postalCode,
					territory=:territory
				WHERE
					officeCode=:officeCode
				;
			";

			$updateRes = $this->conn->prepare($updateRes);

			$updateRes->bindParam(":officeCode",	$this->officeCode);
			$updateRes->bindParam(":city",			$this->city);
			$updateRes->bindParam(":phone",			$this->phone);
			$updateRes->bindParam(":addressLine1",	$this->addressLine1);
			$updateRes->bindParam(":addressLine2",	$this->addressLine2);
			$updateRes->bindParam(":state",			$this->state);
			$updateRes->bindParam(":country",		$this->country);
			$updateRes->bindParam(":postalCode",	$this->postalCode);
			$updateRes->bindParam(":territory",		$this->territory);

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
					officeCode=?
				;
			";

			$deleteResult = $this->conn->prepare($deleteResult);

			$deleteResult->bindParam(1, $this->officeCode);

			$deleteResult->execute();

			if($deleteResult->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $deleteResult->error;
		}
	}
?>
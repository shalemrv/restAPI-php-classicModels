<?php
	
	class Employee{

		use Validate;

		private $conn;
		private $table = "employees";

		public $valid = false;
		public $errors = array();

		public $customers;

		public $employeeNumber;
		public $firstName;
		public $lastName;
		public $extension;
		public $email;
		public $officeCode;
		public $reportsTo;
		public $jobTitle;
		
		function __construct($db){
			$this->conn = $db;
		}

		public function validate(){
			$this->valid = true;

			$this->firstName 	= $this->cleanse("alphaSpace", $this->firstName);
			$this->lastName 	= $this->cleanse("alphaSpace", $this->lastName);
			$this->extension 	= $this->cleanse("alphaNum", $this->extension);
			$this->email 		= $this->cleanse("removeHtmlTags", $this->email);
			$this->officeCode 	= intval($this->officeCode);
			$this->reportsTo 	= intval($this->reportsTo);
			$this->jobTitle 	= $this->cleanse("alphaNumSpace", $this->jobTitle);

			// FIRST NAME VALID
			if(!$this->valid("min-char", $this->firstName, 2) ){
				$this->errors[] = "First name has to be at least 2 characters long.";
				$this->valid = false;
			}

			// LAST NAME VALID
			if(!$this->valid("min-char", $this->lastName, 2) ){
				$this->errors[] = "Last name has to be at least 2 characters long.";
				$this->valid = false;
			}

			// EMAIL VALID
			if( !$this->valid("email", $this->email) ){
				$this->errors[] = "Invalid email format";
				$this->valid = false;
			}

			// CITY VALID
			if(!$this->valid("min-char", $this->jobTitle, 2) ){
				$this->errors[] = "Job Title has to be at least 2 characters long.";
				$this->valid = false;
			}
		}

		public function isDuplicate(){
			$this->valid = true;

			//Same First AND Last name exists
			$sameNames = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					firstName='" . addslashes($this->firstName) . "'
					AND
					lastName='" . addslashes($this->lastName) . "'
				;
			";

			$sameNames = $this->conn->prepare($sameNames);

			$sameNames->execute();

			if($sameNames->rowCount()){
				$this->errors[] = "Employee with same name already exists. Please update first/last name.";
				$this->valid = false;
			}
		}

		public function officeExists(){
			$this->valid = false;

			//New sales rep number is valid name exists
			$officesDataset = "
				SELECT
					*
				FROM
					offices
				WHERE
					officeCode='{$this->officeCode}'
				;
			";

			$officesDataset = $this->conn->prepare($officesDataset);

			$officesDataset->execute();

			if($officesDataset->rowCount()){
				$this->valid = true;
				return;
			}	
			
			$this->errors[] = "Invalid Office. Select an office from the list of existing offices.";
		}

		public function reportingToExists(){
			$this->valid = true;

			if($this->reportsTo==0){
				return;
			}

			//New sales rep number is valid name exists
			$employeesDataset = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					employeeNumber='{$this->reportsTo}'
				;
			";

			$employeesDataset = $this->conn->prepare($employeesDataset);

			$employeesDataset->execute();

			if($employeesDataset->rowCount()==0){
				$this->valid = false;
				$this->errors[] = "Invalid Reporting to Employee. Select a valid employee as Supervisor.";
			}
		}

		public function countCustomers(){
			$this->customers = "
				SELECT
					COUNT(customerNumber) as value
				FROM
					customers
				WHERE
					salesRepEmployeeNumber={$this->employeeNumber}
			";

			$this->customers = $this->conn->prepare($this->customers);

			$this->customers->execute();

			$this->customers = $this->customers->fetch(PDO::FETCH_ASSOC);
			
			$this->customers = intval($this->customers['value']);
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
			$employeesDataset = "
				SELECT
					*
				FROM
					{$this->table}
				ORDER BY
					employeeNumber DESC
				;
			";

			$employeesDataset = $this->conn->prepare($employeesDataset);
			$employeesDataset->execute();

			return $employeesDataset;
		}

		public function details(){
			$this->valid = false;

			$query = "
				SELECT
					*
				FROM
					{$this->table}
				WHERE
					employeeNumber=?
				;
			";

			$employeeDetails = $this->conn->prepare($query);

			$employeeDetails->bindParam(1, $this->employeeNumber);

			$employeeDetails->execute();

			if($employeeDetails->rowCount()==0){
				return;
			}
			
			$this->valid = true;

			$employeeDetails = $employeeDetails->fetch(PDO::FETCH_ASSOC);

			extract($employeeDetails);

			$this->employeeNumber	= intval($employeeNumber);
			$this->lastName			= $lastName;
			$this->firstName		= $firstName;
			$this->extension		= $extension;
			$this->email			= $email;
			$this->officeCode		= $officeCode;
			$this->reportsTo		= intval($reportsTo);
			$this->jobTitle			= $jobTitle;
		}
		
		public function create(){

			//Same Customer number exists
			$maxNumber = "
				SELECT
					MAX(employeeNumber) AS value
				FROM
					{$this->table}
				;
			";

			$maxNumber = $this->conn->prepare($maxNumber);

			$maxNumber->execute();

			$maxNumber = $maxNumber->fetch(PDO::FETCH_ASSOC);

			$this->employeeNumber = intval($maxNumber['value']) + 1;

			$this->valid = false;

			$insertRes = "
				INSERT INTO
					{$this->table}
				SET
					employeeNumber=:employeeNumber,
					firstName=:firstName,
					lastName=:lastName,
					extension=:extension,
					email=:email,
					officeCode=:officeCode,
					jobTitle=:jobTitle
				;
			";

			if($this->reportsTo){
				$insertRes = "
					INSERT INTO
						{$this->table}
					SET
						employeeNumber=:employeeNumber,
						firstName=:firstName,
						lastName=:lastName,
						extension=:extension,
						email=:email,
						officeCode=:officeCode,
						reportsTo=:reportsTo,
						jobTitle=:jobTitle
					;
				";
			}

			$insertRes = $this->conn->prepare($insertRes);

			$insertRes->bindParam(":employeeNumber",	$this->employeeNumber);
			$insertRes->bindParam(":lastName",			$this->lastName);
			$insertRes->bindParam(":firstName",			$this->firstName);
			$insertRes->bindParam(":extension",			$this->extension);
			$insertRes->bindParam(":email",				$this->email);
			$insertRes->bindParam(":officeCode",		$this->officeCode);
			$insertRes->bindParam(":jobTitle",			$this->jobTitle);

			if($this->reportsTo){
				$insertRes->bindParam(":reportsTo",			$this->reportsTo);
			}

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
					firstName=:firstName,
					lastName=:lastName,
					extension=:extension,
					email=:email,
					officeCode=:officeCode,
					jobTitle=:jobTitle
				WHERE
					employeeNumber=:employeeNumber
				;
			";

			if($this->reportsTo){
				$updateRes = "
					UPDATE
						{$this->table}
					SET
						firstName=:firstName,
						lastName=:lastName,
						extension=:extension,
						email=:email,
						officeCode=:officeCode,
						reportsTo=:reportsTo,
						jobTitle=:jobTitle
					WHERE
						employeeNumber=:employeeNumber
					;
				";	
			}

			$updateRes = $this->conn->prepare($updateRes);

			$this->firstName 		= htmlspecialchars(strip_tags($this->firstName));
			$this->lastName 		= htmlspecialchars(strip_tags($this->lastName));
			$this->extension 		= htmlspecialchars(strip_tags($this->extension));
			$this->email 			= htmlspecialchars(strip_tags($this->email));
			$this->officeCode 		= htmlspecialchars(strip_tags($this->officeCode));
			$this->jobTitle 		= htmlspecialchars(strip_tags($this->jobTitle));

			$updateRes->bindParam(":employeeNumber",	$this->employeeNumber);
			$updateRes->bindParam(":firstName",			$this->firstName);
			$updateRes->bindParam(":lastName",			$this->lastName);
			$updateRes->bindParam(":extension",			$this->extension);
			$updateRes->bindParam(":email",				$this->email);
			$updateRes->bindParam(":officeCode",		$this->officeCode);
			$updateRes->bindParam(":jobTitle",			$this->jobTitle);

			if($this->reportsTo){
				$updateRes->bindParam(":reportsTo", $this->reportsTo);
			}

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
					employeeNumber=? 
				;
			";

			$deleteResult = $this->conn->prepare($deleteResult);

			$deleteResult->bindParam(1, $this->employeeNumber);

			$deleteResult->execute();

			if($deleteResult->execute()){
				$this->valid = true;
				return;
			}

			$this->errors[] = $deleteResult->error;
		}
	}
?>
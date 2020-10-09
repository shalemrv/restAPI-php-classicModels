<?php
	include("headers.php");
	/**
	 * Database operations class
	 */
	class Database{

		private $host		= "localhost";
		private $username	= "root";
		private $password	= "";
		private $dbName		= "classic_models";

		public $conn;
		
		// function __construct(argument){
		// 	# code...
		// }

		public function connect(){
			$this->conn = null;

			try{
				//MySQL Workbench
				// $this->password = "654321@admin";
				// $this->conn = new PDO(
				// 	"mysql:host={$this->host};port=3307;dbname={$this->dbName};charset=utf8",
				// 	$this->username,
				// 	$this->password
				// );
				//MySQL by XAMPP
				$this->conn = new PDO(
					"mysql:host={$this->host};dbname={$this->dbName};charset=utf8",
					$this->username,
					$this->password
				);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch (PDOException $e) {
				exit(json_encode(array(
					"complete"	=> false,
					"message"	=> "Database Connection Error: ".$e->getMessage()
				)));
			}

			return $this->conn;
		}
	}
?>
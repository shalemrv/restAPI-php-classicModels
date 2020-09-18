<?php
	include("headers.php");
	/**
	 * Database operations class
	 */
	class Database{

		private $host		= "localhost";
		private $username	= "root";
		private $password	= "";
		private $dbName		= "sample_db";

		public $conn;
		
		// function __construct(argument){
		// 	# code...
		// }

		public function connect(){
			$this->conn = null;

			try{
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
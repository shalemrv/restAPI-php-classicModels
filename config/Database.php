<?php
	require_once("headers.php");
	require_once("validate.php");
	/**
	 * Database operations class
	 */
	class Database{

		private $host;
		private $username;
		private $password;
		private $dbName;

		public $conn;
		
		function __construct(){

			$apiReqBy = $_SERVER['REMOTE_ADDR'];

			$apiReqByParts = explode(".", $apiReqBy);

			// if($apiReqBy=="127.0.0.1" || $apiReqBy=="::1"){
			// 	exit(json_encode(array(
			// 		"complete"	=> false,
			// 		"message"	=> "This API is only for Demo"
			// 	)));
			// }

			$domain			= $_SERVER['HTTP_HOST'];
			$devServer1		= (strpos($domain, "127.0.0.1")!== false)? true : false;
			$devServer2		= (strpos($domain, "localhost")!== false)? true : false;
			$isDevServer	= ($devServer1 || $devServer2)? true : false;

			if($isDevServer){
				$this->host		= "localhost";
				$this->username	= "root";
				$this->password	= "";
				$this->dbName	= "classic_models";
			}
			else{
				require("server-credentials.php");
			}
		}

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
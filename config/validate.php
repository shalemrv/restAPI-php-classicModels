<?php
	
	trait Validate{

		function cleanse($type, $input){
			switch($type) {
				case 'removeHtmlTags':
					return htmlspecialchars(strip_tags($input));

				case 'date':
					return preg_replace('/[^0-9\-]/', '', $input);
					
				case 'alpha':
					return preg_replace('/[^A-Za-z]/', '', $input);
					
				case 'alphaSpace':
					return preg_replace('/[^A-Za-z\s]/', '', $input);
					
				case 'alphaNum':
					return preg_replace('/[^A-Za-z0-9]/', '', $input);
					
				case 'alphaNumUnd':
					return preg_replace('/[^A-Za-z0-9_]/', '', $input);

				case 'alphaNumSpace':
					return preg_replace('/[^A-Za-z0-9\s]/', '', $input);
					
				case 'alphaNumSpaceSpecial':
					return preg_replace('/[^A-Za-z0-9\s\-\.]/', '', $input);
					
				case 'sentence':
					return preg_replace('/[^A-Za-z0-9\s\-\.]/', '', $input);

				case 'num':
					return preg_replace('/[^0-9]/', '', $input);
					
				case 'ratio':
					$parts	= explode(":", $input);
					$p1		= intval($parts[0]);
					$p2		= (isset($parts[1]))? intval($parts[1]) : 1;
					
					$p1		= ($p1)? $p1 : 1;
					$p2		= ($p2)? $p2 : 1;

					return "$p1:$p2";

				case 'removeSpaces':
					return str_replace(" ", "", $input);					
			}
		}

		function valid($type, $input, $len=0){
			switch($type){
				case 'min-char':
					return strlen(preg_replace('/[^A-Za-z0-9]/', '', $input))>=$len;

				case 'eq-char':
					return strlen(preg_replace('/[^A-Za-z0-9]/', '', $input))==$len;
				
				case 'email':
					return filter_var($input, FILTER_VALIDATE_EMAIL);
				
				case 'date-format':
					return preg_match("/\d{4}\-\d{2}-\d{2}/", $input);

				case 'date':
					$dateTime = DateTime::createFromFormat('Y-m-d', $input);
					$errors = DateTime::getLastErrors();
					if(!empty($errors['warning_count'])){
						return false;
					}
					return $dateTime!==false;
			}
		}
	}

?>
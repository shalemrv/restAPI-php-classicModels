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
					
				case 'alphaNumSpace':
					return preg_replace('/[^A-Za-z0-9\s]/', '', $input);
					
				case 'alphaNumSpaceSpecial':
					return preg_replace('/[^A-Za-z0-9\s\-\.]/', '', $input);
					
				case 'num':
					return preg_replace('/[^0-9]/', '', $input);
					
				case 'removeSpaces':
					return str_replace(" ", "", $input);					
			}
		}

		function valid($type, $input, $len=0){
			switch($type){
				case 'min-char':
					return strlen(preg_replace('/[^A-Za-z0-9]/', '', $input))>$len;

				case 'eq-char':
					return strlen(preg_replace('/[^A-Za-z0-9]/', '', $input))==$len;
				
				case 'email':
					return filter_var($input, FILTER_VALIDATE_EMAIL);
			}
		}

		// function removeHtmlTags($input){
		// 	return htmlspecialchars(strip_tags($input));
		// }

		// function alpha($input){
		// 	return preg_replace('/[^A-Za-z]/', '', $input);
		// }

		// function alphaSpace($input){
		// 	return preg_replace('/[^A-Za-z\s]/', '', $input);
		// }

		// function alphaNum($input){
		// 	return preg_replace('/[^A-Za-z0-9]/', '', $input);
		// }

		// function alphaNumSpace($input){
		// 	return preg_replace('/[^A-Za-z0-9\s]/', '', $input);
		// }

		// function alphaNumSpaceSpecial($input){
		// 	return preg_replace('/[^A-Za-z0-9\s\-\.]/', '', $input);
		// }		

		// function num($input){
		// 	return preg_replace('/[^0-9]/', '', $input);
		// }

		// function validEmail($email){
		// 	return filter_var($email, FILTER_VALIDATE_EMAIL);
		// }

		// function removeSpaces($input){
		// 	return str_replace(" ", "", $input);
		// }

		// function validCharCount($input){
		// 	return strlen(preg_replace('/[^A-Za-z0-9]/', '', $input));
		// }
	}

?>
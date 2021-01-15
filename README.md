# Rest API for Classic Modals DB `PHP`

PHP REST API performing CRUD operations on a relational MySQL sample database available on the internet. 

All responses will be in the below format

	{
		"complete"	: true,
		"message"	: "API Execution Message",
		"result"	: requestedData
	}

CUSTOMERS API

	1. List of all Customers

		METHOD: GET
		URL: {{baseURL}}/api/customers/list.php

	2. Details of single Customer

		METHOD: GET
		URL: {{baseURL}}/api/customers/details.php?customerNumber={{customerNumber: INTEGER}}

	3. Create a new Customer

		METHOD: POST
		URL: {{baseURL}}/api/customers/create.php
		PARAMS: {
			"customerName": "Whatsapp Inc",
			"contactFirstName": "Mark",
			"contactLastName": "Zuckerberg",
			"phone": "987654321",
			"addressLine1": "Battery Drive",
			"addressLine2": "Park Avenuew",
			"city": "New York",
			"state": "New York",
			"postalCode": "554268",
			"country": "USA",
			"salesRepEmployeeNumber": "1612",
			"creditLimit": "15000.00"
		}

		NOTE: 'salesRepEmployeeNumber' has to be the unique ID 'employeeNumber' from the employees list API	

	4. Update existing customer
		METHOD: POST
		URL: {{baseURL}}/api/customers/create.php
		PARAMS: {
			"customerName": "Whatsapp Inc",
			"contactFirstName": "Mark",
			"contactLastName": "Zuckerberg",
			"phone": "987654321",
			"addressLine1": "Battery Drive",
			"addressLine2": "Park Avenuew",
			"city": "New York",
			"state": "New York",
			"postalCode": "554268",
			"country": "USA",
			"salesRepEmployeeNumber": "1612",
			"creditLimit": "15000.00"
		}

		NOTE: 'salesRepEmployeeNumber' has to be the unique ID 'employeeNumber' from the employees list API

	5. Delete existing customer
		METHOD: GET
		URL: {{baseURL}}/api/customers/details.php?customerNumber={{customerNumber: INTEGER}}

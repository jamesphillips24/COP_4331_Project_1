<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	$inData = decodeJson();
	$apiKeyUsername = "TheBeast";
	$apiKeyPassword = "WeLoveCOP4331";
	$databaseName = "COP4331";
	$connection = new mysqli("localhost", $apiKeyUsername, $apiKeyPassword, $databaseName);
	if (checkForError($connection) == False) {
		selectMode($inData, $connection);
	}
	$connection->close();

	function selectMode($dictInputData, $providedConnection)
	{
		switch ($dictInputData["mode"]) {
			/*
			case 0:
				changeFirstName($dictInputData, $providedConnection);
				break;
			case 1:
				changeLastName($dictInputData, $providedConnection);
				break;
			case 2:
				changeEmail($dictInputData, $providedConnection);
				break;
			case 3:
				changePhoneNumber($dictInputData, $providedConnection);
				break;
			*/
			case 0:
				editContact($dictInputData, $providedConnection);
				break;
			case 1:
				saveEditContact($dictInputData, $providedConnection);
				break;
			case 4:
				deleteContact($dictInputData, $providedConnection);
				break;
			case 5:
				addContact($dictInputData, $providedConnection);
				break;
			case 6:
				searchForContact($dictInputData, $providedConnection);
				break;
			default:
				returnWithInfo(-6, "", "", "Invalid Mode Operation Called");
				break;
		}
	}


	function checkForError($providedConnection)
	{
		if( $providedConnection->connect_error )
		{
			returnWithError( $providedConnection->connect_error );
			return True;
		}
		else
		{
			return False;
		}
	}

	function deleteContact($dictInputData, $providedConnection) {
		$sqlCMD = $providedConnection->prepare("SELECT ID FROM Contacts WHERE ID=?");
		$sqlCMD->bind_param("i", $dictInputData["InputID"]);
		$sqlCMD->execute();
		$result = $sqlCMD->get_result();
		if ($result->num_rows == 0)
		{
			$sqlCMD->close();
			returnWithInfo(-12, "", "", "Row does not exist.");
			return;
		}
		else {
			$sqlCMD = $providedConnection->prepare("SELECT ID FROM Contacts WHERE ID=? AND UserID=?");
			$sqlCMD->bind_param("ii", $dictInputData["InputID"], $dictInputData["id"]);
			$sqlCMD->execute();
			$result = $sqlCMD->get_result();
			if ($result->num_rows == 0)
			{
				$sqlCMD->close();
				returnWithInfo(-13, "", "", "Access Denied. Invalid UserID Authentification.");
				return;
			}
			$sqlCMD = $providedConnection->prepare("DELETE FROM Contacts WHERE ID=?");
			$sqlCMD->bind_param("i", $dictInputData["InputID"]);
			$sqlCMD->execute();
			$sqlCMD->close();
			returnWithInfo($dictInputData["id"], "", "", "");
			return;
		}
	}

	function editContact($dictInputData, $providedConnection){
		//PROMPTS!!! for contact-edit
		$contactID = $dictInputData["contactID"];
		$userID = $dictInputData["userID"];

		$sqlCMD = $providedConnection->prepare("SELECT 	FirstName, 
														LastName,
														Phone,
														Email
												FROM Contacts 
												WHERE ID = ? and UserID=?");
		$sqlCMD->bind_param("ii", $contactID,$userID);
		$sqlCMD->execute();
		$contactInfo = $sqlCMD->get_result()->fetch_assoc();
		sendResultInfoAsJson(json_encode([
			"contactId" => $contactID,
			"FirstName" => $contactInfo["FirstName"],
			"LastName" 	=> $contactInfo["LastName"],
			"Phone" 	=> $contactInfo["Phone"],
			"Email" 	=> $contactInfo["Email"],
		]));
		$sqlCMD->close();
	}

	function saveEditContact($dictInputData, $providedConnection){
		//checks for validity of email and phone entries
		if($dictInputData["InputFirstName"] == "" && $dictInputData["InputLastName"] == "" && $dictInputData["InputEmail"] == "" && $dictInputData["InputPhone"] == ""){
			returnWithInfo(-9, "", "", "Cannot add empty contact.");
			return;
		}
		if(!($dictInputData["InputEmail"] == "")){
			if (hasSpaces($dictInputData["InputEmail"]) || hasPlus($dictInputData["InputEmail"])) {
				returnWithInfo(-7, "", "", "Invalid email. Cannot<br>contain spaces or aliases");
				return;
			}
			$resArr = 0;
			$emailArr = explode("@", $dictInputData["InputEmail"]);
			if (count($emailArr) != 2) {
				returnWithInfo(-8, "", "", "Invalid email. Use the traditional<br>yourIdentifier@domainHere");
				return;
			}
		}

		if(!($dictInputData["InputPhone"] == "")) {		
			if (countNumericChars($dictInputData["InputPhone"]) < 6 || countNumericChars($dictInputData["InputPhone"]) > 15) {
				returnWithInfo(-10, "", "", "Invalid Phone. International Phone<br>Numbers must have 6 to 15 digits");
				return;
			}
			if(!onlyDashesAndNumbers($dictInputData["InputPhone"])) {
				returnWithInfo(-11, "", "", "Invalid Phone. Must only<br>contain numbers or dashes.");
				return;
			}
		}

		//sql commands
		$sqlCMD = $providedConnection->prepare("UPDATE Contacts 
												SET FirstName = ?, 
													LastName = ?, 
													Email = ?, 
													Phone = ?
												WHERE ID = ? 
												AND UserID = ?;");
		$sqlCMD->bind_param("ssssii", 	$dictInputData["InputFirstName"], 
										$dictInputData["InputLastName"], 
										$dictInputData["InputEmail"], 
										$dictInputData["InputPhone"],

										$dictInputData["contactID"],
										$dictInputData["userID"]);
		$sqlCMD->execute();
		$sqlCMD->close();
		returnWithInfo(0, "", "", "");
		return;
	}

	function changePhoneNumber($dictInputData, $providedConnection) {
		if (countNumericChars($dictInputData["InputPhone"]) < 6 || countNumericChars($dictInputData["InputPhone"]) > 15) {
			returnWithInfo(-10, "", "", "Invalid Phone Number. By E.164, International Phone Numbers must have 6 to 15 digits (area+full country code (zeros included) included).");
			return;
		}
		$sqlCMD = $providedConnection->prepare("UPDATE Contacts SET Phone =? WHERE ID=? AND UserID=?");
		$sqlCMD->bind_param("sii", $dictInputData["InputPhone"], $dictInputData["InputID"],$dictInputData["id"]);
		$sqlCMD->execute();
		$sqlCMD->close();
		returnWithInfo($dictInputData["id"], "", "", "");
		return;

	}

	function changeEmail($dictInputData, $providedConnection)
	{
		if (hasSpaces($dictInputData["InputEmail"]) || hasPlus($dictInputData["InputEmail"])) {
			returnWithInfo(-7, "", "", "Emails cannot contain spaces or aliases");
			return;
		}
		else {
			$resArr = 0;
			$emailArr = explode("@", $dictInputData["InputEmail"]);
			if (count($emailArr) != 2) {
				returnWithInfo(-8, "", "", "Invalid email provided. Please use the traditional yourIdentifier@domainHere");
				return;
			}
			else {
				$sqlCMD = $providedConnection->prepare("UPDATE Contacts SET Email =? WHERE ID=? AND UserID=?");
				$sqlCMD->bind_param("sii", $dictInputData["InputEmail"], $dictInputData["InputID"],$dictInputData["id"]);
				$sqlCMD->execute();
				$sqlCMD->close();
				returnWithInfo($dictInputData["id"], "", "", "");
				return;
			}
		}
	}


	function changeLastName($dictInputData, $providedConnection)
	{
		$sqlCMD = $providedConnection->prepare("UPDATE Contacts SET LastName =? WHERE ID=? AND UserID=?");
		$sqlCMD->bind_param("sii", $dictInputData["InputLName"], $dictInputData["InputID"], $dictInputData["id"]);
		$sqlCMD->execute();
		$sqlCMD->close();
		returnWithInfo($dictInputData["id"], "", "", "");
	}

	function changeFirstName($dictInputData, $providedConnection)
	{
		$sqlCMD = $providedConnection->prepare("UPDATE Contacts SET FirstName =? WHERE ID=? AND UserID=?");
		$sqlCMD->bind_param("sii", $dictInputData["InputFirstName"], $dictInputData["InputID"], $dictInputData["id"]);
		$sqlCMD->execute();
		$sqlCMD->close();
		returnWithInfo($dictInputData["id"], "", "", "");
	}



	function addContact($dictInputData, $providedConnection)
	{
		if($dictInputData["InputFirstName"] == "" && $dictInputData["InputLastName"] == "" && $dictInputData["InputEmail"] == "" && $dictInputData["InputPhone"] == ""){
			returnWithInfo(-9, "", "", "Cannot add empty contact.");
			return;
		}
		if(!($dictInputData["InputEmail"] == "")){
			if (hasSpaces($dictInputData["InputEmail"]) || hasPlus($dictInputData["InputEmail"])) {
				returnWithInfo(-7, "", "", "Invalid email. Cannot<br>contain spaces or aliases");
				return;
			}
			$resArr = 0;
			$emailArr = explode("@", $dictInputData["InputEmail"]);
			if (count($emailArr) != 2) {
				returnWithInfo(-8, "", "", "Invalid email. Use the traditional<br>yourIdentifier@domainHere");
				return;
			}
		}

		if(!($dictInputData["InputPhone"] == "")) {		
			if (countNumericChars($dictInputData["InputPhone"]) < 6 || countNumericChars($dictInputData["InputPhone"]) > 15) {
				returnWithInfo(-10, "", "", "Invalid Phone. International Phone<br>Numbers must have 6 to 15 digits");
				return;
			}
			if(!onlyDashesAndNumbers($dictInputData["InputPhone"])) {
				returnWithInfo(-11, "", "", "Invalid Phone. Must only<br>contain numbers or dashes.");
				return;
			}
		}

		$sqlCMD = $providedConnection->prepare("INSERT INTO Contacts (FirstName, LastName, Email, Phone, UserID) VALUES (?, ?, ?, ?, ?)");
		$sqlCMD->bind_param("ssssi", $dictInputData["InputFirstName"], $dictInputData["InputLastName"], $dictInputData["InputEmail"], $dictInputData["InputPhone"], $dictInputData["id"]);
		$sqlCMD->execute();
		$refID = $providedConnection->insert_id;
		$sqlCMD->close();
		returnWithInfo($refID, "", "", "");
	}

	function searchForContact($dictInputData, $providedConnection)
	{
		$term = "%" . $dictInputData["searchterm"] . "%";
		$sqlCMD = $providedConnection->prepare("SELECT * FROM Contacts WHERE (FirstName LIKE ? OR LastName LIKE ? OR Email LIKE ?) AND UserID=?");
		$sqlCMD->bind_param("sssi", $term, $term, $term, $dictInputData["id"]);
		$sqlCMD->execute();
		$result = $sqlCMD->get_result();
		$results = array();
		while ($row = $result->fetch_assoc()) {
			$results[] = $row;
		}
		$sqlCMD->close();

		sendResultInfoAsJson(json_encode(array(
			"error" => "",
			"searchResults" => $results
		)));
	}


	function decodeJson()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function returnWithError($err) {
		$jsVal = '{
			"error": ' . $err . ',
			"searchResults": {

			}
		}';
		sendResultInfoAsJson($jsVal);
	}

	function returnWithInfo( $userID, $firstName, $lastName, $error ) {
		$returnValue = '{"id":' . $userID . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":"' . $error . '"}';
		sendResultInfoAsJson( $returnValue );
	}

	function sendResultInfoAsJson( $obj ) {
		header('Content-type: application/json');
		echo $obj;
	}

	function hasSpaces($mystring) {
		return (strpos($mystring, ' ') !== false);
	}
	function hasPlus($myString) {
		return (strpos($myString, '+') !== false);
	}
	function hasAtSymbol($myString) {
		return (strpos($myString, '@') !== false);
	}

	function onlyDashesAndNumbers($str) {
		return strspn($str, '0123456789-') === strlen($str);
	}

	function countNumericChars($str) {
		return strlen(preg_replace('/[^0-9]/', '', $str));
	}

	function NumericOnly($str) {
		return ctype_digit($str);
	}

?>
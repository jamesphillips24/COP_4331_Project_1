<?php
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
			$sqlCMD = $providedConnection->prepare("DELETE FROM Contacts ID WHERE ID=?");
			$sqlCMD->bind_param("i", $dictInputData["InputID"]);
			$sqlCMD->execute();
			$sqlCMD->close();
			returnWithInfo($dictInputData["id"], "", "", "");
			return;
		}
	}
	
	function changePhoneNumber($dictInputData, $providedConnection) {
		if (strlen($dictInputData["InputPhone"]) < 6 || strlen($dictInputData["InputPhone"]) > 15) {
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
				$sqlCMD->bind_param("ssi", $dictInputData["InputEmail"], $dictInputData["InputID"],$dictInputData["id"]);
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
		if (hasSpaces($dictInputData["InputEmail"]) || hasPlus($dictInputData["InputEmail"])) {
			returnWithInfo(-7, "", "", "Emails cannot contain spaces or aliases");
			return;
		}
		$resArr = 0;
		$emailArr = explode("@", $dictInputData["InputEmail"]);
		if (count($emailArr) != 2) {
			returnWithInfo(-8, "", "", "Invalid email provided. Please use the traditional yourIdentifier@domainHere");
			return;
		}
		if (strlen($dictInputData["InputPhone"]) < 6 || strlen($dictInputData["InputPhone"]) > 15) {
			returnWithInfo(-10, "", "", "Invalid Phone Number. By E.164, International Phone Numbers must have 6 to 15 digits (area+full country code (zeros included) included).");	
			return;
		}
		elseif(!NumericOnly($dictInputData["InputPhone"])) {
			returnWithInfo(-11, "", "", "Enter Phone Numbers with ONLY Integer Values.");	
			return;
		}
		
		$sqlCMD = $providedConnection->prepare("INSERT INTO Contacts (FirstName, LastName, Email, Phone, UserID) VALUES (?, ?, ?, ?, ?)");
		$sqlCMD->bind_param("ssssi", $dictInputData["InputFirstName"], $dictInputData["InputLastName"], $dictInputData["InputEmail"], $dictInputData["InputPhone"], $dictInputData["id"]);
		$sqlCMD->execute();	
		$refID = $providedConnection->insert_id();		
		$sqlCMD->close();
		returnWithInfo($refID, "", "", "");
	}
	
	function searchForContact($dictInputData, $providedConnection)
	{
		$sqlCMD = $providedConnection->prepare("SELECT * FROM Contacts WHERE (FirstName LIKE ? OR LastName LIKE ? OR Email LIKE ?) AND UserID=?");
		$sqlCMD->bind_param("sssi", $dictInputData["searchterm"], $dictInputData["searchterm"], $dictInputData["searchterm"], $dictInputData["id"]);
		$sqlCMD->execute();
		$result = $sqlCMD->get_result();
		$jsVal = '{
			"error": "",
			"searchResults": {
				';
		$index = 0;
		while ( $row = $result->fetch_assoc()) {
			if ($index != 0) {
				$jsVal .= ',
				';
			}			
			$jsVal .= '	{ "ColumnID": ' . $row["ID"] . ', "First Name":' . $row["FirstName"] . ', "Last Name": ' . $row["LastName"] . ', "Email Address": ' . $row["Email"] . ', "Phone Number": ' . $row["Phone"] . ', UserID: ' . $row["UserID"] .' }';
			$index += 1;
		}
		$jsVal .= '
		}
		}';
		$sqlCMD->close();
		sendResultInfoAsJson($jsVal);
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
	
?>

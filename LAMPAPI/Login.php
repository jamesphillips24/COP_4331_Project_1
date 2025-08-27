<?php
	$inputData = decodeJSON();
	$apiKeyUsername = "masterUserName";
	$apiKeyPassword = "masterPassword";
	$databaseName = "COP4331_Project1";
	$connection = new mysqli("localhost", $apiKeyUsername, $apiKeyPassword, $databaseName);
	attemptLogin($inputData["username"], $inputData["password"], $connection);
	
	function attemptLogin($username, $password, $providedConnection) {
		$sqlCMD = $providedConnection->prepare("SELECT UserID, firstName, lastName FROM PasswordDB WHERE Username=? AND Password=?");
		$sqlCMD->bind_param("ss", $username, $password);
		$sqlCMD->execute();
		$rowHolder = $sqlCMD->get_result();
		$index = 0;
		$result = "";
		while($rowInfo = $rowHolder->fetch_assoc()) {
			if( $index > 0 ) {
				$result .= ",";
			}
			$index++;
			$result .= '"' . $rowInfo["UserID"] . '"';
		}
		if ($index == 0) {
			returnWithInfo(-1, "", "", "Invalid User/Password combination");
		}
		else {
			returnWithInfo((int) $rowInfo["userID"], $rowInfo["firstName"], $rowInfo["lastName"], "");
		}
		$sqlCMD->close();
		$providedConnection->close();
	}


	function decodeJSON()
	{
		return json_decode(file_get_contents('php://input'), true);
	}
	
	function returnWithInfo( $userID, $firstName, $lastName, $error ) {
		$returnValue = '{"id":' . $userID . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":"' . $error . '}';
		sendResultInfoAsJson( $returnValue );
	}
	
	function sendResultInfoAsJson( $obj ) {
		header('Content-type: application/json');
		echo $obj;
	}

?>
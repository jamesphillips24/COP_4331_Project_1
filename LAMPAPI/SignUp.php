<?php
	$inputData = decodeJSON();
	$apiKeyUsername = "masterUserName";
	$apiKeyPassword = "masterPassword";
	$databaseName = "COP4331";
	$connection = new mysqli("localhost", $apiKeyUsername, $apiKeyPassword, $databaseName);
	attemptSignUp($inputData, $connection);
	
	function attemptSignUp($dictInputData, $providedConnection) {
		if ($dictInputData["password"] != $dictInputData["confirmPass"]) {
			$providedConnection->close();
			returnWithInfo(-1, "", "", "Password does not match");
			return;
		}
		$sqlCMD = $providedConnection->prepare("SELECT Username FROM PasswordDB WHERE Username=?");
		$sqlCMD->bind_param("ss", $dictInputData["username"]);
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
			$sqlCMD = $providedConnection->prepare("INSERT INTO PasswordDB (Username, Password) VALUES (?, ?)");
			$sqlCMD->bind_param("ss", $dictInputData["username"], $dictInputData["password"]);
			$sqlCMD->execute();
			$rowHolder = $sqlCMD->get_result();
			$rowInfo = $rowHolder->fetch_assoc();
			$userID = $rowInfo["UserID"];
			$nameArr = explode(" ", $dictInputData["name"]);
			$firstName = $nameArr[0];
			$lastName = "";
			if ($count($nameArr) > 1) {
				$lastName = $nameArr[1];
			}
			$sqlCMD = $providedConnection->prepare("INSERT INTO Contacts (FirstName, LastName, Phone, Email, UserID) VALUES (?, ?, ?, ?, ?)");
			$sqlCMD->bind_param("ss", $firstName, $lastName, "", $dictInputData["email"], $userID);
			$sqlCMD->execute();
			returnWithInfo((int) $userID, $firstName, $lastName, "");
		}
		else {
			returnWithInfo(-2, "", "", "Username already exists");
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
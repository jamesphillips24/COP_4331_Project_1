<?php
	$inputData = decodeJSON();
	$apiKeyUsername = "masterUserName";
	$apiKeyPassword = "masterPassword";
	$databaseName = "COP4331_Project1";
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
			$nameArr = explode(" ", $dictInputData["name"]);
			$firstName = $nameArr[0];
			$lastName = "";
			if ($count($nameArr) > 1) {
				$lastName = $nameArr[1];
			}
			$sqlCMD = $providedConnection->prepare("INSERT INTO PasswordDB (firstName, lastName, Username, Password, Email) VALUES (?, ?, ?, ?, ?)");
			$sqlCMD->bind_param("ss", $firstName, $lastName, $username, $dictInputData["password"], $dictInputData["email"]);
			$sqlCMD->execute();
			$sqlCMD = $providedConnection->prepare("SELECT UserID FROM PasswordDB WHERE Username=?");
			$sqlCMD->bind_param("ss", $dictInputData["username"]);
			$sqlCMD->execute();
			$rowHolder = $sqlCMD->get_result();
			$rowInfo = $rowHolder->fetch_assoc();
			$userID = $rowInfo["UserID"];
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
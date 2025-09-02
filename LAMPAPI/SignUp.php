<?php
	$inputData = decodeJSON();
	$apiKeyUsername = "TheBeast";
	$apiKeyPassword = "WeLoveCOP4331";
	$databaseName = "COP4331";
	$connection = new mysqli("localhost", $apiKeyUsername, $apiKeyPassword, $databaseName);
	attemptSignUp($inputData, $connection);
	
	function attemptSignUp($dictInputData, $providedConnection) {
		$name = $dictInputData["name"];
		$username = $dictInputData["username"];
		$password =  $dictInputData["password"];

		#confirm passwords matching
		if ($dictInputData["password"] != $dictInputData["confirmPass"]) {
			returnWithInfo(-1, "", "", "Password does not match");
			return;
		}

		#confirm username is not already used
		$sqlCMD = $providedConnection->prepare("SELECT Login FROM Users WHERE Login=?");
		$sqlCMD->bind_param("s", $dictInputData["username"]);
		$sqlCMD->execute();
		$matchingRow = $sqlCMD->get_result()->fetch_assoc();
		if($matchingRow != null){ #runs if username already exists
			returnWithInfo(-2, "", "", "Username already exists");
			return;
		}

		#add new user data
		$sqlCMD = $providedConnection->prepare("INSERT INTO Users (Name, Login, Password) VALUES (?, ?, ?)");
		$sqlCMD->bind_param("sss", $name, $username, $password);
		$sqlCMD->execute();
		
		#close
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
<?php
	$inputData = decodeJSON();
	$apiKeyUsername = "TheBeast";
	$apiKeyPassword = "WeLoveCOP4331";
	$databaseName = "COP4331";
	$connection = new mysqli("localhost", $apiKeyUsername, $apiKeyPassword, $databaseName);
	attemptLogin($inputData["username"], $inputData["password"], $connection);
	
	function attemptLogin($username, $password, $providedConnection) {
		$sqlCMD = $providedConnection->prepare("SELECT ID FROM Users WHERE Login=? AND Password=?");
		$sqlCMD->bind_param("ss", $username, $password);
		$sqlCMD->execute();
		$rowHolder = $sqlCMD->get_result();
		$index = 0;
		$userID = -1;
		while($rowInfo = $rowHolder->fetch_assoc()) {
			if( $index > 0 ) {
				break;
			}
			$index++;
			$userID = $rowInfo["ID"];
		}
		if ($index == 0) {
			returnWithInfo(-1, "", "", "Invalid User/Password combination");
		}
		else {
			$sqlCMD = $providedConnection->prepare("SELECT FirstName, LastName FROM Users WHERE ID=?");
			$sqlCMD->bind_param("i", $userID);
			$sqlCMD->execute();
			$rowHolder = $sqlCMD->get_result();
			$index = 0;
			$fName = "";
			$lName = "";
			while($rowInfo = $rowHolder->fetch_assoc()) {
				if( $index > 0 ) {
					break;
				}
				$index++;
				$fName = $rowInfo["FirstName"];
				$lName = $rowInfo["LastName"];
			}
			returnWithInfo((int) $userID, $fName, $lName, "");
		}
		$sqlCMD->close();
		$providedConnection->close();
	}


	function decodeJSON()
	{
		return json_decode(file_get_contents('php://input'), true);
	}
	
	function returnWithInfo( $userID, $firstName, $lastName, $error ) {
		$returnValue = '{"id":' . $userID . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":"' . $error . '"}';
		sendResultInfoAsJson( $returnValue );
	}
	
	function sendResultInfoAsJson( $obj ) {
		header('Content-type: application/json');
		echo $obj;
	}

?>
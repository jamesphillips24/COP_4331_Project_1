<?php
	$inData = decodeJSON();
	$apiKeyUsername = "TheBeast";
	$apiKeyPassword = "WeLoveCOP4331";
	$databaseName = "COP4331";
	$connection = new mysqli("localhost", $apiKeyUsername, $apiKeyPassword, $databaseName);
	if (checkForError($connection) == False) {
		selectMode($inData, $connection);	
	}
	$connection->close();
	
	function selectMode($dictInputData, $providedConnection) {
		switch ($dictInputData["mode"]) {
			case 0:
				changeName($dictInputData, $providedConnection);
				break;
			case 1:
				changePassword($dictInputData, $providedConnection);
				break;
			case 2:
				changeUserName($dictInputData, $providedConnection);
				break;
			case 3:
				changeEmail($dictInputData, $providedConnection);
				break;
			case 4:
				changePhoneNumber($dictInputData, $providedConnection);
				break;
			case 5:
				verifyCorrectPassword($dictInputData, $providedConnection);
				break;
			case 6:
				removeUserAccount($dictInputData, $providedConnection);
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
	
	function changeName($dictInputData, $providedConnection) {
		$sqlCMD = $providedConnection->prepare("UPDATE Users SET Name =? WHERE ID=?");
		$sqlCMD->bind_param("si", $dictInputData["Name"], $dictInputData["id"]);
		$sqlCMD->execute();
		$sqlCMD->close();
		returnWithInfo($dictInputData["id"], "", "", "");
		return;
	}
	
	function changePassword($dictInputData, $providedConnection) {
		if (hasSpaces($dictInputData["password"])) {
			returnWithInfo(-4, "", "", "Username and password cannot contain spaces");
			return;
		}
		elseif ($dictInputData["password"] != $dictInputData["confirmPass"]) {
			returnWithInfo(-1, "", "", "Password does not match");
			return;
		}
		else {
			$sqlCMD = $providedConnection->prepare("SELECT Password FROM Users WHERE ID=?");
			$sqlCMD->bind_param("i", $dictInputData["id"]);
			$sqlCMD->execute();
			$matchingRow = $sqlCMD->get_result()->fetch_assoc();
			if ($matchingRow["Password"] == $dictInputData["password"]) {
				$sqlCMD->close();
				returnWithInfo(-5, "", "", "Password cannot be the same as prior password");
				return;
			}
			$sqlCMD = $providedConnection->prepare("UPDATE Users SET Password =? WHERE ID=?");
			$sqlCMD->bind_param("si", $dictInputData["password"], $dictInputData["id"]);
			$sqlCMD->execute();
			$sqlCMD->close();
			returnWithInfo($dictInputData["id"], "", "", "");
			return;
		}
	}
	
	function changeUserName($dictInputData, $providedConnection) {
		if (hasSpaces($dictInputData["username"])) {
			returnWithInfo(-4, "", "", "Username and password cannot contain spaces");
			return;
		}
		else {
			$sqlCMD = $providedConnection->prepare("SELECT Login FROM Users WHERE Login=?");
			$sqlCMD->bind_param("s", $dictInputData["username"]);
			$sqlCMD->execute();
			$matchingRow = $sqlCMD->get_result()->fetch_assoc();
			if($matchingRow != null){
				$sqlCMD->close();
				returnWithInfo(-2, "", "", "Username already exists");
				return;
			}
			else {
				$sqlCMD = $providedConnection->prepare("UPDATE Users SET Login =? WHERE ID=?");
				$sqlCMD->bind_param("si", $dictInputData["username"], $dictInputData["id"]);
				$sqlCMD->execute();
				$sqlCMD->close();
				returnWithInfo($dictInputData["id"], "", "", "");
				return;
			}
		}
	}
	
	function changeEmail($dictInputData, $providedConnection) {
		if (hasSpaces($dictInputData["email"]) || hasPlus($dictInputData["email"])) {
			returnWithInfo(-7, "", "", "Emails cannot contain spaces or aliases");
			return;
		}
		else {
			$resultHold = emailDomainReader($dictInputData["email"]);
			if ($resultHold == -1) {
				returnWithInfo(-8, "", "", "Invalid email provided. Please use the traditional yourIdentifier@domainHere");
				return;
			}
			elseif ($resultHold == 0) {
				returnWithInfo(-9, "", "", "Please use a trustworthy email domain.");
				return;
			}
			else {
				$sqlCMD = $providedConnection->prepare("UPDATE Users SET Email =? WHERE ID=?");
				$sqlCMD->bind_param("s", $dictInputData["email"], $dictInputData["id"]);
				$sqlCMD->execute();
				$sqlCMD->close();
				returnWithInfo($dictInputData["id"], "", "", "");
				return;
			}
		}
	}
	
	function changePhoneNumber($dictInputData, $providedConnection) {
		if (strlen($dictInputData["phone"]) < 6 || strlen($dictInputData["phone"]) > 15) {
			returnWithInfo(-10, "", "", "Invalid Phone Number. By E.164, International Phone Numbers must have 6 to 15 digits (area+full country code (zeros included) included).");	
			return;
		}		
		$sqlCMD = $providedConnection->prepare("UPDATE Users SET Phone =? WHERE ID=?");
		$sqlCMD->bind_param("s", $dictInputData["phone"], $dictInputData["id"]);
		$sqlCMD->execute();
		$sqlCMD->close();
		returnWithInfo($dictInputData["id"], "", "", "");
		return;
		
	}
	
	function removeUserAccount($dictInputData, $providedConnection) {
		if (verifyPasswordHelper($dictInputData, $providedConnection)) {
			$sqlCMD = $providedConnection->prepare("DELETE FROM Contacts Where UserID=?");
			$sqlCMD->bind_param("i", $dictInputData["id"]);
			$sqlCMD->execute();
			$sqlCMD = $providedConnection->prepare("DELETE FROM Users Where ID=?");
			$sqlCMD->bind_param("i", $dictInputData["id"]);
			$sqlCMD->execute();
			$sqlCMD->close();
			return;
		}
		else {
			returnWithInfo(-14, "", "", "Access Denied. Invalid Password Authentification.");
			return;
		}
	}
	
	function verifyCorrectPassword($dictInputData, $providedConnection) {
		$result = verifyPasswordHelper($dictInputData, $providedConnection);
		if ($result) {
			returnWithInfo($dictInputData["id"], "", "", "");	
			return;
		}
		else {
			returnWithInfo(-11, "", "", "Invalid Password");
			return;
		}
	}
	
	function verifyPasswordHelper($dictInputData, $providedConnection) {
		$sqlCMD = $providedConnection->prepare("SELECT Password FROM Users WHERE ID=?");
		$sqlCMD->bind_param("i", $dictInputData["id"]);
		$sqlCMD->execute();
		$matchingRow = $sqlCMD->get_result()->fetch_assoc();
		if($matchingRow == null){
			$sqlCMD->close();
			return False;
		}
		else {
			if ($matchingrow["Password"] == $dictInputData["password"]) 
			{
				$sqlCMD->close();
				return True;
			}
			$sqlCMD->close();
			return False;
		}
	}
	

	function decodeJSON()
	{
		return json_decode(file_get_contents('php://input'), true);
	}
	
	function returnWithInfo( $userID, $firstName, $lastName, $error ) {
		$returnValue = '{"id":' . $userID . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":"' . $error . '"}';
		sendResultInfoAsJson( $returnValue );
	}
	
	function returnWithError($userID, $error) {
		$returnValue = '{"id":' . $userID . ',"firstName":"","lastName":"","error":"' . $error . '"}';
		sendResultInfoAsJson( $returnValue );		
	}
	
	function sendResultInfoAsJson( $obj ) {
		header('Content-type: application/json');
		echo $obj;
	}

	//checks if there are spaces in a string
	function hasSpaces($mystring) {
		return (strpos($mystring, ' ') !== false);
	}
	function hasPlus($myString) {
		return (strpos($myString, '+') !== false);
	}
	function hasAtSymbol($myString) {
		return (strpos($myString, '@') !== false);
	}
	function emailDomainReader($myString) {
		$emailArr = explode("@", $myString);
		if (count($emailArr) != 2) {
			return -1;
		}
		else {
			$eDomain = strtolower($emailArr[1]); 
			switch ($eDomain) {
				case "ucf.edu":
					return 1;
				# Top 30 Email Domains from 'https://ithy.com/article/top-email-domains-comprehensive-guide-2025-7t1j370x'
				case "gmail.com":
					return 1;
				case "outlook.com":
					return 1;
				case "yahoo.com":
					return 1;
				case "icloud.com":
					return 1;
				case "hotmail.com":
					return 1;
				case "qq.com":
					return 1;
				case "protonmail.com":
					return 1;
				case "aol.com":
					return 1;
				case "zoho.com":
					return 1;
				case "mail.com":
					return 1;
				case "gmx.com":
					return 1;
				case "yandex.com":
					return 1;
				case "tutanota.com":
					return 1;
				case "mail.ru":
					return 1;
				case "live.com":
					return 1;
				case "msn.com":
					return 1;
				case "naver.com":
					return 1;
				case "163.com":
					return 1;
				case "ymail.com":
					return 1;
				case "fastmail.com":
					return 1;
				case "web.de":
					return 1;
				case "t-online.de":
					return 1;
				case "comcast.net":
					return 1;
				case "neo.com":
					return 1;
				case "rediffmail.com":
					return 1;
				case "libero.it":
					return 1;
				case "freenet.de":
					return 1;
				case "hushmail.com":
					return 1;
				case "inbox.com":
					return 1;
				case "hotmail.co.uk":
					return 1;
				default:
					return 0;
			}
		}
	}
?>
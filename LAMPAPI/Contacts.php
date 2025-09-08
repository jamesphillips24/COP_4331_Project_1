<?php
	$inData = decodeJson();
	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "cop4331"); 	
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("SELECT * FROM Contacts WHERE Name LIKE ? OR Login LIKE ? OR Email LIKE ? AND FriendID=?");
		$stmt->bind_param("ss", $inData["searchterm"], $inData["searchterm"], $inData["searchterm"], $inData["ID"]);
		$stmt->execute();
		$result = $stmt->get_result();
		$jsVal = "{
			error: '',
			searchResults: {
				";
		$index = 0;
		while ( $row = $result->fetch_assoc()) {
			if ($index != 0) {
				$jsVal .= ",
				";
			}			
			$jsVal . "	{ Username:" . $row["Username"] . ", Name: " . $row["Name"] . ", Email Address: " . $row["Email"] . ", Phone Number: " . $row["Phone"] . ", Error: " . "" . " }";
			$index += 1;
		}
		$jsVal .= "
		}
		}";
		sendResultInfoAsJson($jsVal);

		$stmt->close();
		$conn->close();
	}
	
	function decodeJson()
	{
		return json_decode(file_get_contents('php://input'), true);
	}
	
	function returnWithError($err) {
		$jsVal = "{
			";
		$jsVal . "error: " . $err . ",
		searchResults: {
			
		}
		}";
		sendResultInfoAsJson($error);
	}
	
	function sendResultInfoAsJson( $obj ) {
		header('Content-type: application/json');
		echo $obj;
	}
	
?>

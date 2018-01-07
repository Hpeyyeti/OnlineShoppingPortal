<?php
  require_once 'functions.php';
if(
	isset($_POST['firstName']) && 
	isset($_POST['lastName']) && 
	isset($_POST['userName']) && 
	isset($_POST['password']) && 
	isset($_POST['email']) && 
	isset($_POST['phoneNumber']) && 
	isset($_POST['streetAddress']) &&
	isset($_POST['zipcode']) &&
	isset($_POST['city']) &&
	isset($_POST['state'])
	)
{
	$firstName = $_POST['firstName'];
	$lastName = $_POST['lastName'];
	$userName= $_POST['userName'];	
	$password = sha1($_POST['password']);
	$email = $_POST['email'];
	$phoneNumber = $_POST['phoneNumber'];
	$streetAddress = $_POST['streetAddress'];
	$zipCode= $_POST['zipcode'];
	$city= $_POST['city'];
	$state= $_POST['state'];
	$dbh = getDbConnection();
	$stateId = "";
	$dbh->beginTransaction();
	$prepStmtSql = "SELECT StateId FROM state WHERE LOWER(StateName) = LOWER(:state)";
	$statement = $dbh->prepare($prepStmtSql);
	$statement->execute(array(':state' => $state));
	$rows = $statement->fetchAll();
	foreach ($rows as $row) {
		$stateId = $row["StateId"];
	}

	if ($stateId != "")
	{
		$cityId = "";
		$prepStmtSql = "SELECT CityId FROM city WHERE LOWER(CityName) = LOWER(:city)";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':city' => $city));
		$rows = $statement->fetchAll();
		foreach ($rows as $row) {
			$cityId = $row["CityId"];
		}
		
		if ($cityId == "")
		{
			$prepStmtSql = "INSERT INTO city (CityName, StateId) VALUES (:city, :stateId)";
			$statement = $dbh->prepare($prepStmtSql);
			$statement->execute(array(':city' => $city, ':stateId' => $stateId));
			
			$prepStmtSql = "SELECT CityId FROM city WHERE CityName = :city";
			$statement = $dbh->prepare($prepStmtSql);
			$statement->execute(array(':city' => $city));
			$rows = $statement->fetchAll();
			foreach ($rows as $row) {
				$cityId = $row["CityId"];
			}
		}
		
		$zipCodeId = "";
		$prepStmtSql = "SELECT ZipcodeId FROM zipcode WHERE Zipcode = :zipCode";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':zipCode' => $zipCode));
		$rows = $statement->fetchAll();
		foreach ($rows as $row) {
			$zipCodeId = $row["ZipcodeId"];
		}
		
		if ($zipCodeId == "")
		{
			$prepStmtSql = "INSERT INTO zipcode (Zipcode, CityId) VALUES (:zipCode, :cityId)";
			$statement = $dbh->prepare($prepStmtSql);
			$statement->execute(array(':zipCode' => $zipCode, ':cityId' => $cityId));
			
			$prepStmtSql = "SELECT ZipcodeId FROM zipcode WHERE Zipcode = :zipCode";
			$statement = $dbh->prepare($prepStmtSql);
			$statement->execute(array(':zipCode' => $zipCode));
			$rows = $statement->fetchAll();
			foreach ($rows as $row) {
				$zipCodeId = $row["ZipcodeId"];
			}	
		}

		$prepStmtSql = "INSERT INTO customer (UserName, PasswordHash) VALUES (:userName, :password)";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':userName' => $userName, ':password' => $password));
		
		$prepStmtSql = "SELECT CustomerId FROM customer WHERE UserName = :userName";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':userName' => $userName));
		$rows = $statement->fetchAll();
		foreach ($rows as $row) {
			$customerId = $row["CustomerId"];
		}
		
		$prepStmtSql = "INSERT INTO addressablecontact 
							(FirstName, LastName, Email, StreetAddress, CustomerId, ZipcodeId) 
						VALUES 
							(:firstName, :lastName, :email, :streetAddress, :customerId, :zipCodeId)";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':firstName' => $firstName, ':lastName' => $lastName, ':email' => $email, ':streetAddress' => $streetAddress, ':customerId' => $customerId, ':zipCodeId' => $zipCodeId));
		
		$addressableContactId = "";
		$prepStmtSql = "SELECT AddressableContactId FROM addressablecontact WHERE CustomerId = :customerId";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':customerId' => $customerId));
		$rows = $statement->fetchAll();
		foreach ($rows as $row) {
			$addressableContactId = $row["AddressableContactId"];
		}

		$prepStmtSql = "INSERT INTO contactphone (PhoneNumber, AddressableContactId) VALUES (:phoneNumber, :addressableContactId)";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':phoneNumber' => $phoneNumber, ':addressableContactId' => $addressableContactId));
		$dbh->commit();

		$dbh = null;
		
		echo "Customer registration is successfully completed.";
		echo "<br/>";
		echo "<br/>";
		echo "<a href='Login.html'>Please log-in with your credentials</a>";
	}
	else 
	{
		echo "Invalid State entered!! Enter a full name with the first letter uppercased, like California";
		echo "<br/>";
		echo "<a href='CustomerRegistration.html'>Back to Customer Registration page</a>";
	}
}
else 
{
	echo "All fields should be filled. Either one or many fields are empty.";
	echo "<br/>";
	echo "<a href='CustomerRegistration.html'>Back to Customer Registration page</a>";
} 
?>
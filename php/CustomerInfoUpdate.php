<?php
require_once 'functions.php';	
session_start();
if(
	isset($_POST['firstName']) || 
	isset($_POST['lastName']) ||
	isset($_POST['password']) ||
	isset($_POST['email']) || 
	isset($_POST['phoneNumber']) ||
	isset($_POST['streetAddress']) ||
	isset($_POST['zipcode']) ||
	isset($_POST['city']) ||
	isset($_POST['state'])
  )
{
	$userName= $_SESSION['UserName'];
	
	$firstName = $_POST['firstName'];
	$lastName = $_POST['lastName'];
	$password = $_POST['password'];
	if($password != "")
	{
		$password = sha1($_POST['password']);
	}
	else 
	{
		$password = "";
	}
	$email = $_POST['email'];
	$phoneNumber = $_POST['phoneNumber'];
	$streetAddress = $_POST['streetAddress'];
	$zipCode= $_POST['zipcode'];
	$city= $_POST['city'];
	$state= $_POST['state'];
	$dbh = getDbConnection();
	$dbh->beginTransaction();

	$zipCodeId = "";
	if ($state != "" && $zipCode != "" &&	$city != "")
	{
		$stateId = "";
		$prepStmtSql = "SELECT StateId FROM state WHERE LOWER(StateName) = LOWER(:state)";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':state' => $state));
		$rows = $statement->fetchAll();
		foreach ($rows as $row) {
			$stateId = $row["StateId"];
		}
		
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
	}
	else if (($state == "" || $zipCode == "" ||	$city == "") && !($state == "" && $zipCode == "" &&	$city == ""))
	{
		echo "Value not entered for State or ZipCode or City. All the 3 values has to be provided for updating State or ZipCode or City.";
		echo "<br/>";
		echo "<a href='CustomerInfoUpdate.html'>Back to Customer information update page</a>";
	}
	
	$prepStmtSql = "SELECT CustomerId FROM customer WHERE UserName = :userName";
	$statement = $dbh->prepare($prepStmtSql);
	$statement->execute(array(':userName' => $userName));
	$rows = $statement->fetchAll();
	foreach ($rows as $row) {
		$customerId = $row["CustomerId"];
	}

	if($firstName != "")
	{
		$prepStmtSql = "UPDATE addressablecontact SET FirstName = :firstName WHERE CustomerId = :customerId";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':firstName' => $firstName, ':customerId' => $customerId));
	}
	if($lastName != "")
	{
		$prepStmtSql = "UPDATE addressablecontact SET LastName = :lastName WHERE CustomerId = :customerId";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':lastName' => $lastName, ':customerId' => $customerId));
	}
	if($password != "")
	{
		$prepStmtSql = "UPDATE customer SET PasswordHash = :password WHERE UserName = :userName";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':userName' => $userName, ':password' => $password));
	}
	if($email != "")
	{
		$prepStmtSql = "UPDATE addressablecontact SET Email = :email WHERE CustomerId = :customerId";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':email' => $email, ':customerId' => $customerId));
	}
	if($streetAddress != "")
	{
		$prepStmtSql = "UPDATE addressablecontact SET StreetAddress = :streetAddress WHERE CustomerId = :customerId";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':streetAddress' => $streetAddress, ':customerId' => $customerId));
	}
	if($zipCodeId != "")
	{
		$prepStmtSql = "UPDATE addressablecontact SET ZipcodeId = :zipCodeId WHERE CustomerId = :customerId";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':zipCodeId' => $zipCodeId, ':customerId' => $customerId));
	}
	if($phoneNumber != "")
	{
		$addressableContactId = "";
		$prepStmtSql = "SELECT AddressableContactId FROM addressablecontact WHERE CustomerId = :customerId";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':customerId' => $customerId));
		$rows = $statement->fetchAll();
		foreach ($rows as $row) {
			$addressableContactId = $row["AddressableContactId"];
		}
		
		$prepStmtSql = "UPDATE contactphone SET PhoneNumber = :phoneNumber WHERE AddressableContactId = :addressableContactId";
		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(array(':phoneNumber' => $phoneNumber, ':addressableContactId' => $addressableContactId));
	}
	$dbh->commit();

	$dbh = null;

	echo "Customer Info updated successfully";
	echo "<br/>";
	echo "<br/>";
	echo "<a href='Index.html'>Go to Index page</a>";
}
else 
{
	echo "No new values provided for updates!!";
	echo "<br/>";
	echo "<a href='CustomerInfoUpdate.html'>Back to Customer information update page</a>";
} 
?>
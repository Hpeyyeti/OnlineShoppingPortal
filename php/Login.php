<?php
require_once 'functions.php';	
session_start();
if(
	isset($_POST['userName']) && 
	isset($_POST['password'])
  )
{
	$userName= $_POST['userName'];	
	$password = $_POST['password'];
	
	$dbh = getDbConnection();
	$passwordHash="";
	$prepStmtSql = "SELECT PasswordHash FROM customer WHERE LOWER(UserName) = LOWER(:userName)";
	$statement = $dbh->prepare($prepStmtSql);
	$statement->execute(array(':userName' => $userName));
	$rows = $statement->fetchAll();
	foreach ($rows as $row) {
		$passwordHash = $row["PasswordHash"];
	}	
	if (sha1($password) === $passwordHash)
	{	
		$_SESSION['UserName']=$userName;
		header("Location: Index.html");
	}
	else 
	{
		echo "Invalid credentials. Please try again!!";
		echo "<br/>";
		echo "<br/>";
		echo "<a href='Login.html'>Back to Login page</a>";
	}
}
else 
{
	echo "User ID or/and Password are empty!!";
	echo "<br/>";
	echo "<br/>";
	echo "<a href='Login.html'>Back to Login page</a>";
} 
?>
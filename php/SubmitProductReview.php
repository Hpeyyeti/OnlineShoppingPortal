<?php
require_once 'functions.php';	
	session_start();
if(
	isset($_POST['rating']) && 
	isset($_POST['review']) &&
	isset($_POST['ProductId']) &&
	isset($_POST['SubcategoryName'])
  )
{
	$userName=$_SESSION['UserName'];
	$rating=$_POST['rating'];
	$review=$_POST['review'];
	$productId=$_POST['ProductId'];
	$subcategoryName=$_POST['SubcategoryName'];
	try {
			$dbh = getDbConnection();

	}   catch (PDOException $ex) {
		printf("Connect failed: %s\n", $ex->getMessage());
		exit();
	}
	$dbh->beginTransaction();
	$prepStmtSql = "SELECT CustomerId FROM customer WHERE UserName= :userName";
	$statement = $dbh->prepare($prepStmtSql);
	$statement->execute(array(':userName' => $userName));
	$rows = $statement->fetchAll();	
	foreach ($rows as $row) {
		$customerId = $row["CustomerId"];
	}
	
	$prepStmtSql = "INSERT INTO review 
						(Rating, ReviewComment, CustomerId, ProductId, ReviewDate)
					VALUES
						(:rating, :review, :customerId, :productId, NOW())";
	$statement = $dbh->prepare($prepStmtSql);
	$statement->execute(array(':rating' => $rating, ':review' => $review, ':customerId' => $customerId, ':productId' => $productId));
	$dbh->commit();

	echo "<strong> Product rating and review added successfully</strong>";
	echo "<br/>";
	echo "<br/>";
	echo "<a href='ProductSearchDB.php?subcategory_name=".$subcategoryName."'>Rate another product</a>";
	echo "<footer id='foot01'></footer>";
}
else
{
	echo "Please fill-in all the details and submit";
	echo "<br/>";
	echo "<br/>";
	echo "<a href='ProductSearchDB.php?subcategory_name=".$_POST['SubcategoryName']."'>Back</a>";
	
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Customers</title>
	<meta charset="UTF-8">
    <style>
        .search-results {
            border: 1px solid #ccc;
            border-collapse: collapse;
        }
        .search-results td, .search-results th {
            border: 1px solid #ccc;
            text-align: center;
			max-width: 350px;
        }
		tr:nth-child(even){
			background-color: #efeef8
		}
		tr {
            padding: 3px;
		}		
		th {
			background-color: #996600;
			color: white;
			padding: 10px;
		}
    </style>
</head>
<body>
	<table class=search-results>
	<?php
		require_once 'functions.php';
		session_start();
		$userName= $_SESSION['UserName'];
		try {
			$dbh = getDbConnection();
		} catch (PDOException $ex) {
			printf("Connect failed: %s\n", $ex->getMessage());
			exit();
		}
		$prepStmtSql = "SELECT 
							B.OrderId, B.OrderStatus, B.OrderDate, C.PaymentAmount
						FROM 
							CUSTOMER A, `ORDER` B, PAYMENT C 
						WHERE 
							A.UserName = :userName AND
							A.CustomerId = B.CustomerId AND
							B.PaymentId = C.PaymentId";

		$statement = $dbh->prepare($prepStmtSql);
		$statement->execute(['userName' => $userName]);
		$rows = $statement->fetchAll();
		if (count($rows) == 0) 
		{
			echo "<tr>";
			echo "<td><strong>No orders found for the User Name: ".$userName."</strong></td>";
			echo "</tr>";
		} 
		else 
		{
	?>
	
	
		<tr>
			<th>Order ID</th>
			<th>Order Status</th>
			<th>Order Date</th>
			<th>Payment Amount</th>
		</tr>	
	<?php		
			foreach ($rows as $row) {
				printf(
						"<tr>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
						</tr>\n",
						htmlspecialchars($row["OrderId"]),
						htmlspecialchars($row["OrderStatus"]),
						htmlspecialchars($row["OrderDate"]),
						htmlspecialchars($row["PaymentAmount"])
					);
			}
		}
	?>  
	</table>
	<br><br>
	<a href="Index.html">Back</a>
</body>
</html>
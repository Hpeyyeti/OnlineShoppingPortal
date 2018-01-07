<!DOCTYPE html>
<html lang="en-US">
<head>
    <style>
		tr:nth-child(even){
			background-color: #efeef8
		}
		tr {
            padding: 3px;
		}		
		th {
			background-color: #996600;
			color: white;
			padding: 5px;
		}
		textarea {
			border: 1px solid #b7b7b7;
			width: 350px;
		}
    </style>
	
	<script>
		function onProdReviewSubmitButtonClick() {
			var form = document.forms['ProdReviewForm'];
			
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'ProductId';
			
			input.value = document.getElementById("productId").value;
			form.appendChild(input);
			
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'SubcategoryName';
		
			input.value = document.getElementById("subcategoryName").value;
			form.appendChild(input);			
			document.getElementById("ProdReviewForm").submit();
		}
	</script>	
</head>
<body>    
	<form id="ProdReviewForm" action="SubmitProductReview.php" method="post" autocomplete="off">
		<div>
			<fieldset>
				<legend>Review this product</legend>	
				<table class=search-results>
					<tr>
						<th>Brand Name</th><th>Product Name</th><th>Model</th><th>Description</th><th>Price</th>
					</tr>				
					<?php
					  require_once 'functions.php';
						session_start();
						$userName= $_SESSION['UserName'];
						if (
							isset($_POST['SelectedProductId']) && isset($_POST['SubcategoryName'])
						   ) 
						{
							try {
								
							$dbh = getDbConnection();
							} catch (PDOException $ex) {
								printf("Connect failed: %s\n", $ex->getMessage());
								exit();
							}
							$productId = $_POST['SelectedProductId'];
							$subcategoryName = $_POST['SubcategoryName'];
							$prepStmtSql = "SELECT
												B.BrandName, A.ProductName, A.Model, A.Description, A.Price
											FROM 
												PRODUCT A, BRAND B
											WHERE 
												A.BrandId = B.BrandId
												AND A.ProductId = :productId";
							$statement = $dbh->prepare($prepStmtSql);
							$statement->execute(array(':productId' => $productId));
							$rows = $statement->fetchAll();	
							foreach ($rows as $row) {
								printf(
									"<tr>
										<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>
									</tr>",
									htmlspecialchars($row['BrandName']),
									htmlspecialchars($row['ProductName']),
									htmlspecialchars($row['Model']),
									htmlspecialchars($row['Description']),
									htmlspecialchars($row['Price'])
								);
							}
						}
					?>
				</table>				
				<p>
					<label for="rating">Rating</label>
					<input type="radio" name="rating" value="5" /> 5 
					<input type="radio" name="rating" value="4" /> 4
					<input type="radio" name="rating" value="3" /> 3 
					<input type="radio" name="rating" value="2" /> 2 
					<input type="radio" name="rating" value="1" /> 1
				</p>
				<p>
					<label for="review">Review</label>
					<textarea name="review" rows="8" cols="40"></textarea>
				</p>
				<p>
					<button type="button" onclick="onProdReviewSubmitButtonClick()">Submit Review</button>
				</p>
				<input type="hidden" value=<?php echo $productId; ?> id="productId">
				<input type="hidden" value=<?php echo $subcategoryName; ?> id="subcategoryName">
			</fieldset>
		</div>
	</form>
	<a href="ProductSearch.html">Back</a>
	
</body>
</html>
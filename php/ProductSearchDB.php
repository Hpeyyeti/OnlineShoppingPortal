<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Product Search</title>
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
	
	<script>
		function onProdReviewButtonClick() {
			var x = document.getElementsByName('selectedProduct');
			var prodID="";
			for(var i=0; i<x.length;i++) 
			{
				if(x[i].checked){
					prodID = x[i].value;
				}
			}
			var form = document.forms['ProdDisplayForm'];
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'SelectedProductId';
			input.value = prodID;
			form.appendChild(input);
			
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'SubcategoryName';
			input.value = document.getElementById("subcategoryName").value;
			form.appendChild(input);			
			document.getElementById("ProdDisplayForm").submit();
		}
	</script>
</head>
<body>    
	<form id="ProdDisplayForm" action="ProductReview.php" method="post" autocomplete="off">
		<div>
			<table class=search-results>	
				<?php
				   require_once 'functions.php';
					if (isset($_GET['subcategory_name'])) {
						try {
							$dbh = getDbConnection();
						} catch (PDOException $ex) {
							printf("Connect failed: %s\n", $ex->getMessage());
							exit();
						}
						$subcategoryName = $_GET['subcategory_name'];
						$prepStmtSql = "SELECT
											C.BrandName, B.ProductName, B.ProductId, B.Model, B.Description, B.Price
										FROM 
											SUBCATEGORY A, PRODUCT B, BRAND C
										WHERE 
											LOWER(A.SubCategoryName) = LOWER(:subcategoryName) AND
											A.SubCategoryId = B.SubCategoryId AND
											B.BrandId = C.BrandId";
						$statement = $dbh->prepare($prepStmtSql);
						$statement->execute(array(':subcategoryName' => $subcategoryName));
						$rows = $statement->fetchAll();
						if (count($rows) == 0) 
						{
							echo "<tr>";
							echo "<td><strong>No product found for the Sub-Category Name: ".$subcategoryName."</strong></td>";
							echo "</tr>";
						} else {
				?>
				<tr>
					<th>Brand Name</th><th>Product Name</th><th>Model</th><th>Description</th><th>Price</th><th>Select</th>
				</tr>
				<?php			
							foreach ($rows as $row) {
								printf(
									"<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><input type='radio' name='selectedProduct' value='%s' /></td></tr>",
									htmlspecialchars($row['BrandName']),
									htmlspecialchars($row['ProductName']),
									htmlspecialchars($row['Model']),
									htmlspecialchars($row['Description']),
									htmlspecialchars($row['Price']),
									htmlspecialchars($row['ProductId'])
								);
							}
						}
					}
				?>
			</table>
		</div>
		<?php
		if (count($rows) != 0) {
			echo "<p align='center'>";
			echo 	"<button type='button' onclick='onProdReviewButtonClick()'>Submit a review</button>";
			echo "</p>";
		}	
		?>
		<input type="hidden" name="subcategoryName" id="subcategoryName" value=<?php echo $subcategoryName; ?> >
	</form>
	<br>
	<a href="ProductSearch.html">Back</a>

	
</body>
</html>
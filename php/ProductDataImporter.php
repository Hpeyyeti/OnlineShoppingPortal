<?php


class ProductDataImporter
{
    /** @var string  */
    private $file;

    private $brandNameToIdMap = [];

    private $subCategoryNameToIdMap = [];

    private $mainCategoryId;

    /** @var PDO */
    private $dbh;

    /**
     * @param PDO $dbh
     * @param string $file
     */
    public function __construct(PDO $dbh, $file)
    {
        $this->dbh = $dbh;
        $this->file = $file;
    }

    function import()
    {
        $this->cleanUpDb();
        $this->makeSuperParentCategory('Main');
        $this->parseAndInsert();
    }

    private function parseAndInsert()
    {
        $rows = $this->getProductRows();
        $sql = "
        insert into product 
          (SubCategoryId, BrandId, ProductName, Description, ImageUrl, Model, Price) 
        values 
          (?,?,?,?,?,?,?)
        ";
        $insertStmt = $this->dbh->prepare($sql);

        foreach ($rows as $i => $row) {
            list($partNumber, $brandName, $dataSource, $subCategoryName, $productName, $ean, $marketPresence, $family, $description) = $row;
            if (strlen($description) < 16) {
                continue;
            }

            $subCategoryId = $this->getSubCategoryId($subCategoryName);
            $brandId = $this->getBrandId($brandName);
            $price = $this->guessPrice();
            $insertStmt->execute([
                $subCategoryId, $brandId, $productName, $description, "images/$partNumber.jpg", $partNumber, $price
            ]);
            printf("subCategoryId=%s, brandId=%s, productName=%s, description=%s, partNumber=%s, price=%s\n", $subCategoryId, $brandId, $productName, $description, $partNumber, $price);

            if ($i > 10000) {
                break;
            }
        }
    }

    /**
     * The file is 1 record per line, with columns separated by 1 or more tabs.
     * We parse this kinda like a csv file, and then insert the rows into our db.
     */
    private function getProductRows()
    {
        $rows = [];
        $fp = fopen($this->file, 'r');
        fgets($fp, 10000); // Throw away header row.
        $i = 0;
        // Read 1 line.
        while ($line = fgets($fp, 10000)) {
            $i++;

            // Standardize multiple tabs into 1 tab per column.
            $prepped = str_replace("\t\t\t", "\t", $line);

            // Parse the line as a tsv.
            $row = str_getcsv($prepped, "\t");

            if (!$row) {
                echo "failed to parse line $i\n";
                continue;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function getBrandId($brandName)
    {
        // Inset a new record if this is the first time we've seen this brand name.
        if (!isset($this->brandNameToIdMap[$brandName])) {
            $sql = "insert into BRAND (BrandName) values (?)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([$brandName]);
            $brandId = $this->dbh->lastInsertId();
            $this->brandNameToIdMap[$brandName] = $brandId;
        }

        return $this->brandNameToIdMap[$brandName];
    }

    private function getSubCategoryId($subCategoryName)
    {
        // Inset a new record if this is the first time we've seen this brand name.
        if (!isset($this->subCategoryNameToIdMap[$subCategoryName])) {
            $sql = "insert into SUBCATEGORY (SubCategoryName, CategoryId) values (?, ?)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([$subCategoryName, $this->mainCategoryId]);
            $subcategoryId = $this->dbh->lastInsertId();
            $this->subCategoryNameToIdMap[$subCategoryName] = $subcategoryId;
        }

        return $this->subCategoryNameToIdMap[$subCategoryName];
    }

    private function cleanUpDb()
    {
        $this->dbh->exec("delete from PRODUCT");
        $this->dbh->exec("delete from subcategory");
        $this->dbh->exec("delete from category");
        $this->dbh->exec("delete from brand");
    }

    private function makeSuperParentCategory($categoryName)
    {
        $sql = "insert into CATEGORY (CategoryName) values (?)";
        $stmt = $this->dbh->prepare($sql);
        // Our data set doesn't have nested categories, so we make just use a single fictitious Category
        // and make everything a SubCategory of that. Works great.
        $stmt->execute([$categoryName]);
        $this->mainCategoryId = $this->dbh->lastInsertId();
    }

    private function guessPrice()
    {
        $a = ['99', '99', '99', '99', '99', '99', '99', '98', '60', '00'];
        $cents = $a[mt_rand(0, count($a) - 1)];

        $a = [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 10, 10, 10, 10, 10, 100, 100, 1000];
        $dollars = $a[mt_rand(0, count($a) - 1)] * mt_rand(1, 10);

        // Make stuff like 200.XX into 199.XX
        if ($dollars > 10 && $dollars % 100 === 0) {
            $dollars -= 1;
        }

        return "$dollars.$cents";
    }
}
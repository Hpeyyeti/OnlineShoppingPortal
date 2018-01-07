<?php


class UserDataImporter
{
    /** @var string  */
    private $file;
    
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
        $this->parseAndInsert();
    }

    private function parseAndInsert()
    {
        $rows = $this->getCsvRows();

        foreach ($rows as $i => $row) {
            list($firstName, $lastName, $email, $streetAddress, $zipcode, $state, $city, $userName, $phoneNumber) = $row;
            $sql = "insert into simulateduserdata (FirstName, LastName, Email, StreetAddress, Zipcode, State, City, UserName, PhoneNumber, PasswordHash) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([$firstName, $lastName, $email, $streetAddress, $zipcode, $state, $city, $userName, $phoneNumber, $this->simulatePasswordHash($userName)]);

            if ($i > 10000) {
                break;
            }
        }
    }

    /**
     * The file is 1 record per line, with columns separated by 1 or more tabs.
     * We parse this kinda like a csv file, and then insert the rows into our db.
     */
    private function getCsvRows()
    {
        $rows = [];
        $fp = fopen($this->file, 'r');
        fgets($fp, 10000); // Throw away header row.
        $i = 0;
        // Read 1 line.
        while ($line = fgets($fp, 10000)) {
            $i++;

            // Parse the line as a csv.
            $row = str_getcsv($line);

            if (!$row) {
                echo "failed to parse line $i\n";
                continue;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function simulatePasswordHash($userName)
    {
        $salt = " Morton's Salt";
        return sha1($userName . $salt);
    }

    private function cleanUpDb()
    {
        $this->dbh->exec("delete from simulateduserdata");
    }
}
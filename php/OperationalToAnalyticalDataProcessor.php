<?php


class OperationalToAnalyticalDataProcessor
{
    /** @var PDO */
    private $dbh;

    private $file;

    public function __construct(PDO $dbh, $file)
    {
        $this->dbh = $dbh;
        $this->file = $file;
    }

    public function process()
    {
        $txt = file_get_contents($this->file);

        echo "Filling Calendar table with more dates...\n";
        $this->fillDatesUpToToday();

        $this->dbh->beginTransaction();

        echo "Synchronizing tables...\n";
        preg_match_all('#insert\s+into .*?;#si', $txt, $matches);
        foreach ($matches[0] as $sqlStmt) {
            $rowsAffected = $this->dbh->exec($sqlStmt);
            echo "Running $sqlStmt\n";
            echo "Rows affected $rowsAffected\n\n\n\n";
        }

        $this->dbh->commit();
        echo "Done";

    }

    private function fillDatesUpToToday() {
        $now = new DateTime();
        $today = $now->format('Y-m-d');
        $this->dbh->exec("call cmpe226a6.fillDates('2010-01-01', '$today')");
    }


}
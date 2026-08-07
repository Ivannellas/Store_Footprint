<?php

class FootprintModel
{
    private mysqli $dbConn;
    private array $allowedTables = ['tbl_store_footprint', 'tbl_parking_footprint'];

    public function __construct(mysqli $conn)
    {
        $this->dbConn = $conn;
    }

    public function addFootprint(string $tableName, string $personnel, string $date, string $time, int $count): bool
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true)) {
            return false;
        }

        $query = "INSERT INTO {$tableName} (opersonnel, odate, otime, ocount) 
                  VALUES (?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($this->dbConn, $query)) {
            mysqli_stmt_bind_param($stmt, "sssi", $personnel, $date, $time, $count);
            $success = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $success;
        }

        return false;
    }
  
    public function getFootprints(string $tableName): array
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true)) {
            return [];
        }

        $records = [];
        $query = "SELECT otableid, opersonnel, odate, otime, ocount 
                  FROM {$tableName} 
                  ORDER BY odate DESC, otime DESC";

        $result = mysqli_query($this->dbConn, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $records[] = $row;
            }
            mysqli_free_result($result); 
        }

        return $records;
    }
}
<?php

class FootprintModel
{
    private mysqli $db;
    private array $allowedTables = ['tbl_store_footprint', 'tbl_parking_footprint'];

    public ?string $oPersonnel = null;
    public ?string $oDate = null;
    public ?string $oTime = null;
    public ?int $oCount = null;

    public function __construct(mysqli $conn)
    {
        $this->db = $conn;
    }

/*==============================================================
//   Add a new footprint record                               //
=============================================================*/
    public function AddFootprint(string $tableName): bool
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true)) {
            return false;
        }

        $query = "INSERT INTO {$tableName} (opersonnel, odate, otime, ocount) 
                  VALUES (?, ?, ?, ?)";

        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param("sssi", $this->oPersonnel, $this->oDate, $this->oTime, $this->oCount);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } else {
            error_log("Failed to add footprint: " . $this->db->error);
            return false;
        }
    }

/*==============================================================
//   Retrieve footprints                                       //
=============================================================*/
    public function GetFootprints(string $tableName): array
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true)) {
            return [];
        }

        $records = [];
        $query = "SELECT otableid, opersonnel, odate, otime, ocount 
                  FROM {$tableName} 
                  ORDER BY odate DESC, otime DESC";

        $result = mysqli_query($this->db, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $records[] = $row;
            }
            mysqli_free_result($result);
        }

        return $records;
    }
}
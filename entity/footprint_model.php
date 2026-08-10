<?php

class FootprintModel
{
    private mysqli $db;
    private array $allowedTables = ['tbl_store_footprint', 'tbl_parking_footprint'];

    public ?string $oPersonnel = null;
    public ?string $oDate = null;
    public ?string $oStartTime = null;
    public ?string $oEndTime = null;
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

        $query = "INSERT INTO {$tableName} (opersonnel, odate, ostarttime, oendtime, ocount) 
                  VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param("ssssi", $this->oPersonnel, $this->oDate, $this->oStartTime, $this->oEndTime, $this->oCount);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } else {
            error_log("Failed to add footprint: " . $this->db->error);
            return false;
        }
    }

    /*==============================================================
    //   Retrieve footprints for current day only                 //
    =============================================================*/
    public function GetFootprints(string $tableName, ?string $date = null): array
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true)) {
            return [];
        }

        // Default to today's date if no specific date is passed
        $targetDate = $date ?? date('m-d-Y');

        $records = [];
        $query = "SELECT 
                    otableid, 
                    opersonnel, 
                    odate, 
                    TIME_FORMAT(ostarttime, '%h:%i %p') AS ostarttime, 
                    TIME_FORMAT(oendtime, '%h:%i %p') AS oendtime, 
                    ocount 
                  FROM {$tableName} 
                  WHERE odate = ? 
                  ORDER BY ostarttime DESC";

        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param("s", $targetDate);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }
            $stmt->close();
        } else {
            error_log("Failed to fetch footprints: " . $this->db->error);
        }

        return $records;
    }

    /*==============================================================
// Check if personnel already logged for this time range       //
=============================================================*/
    public function HasExistingLog(string $tableName, string $personnel, string $date, string $startTime, string $endTime): bool
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true)) {
            return false;
        }

        $query = "SELECT COUNT(*) AS total 
              FROM {$tableName} 
              WHERE LOWER(TRIM(opersonnel)) = LOWER(TRIM(?)) 
                AND odate = ? 
                AND ostarttime = ? 
                AND oendtime = ?";

        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param("ssss", $personnel, $date, $startTime, $endTime);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return ((int)($result['total'] ?? 0)) > 0;
        }

        return false;
    }
}

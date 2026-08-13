<?php

class FootprintModel
{
    private mysqli $db;
    private array $allowedTables = ['tbl_store_footprint', 'tbl_parking_footprint'];

    public function __construct(mysqli $conn)
    {
        $this->db = $conn;
    }

    /*==============================================================
    //   Add a new footprint record                               //
    =============================================================*/
    public function AddFootprint(string $tableName, array $data): bool
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true)) {
            return false;
        }

        $query = "INSERT INTO {$tableName} (added_by, opersonnel, odate, otimerange, ocount, void_status) 
                  VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param(
                "ssssii",
                $data['added_by'],
                $data['opersonnel'],
                $data['odate'],
                $data['otimerange'],
                $data['ocount'],
                $data['void_status']
            );
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        error_log("Failed to add footprint: " . $this->db->error);
        return false;
    }

    /*==============================================================
    //   Retrieve footprints (Filtered by date or overall history) //
    =============================================================*/
    public function GetFootprints(string $tableName, ?string $date = null): array
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true)) {
            return [];
        }

        $records = [];
        $hasDateFilter = !empty($date);

        $query = "SELECT 
                    added_by,
                    created_at,
                    otableid, 
                    opersonnel, 
                    odate, 
                    otimerange, 
                    ocount,
                    void_status
                  FROM {$tableName}";

        if ($hasDateFilter) {
            $query .= " WHERE odate = ?";
        }

        $query .= " ORDER BY odate DESC, otableid DESC";

        if ($stmt = $this->db->prepare($query)) {
            if ($hasDateFilter) {
                $stmt->bind_param("s", $date);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $records[] = $row;
                }
            }
            $stmt->close();
        } else {
            error_log("Failed to fetch footprints: " . $this->db->error);
        }

        return $records;
    }

    /*==============================================================
    //   Check if personnel already logged for this time range     //
    =============================================================*/
    public function HasExistingLog(string $tableName, string $personnel, string $date, string $timeRange): bool
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true)) {
            return false;
        }

        $query = "SELECT COUNT(*) AS total 
                  FROM {$tableName} 
                  WHERE LOWER(TRIM(opersonnel)) = LOWER(TRIM(?)) 
                    AND odate = ? 
                    AND otimerange = ?
                    AND (void_status IS NULL OR void_status != 0)";

        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param("sss", $personnel, $date, $timeRange);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            return ((int)($row['total'] ?? 0)) > 0;
        }

        return false;
    }

    /*==============================================================
    //   Void a footprint record (set void_status = 0)            //
    ==============================================================*/
    public function VoidFootprint(string $tableName, int $tableId): bool
    {
        $tableName = trim($tableName);

        if (!in_array($tableName, $this->allowedTables, true) || $tableId <= 0) {
            return false;
        }

        $query = "UPDATE {$tableName} SET void_status = 0 WHERE otableid = ?";

        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param("i", $tableId);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        error_log("Failed to void footprint: " . $this->db->error);
        return false;
    }
}

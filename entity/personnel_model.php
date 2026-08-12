<?php

class PersonnelModel
{
    private mysqli $db;

    public function __construct(mysqli $conn)
    {
        $this->db = $conn;
    }

    // Add new personnel
    public function AddPersonnel(string $name): bool
    {
        $name = trim($name);
        $query = "INSERT INTO tbl_footprint_personnel (personnel_name, status) VALUES (?, '1')";

        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param("s", $name);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    // Get ALL personnel (Active and Inactive)
    public function GetAllPersonnel(): array
    {
        $records = [];
        $query = "SELECT personnel_id, personnel_name, status FROM tbl_footprint_personnel ORDER BY personnel_name ASC";

        if ($result = $this->db->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }
        }
        return $records;
    }

    // Check if personnel exists
    public function PersonnelExists(string $name): bool
    {
        $query = "SELECT COUNT(*) as total FROM tbl_footprint_personnel WHERE LOWER(TRIM(personnel_name)) = LOWER(TRIM(?))";
        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return ((int)($res['total'] ?? 0)) > 0;
        }
        return false;
    }

    // Toggle personnel status ('1' = Active, '0' = Inactive)
    public function UpdateStatus(int $id, string $status): bool
    {
        $query = "UPDATE tbl_footprint_personnel SET status = ? WHERE personnel_id = ?";
        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param("si", $status, $id);
            $res = $stmt->execute();
            $stmt->close();
            return $res;
        }
        return false;
    }
}
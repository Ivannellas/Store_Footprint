<?php

require_once __DIR__ . '/../entity/footprint_model.php';

class FootprintController
{
    private mysqli $dbConn;
    private FootprintModel $footprintModel;

    public function __construct(mysqli $conn)
    {
        $this->dbConn = $conn;
        $this->footprintModel = new FootprintModel($conn);
    }

    /*===========================================================
    // Add a new footprint entry                                //
    ===========================================================*/
    public function HandleAddFootprint(string $type, array $data): array
    {
        $tableName = ($type === 'parking') ? 'tbl_parking_footprint' : 'tbl_store_footprint';

        $personnel = trim($data['opersonnel'] ?? '');
        $date      = trim($data['odate'] ?? date('Y-m-d'));
        $timeRange = trim($data['otimerange'] ?? '');
        $count     = (int)($data['ocount'] ?? 0);
        $addedBy   = trim($data['added_by'] ?? '');

        if (empty($personnel) || empty($timeRange) || $count <= 0) {
            return [
                'success' => false,
                'message' => 'Please fill in all required fields.'
            ];
        }

        // Duplicate log check for exact personnel, date, and time range
        if ($this->footprintModel->HasExistingLog($tableName, $personnel, $date, $timeRange)) {
            return [
                'success' => false,
                'message' => "'" . htmlspecialchars($personnel) . "' has already logged for time range: " . htmlspecialchars($timeRange) . "."
            ];
        }

        $payload = [
            'added_by'   => $addedBy,
            'opersonnel' => $personnel,
            'odate'      => $date,
            'otimerange' => $timeRange,
            'ocount'     => $count
        ];

        $isSaved = $this->footprintModel->AddFootprint($tableName, $payload);

        if ($isSaved) {
            return ['success' => true, 'message' => 'Foot Traffic added successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to save record due to a database error.'];
    }

    /*===========================================================
    // Load store footprints                                   //
    ===========================================================*/
    public function RenderStoreFootprints(?string $date = null): array
    {
        return $this->footprintModel->GetFootprints('tbl_store_footprint', $date);
    }

    /*===========================================================
    // Load parking footprints                                 //
    ===========================================================*/
    public function RenderParkingFootprints(?string $date = null): array
    {
        return $this->footprintModel->GetFootprints('tbl_parking_footprint', $date);
    }
}
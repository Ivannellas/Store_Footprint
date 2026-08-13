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

        $personnel  = trim($data['opersonnel'] ?? '');
        $date       = trim($data['odate'] ?? date('Y-m-d'));
        $timeRange  = trim($data['otimerange'] ?? '');
        $count      = (int)($data['ocount'] ?? 0);
        $addedBy    = trim($data['added_by'] ?? '');
        $voidStatus = (int)($data['void_status'] ?? 1);

        if (empty($personnel) || empty($timeRange) || $count < 0) {
            return [
                'success' => false,
                'message' => 'Please fill in all required fields.'
            ];
        }

        // Duplicate log check for exact personnel, date, and time range
        if ($this->footprintModel->HasExistingLog($tableName, $personnel, $date, $timeRange)) {
            return [
                'success' => false,
                'message' => "'" . htmlspecialchars($personnel) . "' has already logged for: " . htmlspecialchars($timeRange) . "."
            ];
        }

        $payload = [
            'added_by'    => $addedBy,
            'opersonnel'  => $personnel,
            'odate'       => $date,
            'otimerange'  => $timeRange,
            'ocount'      => $count,
            'void_status' => $voidStatus
        ];

        $isSaved = $this->footprintModel->AddFootprint($tableName, $payload);

        $label = ($type === 'parking') ? 'Vehicle Traffic' : 'Foot Traffic';
        if ($isSaved) {
            return ['success' => true, 'message' => "{$label} added successfully."];
        }

        return ['success' => false, 'message' => 'Failed to save record due to a database error.'];
    }

    /*===========================================================
    // Void an existing footprint entry                         //
    ===========================================================*/
    public function HandleVoidFootprint(string $type, int $tableId): array
    {
        if ($tableId <= 0) {
            return ['success' => false, 'message' => 'Invalid record ID for void operation.'];
        }

        $tableName = ($type === 'parking') ? 'tbl_parking_footprint' : 'tbl_store_footprint';
        $isVoided  = $this->footprintModel->VoidFootprint($tableName, $tableId);

        if ($isVoided) {
            return ['success' => true, 'message' => 'Record voided successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to void the record due to a database error.'];
    }

    /*===========================================================
    // Load store footprints (Single Date or Date Range Filter) //
    ===========================================================*/
    public function RenderStoreFootprints(?string $startDate = null, ?string $endDate = null): array
    {
        return $this->footprintModel->GetFootprints('tbl_store_footprint', $startDate, $endDate);
    }

    /*===========================================================
    // Load parking footprints (Single Date or Date Range Filter)//
    ===========================================================*/
    public function RenderParkingFootprints(?string $startDate = null, ?string $endDate = null): array
    {
        return $this->footprintModel->GetFootprints('tbl_parking_footprint', $startDate, $endDate);
    }
}
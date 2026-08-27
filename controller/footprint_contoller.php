<?php

require_once __DIR__ . '/../entity/footprint_model.php';

class FootprintController
{
    private const FOOTPRINT_MODULE_ID = '14';

    private mysqli $dbConn;
    private FootprintModel $footprintModel;

    public function __construct(mysqli $conn)
    {
        $this->dbConn = $conn;
        $this->footprintModel = new FootprintModel($conn);
    }

    /*===========================================================
    // Add a new footprint entry                                 //
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
        $voidedBy   = trim($data['voided_by'] ?? '');


        // Prevent submission if personnel is empty or placeholder is selected
        if (empty($personnel) || $personnel === 'SELECT PERSONNEL' || empty($timeRange) || $count < 0) {
            return [
                'success' => false,
                'message' => 'Please select a personnel and fill in all required fields.'
            ];
        }

        $payload = [
            'added_by'     => $addedBy,
            'opersonnel'   => $personnel,
            'odate'        => $date,
            'otimerange'   => $timeRange,
            'ocount'       => $count,
            'void_status'  => $voidStatus,
            'voided_by'    => $voidedBy,
            'vehicle_type' => $data['vehicle_type'] ?? ''
        ];

        if ($type === 'parking') {
            $rawVehicleType = strtoupper(trim($data['vehicle_type'] ?? ''));
            $allowedTypes   = ['2WHEELS', '3WHEELS', '4WHEELS', '6WHEELS'];

            if (!in_array($rawVehicleType, $allowedTypes, true)) {
                return [
                    'success' => false,
                    'message' => 'Please specify a valid vehicle category.'
                ];
            }

            $payload['vehicle_type'] = $rawVehicleType;

            if ($this->footprintModel->HasExistingLog($tableName, $personnel, $date, $timeRange, $rawVehicleType)) {
                return [
                    'success' => false,
                    'message' => "'" . htmlspecialchars($personnel) . "' has already logged entries for " . $rawVehicleType . " during " . htmlspecialchars($timeRange) . "."
                ];
            }
        } else {
            if ($this->footprintModel->HasExistingLog($tableName, $personnel, $date, $timeRange)) {
                return [
                    'success' => false,
                    'message' => "'" . htmlspecialchars($personnel) . "' has already logged for: " . htmlspecialchars($timeRange) . "."
                ];
            }
        }

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
    public function HandleVoidFootprint(
        string $type,
        int $tableId,
        string $voidedBy = '',
        string $initiatorUserId = '',
        string $approverPostcode = '',
        string $voidReason = ''
    ): array {
        if ($tableId <= 0) {
            return ['success' => false, 'message' => 'Invalid record ID for void operation.'];
        }

        if (empty(trim($voidReason))) {
            return ['success' => false, 'message' => 'A valid reason is required to void this record.'];
        }

        $tableName = ($type === 'parking') ? 'tbl_parking_footprint' : 'tbl_store_footprint';

        // Verify entry creation date is today
        if (!$this->IsCreatedToday($tableName, $tableId)) {
            return [
                'success' => false,
                'message' => 'Records can only be voided on the same date they were created.'
            ];
        }

        // Validate Supervisor or Manager approval code
        if (!$this->HasAuthorizedVoidApprover($initiatorUserId, $approverPostcode)) {
            return [
                'success' => false,
                'message' => 'You need another admin postcode.'
            ];
        }

        $isVoided = $this->footprintModel->VoidFootprint($tableName, $tableId, $voidedBy, $voidReason);

        if ($isVoided) {
            return ['success' => true, 'message' => 'Record voided successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to void the record due to a database error.'];
    }

    /**
     * Verifies that a record was created on the current calendar date.
     */
    private function IsCreatedToday(string $tableName, int $tableId): bool
    {
        // Adjust created_at to created_date or odate if necessary based on your database column
        $query = "SELECT 1 FROM {$tableName} WHERE otableid = ? AND DATE(created_at) = CURDATE() LIMIT 1";

        if ($stmt = $this->dbConn->prepare($query)) {
            $stmt->bind_param('i', $tableId);
            $stmt->execute();
            $isToday = $stmt->get_result()->num_rows === 1;
            $stmt->close();
            return $isToday;
        }

        return false;
    }

    /**
     * A void must be approved by another active user with a matching postcode
     * and Supervisor or Manager permission.
     */
    private function HasAuthorizedVoidApprover(string $initiatorUserId, string $approverPostcode): bool
    {
        $initiatorUserId = trim($initiatorUserId);
        $approverPostcode = trim($approverPostcode);

        if ($initiatorUserId === '' || $approverPostcode === '') {
            return false;
        }

        $query = 'SELECT 1
                  FROM tbl_user u
                  INNER JOIN tbl_access a ON a.oUserid = u.oUserid
                  WHERE u.oUserid <> ?
                    AND u.oPostcode = ?
                    AND u.oActive = 1
                    AND a.oModuleid = ?
                    AND (a.oSupervisor = 1 OR a.oManager = 1)
                  LIMIT 1';

        if (!$stmt = $this->dbConn->prepare($query)) {
            error_log('Failed to prepare void approver validation: ' . $this->dbConn->error);
            return false;
        }

        $moduleId = self::FOOTPRINT_MODULE_ID;
        $stmt->bind_param('sss', $initiatorUserId, $approverPostcode, $moduleId);
        $stmt->execute();
        $isAuthorized = $stmt->get_result()->num_rows === 1;
        $stmt->close();

        return $isAuthorized;
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

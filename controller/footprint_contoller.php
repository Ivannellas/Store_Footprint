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

        // Check if this specific personnel already logged for this time range today
        if ($this->footprintModel->HasExistingLog(
            $tableName,
            $data['opersonnel'],
            $data['odate'],
            $data['ostarttime'],
            $data['oendtime']
        )) {
            return [
                'success' => false,
                'message' => "'" . htmlspecialchars($data['opersonnel']) . "' has already logged for this time range (" . $data['ostarttime'] . " - " . $data['oendtime'] . ")."
            ];
        }

        // Assign data to model
        $this->footprintModel->oPersonnel = $data['opersonnel'];
        $this->footprintModel->oDate      = $data['odate'];
        $this->footprintModel->oStartTime = $data['ostarttime'];
        $this->footprintModel->oEndTime   = $data['oendtime'];
        $this->footprintModel->oCount     = $data['ocount'];

        $isSaved = $this->footprintModel->AddFootprint($tableName);

        if ($isSaved) {
            return ['success' => true, 'message' => 'Footprint added successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to save footprint record due to a database error.'];
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

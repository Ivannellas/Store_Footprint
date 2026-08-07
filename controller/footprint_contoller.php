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
    public function HandleAddFootprint(string $type, array $formData): bool
    {
        $tableName = ($type === 'parking') ? 'tbl_parking_footprint' : 'tbl_store_footprint';
        
        $this->footprintModel->oPersonnel = $formData['opersonnel'] ?? '';
        $this->footprintModel->oDate      = $formData['odate'] ?? '';
        $this->footprintModel->oStartTime      = $formData['ostarttime'] ?? '';
        $this->footprintModel->oEndTime      = $formData['oendtime'] ?? '';
        $this->footprintModel->oCount     = isset($formData['ocount']) ? (int)$formData['ocount'] : 0;

        if (empty($this->footprintModel->oPersonnel) || empty($this->footprintModel->oDate)) {
            return false;
        }

        return $this->footprintModel->AddFootprint($tableName);
    }

/*===========================================================
// Load store footprints                                   //
===========================================================*/
    public function RenderStoreFootprints(): array
    {
        return $this->footprintModel->GetFootprints('tbl_store_footprint');
    }

/*===========================================================
// Load parking footprints                                 //
===========================================================*/
    public function RenderParkingFootprints(): array
    {
        return $this->footprintModel->GetFootprints('tbl_parking_footprint');
    }
}
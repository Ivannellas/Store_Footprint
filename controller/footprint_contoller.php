<?php

require_once '../entity/footprint_model.php';

class FootprintController
{
    private FootprintModel $model;

    public function __construct(mysqli $conn)
    {
        $this->model = new FootprintModel($conn);
    }

 
    public function renderStoreFootprints(): array
    {
        return $this->model->getFootprints('tbl_store_footprint');
    }

   
    public function renderParkingFootprints(): array
    {
        return $this->model->getFootprints('tbl_parking_footprint');
    }

    
    public function handleAddFootprint(string $type, string $personnel, string $date, string $time, int $count): bool
    {
        $tableName = ($type === 'parking') ? 'tbl_parking_footprint' : 'tbl_store_footprint';
        return $this->model->addFootprint($tableName, $personnel, $date, $time, $count);
    }

   
}
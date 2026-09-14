<?php

require_once __DIR__ . '/../entity/personnel_model.php';

class PersonnelController
{
    private PersonnelModel $model;

    public function __construct(mysqli $conn)
    {
        $this->model = new PersonnelModel($conn);
    }

    public function HandleAddPersonnel(array $data): array
    {
        $name = trim($data['personnel_name'] ?? '');

        if (empty($name)) {
            return ['success' => false, 'message' => 'Area cannot be empty.'];
        }

        if ($this->model->PersonnelExists($name)) {
            return ['success' => false, 'message' => 'Area already exists!'];
        }

        if ($this->model->AddPersonnel($name)) {
            return ['success' => true, 'message' => 'Area added successfully!'];
        }

        return ['success' => false, 'message' => 'Failed to add area.'];
    }

    public function HandleToggleStatus(int $id, string $currentStatus): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid area ID.'];
        }

        $isActive = ($currentStatus === '1' || $currentStatus === 'Active' || $currentStatus === 1);
        $newStatus = $isActive ? '0' : '1';
        $actionText = $isActive ? 'deactivated' : 'activated';

        if ($this->model->UpdateStatus($id, $newStatus)) {
            return ['success' => true, 'message' => "Area {$actionText} successfully!"];
        }

        return ['success' => false, 'message' => "Failed to update area status."];
    }

    public function GetAllPersonnel(): array
    {
        return $this->model->GetAllPersonnel();
    }
}
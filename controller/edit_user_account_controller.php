<?php
require_once __DIR__ . '/../entity/edit_user_account_model.php';

class EditUserAccountController
{
    private mysqli $dbConn;
    private EditUserModel $userModel;

    public function __construct(mysqli $conn)
    {
        $this->dbConn = $conn;
        $this->userModel = new EditUserModel($conn);
    }

/*=============================================================
// load user data for editing                                //
=========================================================== */
    public function LoadUserData(string $userId)
    {
        return $this->userModel->getUserById($userId);
    }

/*============================================================
// update user profile                                      //
=========================================================== */
    public function UpdateProfile(array $formData, string $userId): bool
    {
        if (empty($formData['fullname']) || empty($formData['username'])) {
            return false;
        }
        return $this->userModel->updateProfile($formData, $userId);
    }

/*============================================================
// check if postcode is available                            //
=========================================================== */
    public function isPostcodeAvailable(int $code, string $currentUserId): bool
    {
        return $this->userModel->isPostcodeAvailable($code, $currentUserId);
    }
}
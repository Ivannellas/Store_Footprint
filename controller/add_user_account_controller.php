<?php

require '../entity/add_user_account_model.php';

class AddUserAccountController
{
    private mysqli $dbConn;

    public function __construct(mysqli $conn)
    {
        $this->dbConn = $conn;
    }

/*===========================================================
// add a new user account                                  //
===========================================================*/
public function AddUserAccount(array $formData): bool
    {
        $userModel = new AddUserAccount($this->dbConn);

        $userModel->oUserid     = $formData['userid'] ?? '';
        $userModel->oFullname   = $formData['fullname'] ?? '';
        $userModel->oUsername   = $formData['username'] ?? '';
        $userModel->oPassword   = $formData['password'] ?? '';
        $userModel->oPosition   = $formData['position'] ?? '';
        $userModel->oPostcode   = isset($formData['postcode']) && $formData['postcode'] !== '' ? (int)$formData['postcode'] : 0;
        $userModel->oActive     = isset($formData['active']) ? (int)$formData['active'] : 0;

        if (empty($userModel->oUserid) || empty($userModel->oUsername)) {
            return false;
        }

        return $userModel->AddUserAccount();
    }

/*===========================================================
// get existing postcodes                                  //
===========================================================*/
public function ExistingPostcodes(): array
    {
        $postcodes = [];
        $query = "SELECT oPostcode 
                FROM tbl_user 
                WHERE oPostcode IS NOT NULL 
                AND oPostcode != 0";

        if ($result = $this->dbConn->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $postcodes[] = (int)$row['oPostcode'];
            }
            $result->close();
        }

        return $postcodes;
    }

/*===========================================================
// check if a postcode is available                        //
===========================================================*/    

public function isPostcodeAvailable(int $code): bool
    {
        $query = "SELECT 1 
                FROM tbl_user 
                WHERE oPostcode = ? 
                LIMIT 1";
        $isTaken = false;

        if ($stmt = $this->dbConn->prepare($query)) {
            $stmt->bind_param("i", $code);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $isTaken = true;
            }
            $stmt->close();
        }

        return !$isTaken; 
    }
}

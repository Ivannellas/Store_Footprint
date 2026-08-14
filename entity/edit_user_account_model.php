<?php

class EditUserModel
{
    private mysqli $db;

    public function __construct(mysqli $conn)
    {
        $this->db = $conn;
    }

/*==============================================================
//   Retrieve user by ID                                      //
=============================================================*/   
    public function getUserById(string $userId)
    {
        $id = mysqli_real_escape_string($this->db, $userId);
        
        $query = "SELECT oUserid, 
                         oUsername, 
                         oFullname, 
                         oPosition, 
                         oPostcode, 
                         oActive 
                    FROM tbl_user 
                    WHERE oUserid = '$id' 
                    LIMIT 1";
        $result = mysqli_query($this->db, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return false;
    }

/*==============================================================
//   Update user profile                                      //
=============================================================*/       
    public function updateProfile(array $data, string $userId): bool
    {
        $id = mysqli_real_escape_string($this->db, $userId);
        $fullname = mysqli_real_escape_string($this->db, $data['fullname'] ?? '');
        $username = mysqli_real_escape_string($this->db, $data['username'] ?? '');
        $position = mysqli_real_escape_string($this->db, $data['position'] ?? '');
        $postcode = isset($data['postcode']) ? (int)$data['postcode'] : 0;
        $active = isset($data['active']) ? (int)$data['active'] : 0;

        if (!empty($data['password'])) {
            $password = mysqli_real_escape_string($this->db, $data['password']);
            $query = "UPDATE tbl_user 
                      SET oFullname = '$fullname', 
                          oUsername = '$username', 
                          oPosition = '$position', 
                          oPostcode = $postcode,
                          oActive = $active,
                          oPassword = '$password' 
                      WHERE oUserid = '$id'";
        } else {
            $query = "UPDATE tbl_user 
                      SET oFullname = '$fullname', 
                          oUsername = '$username', 
                          oPosition = '$position', 
                          oPostcode = $postcode,
                          oActive = $active
                      WHERE oUserid = '$id'";
        }

        return (bool)mysqli_query($this->db, $query);
    }

/*==============================================================
//   Check if postcode is available (excluding current user)  //
=============================================================*/
    public function isPostcodeAvailable(int $code, string $currentUserId): bool
    {
        if ($code <= 0) {
            return true;
        }

        $id = mysqli_real_escape_string($this->db, $currentUserId);
        $query = "SELECT 1 
                  FROM tbl_user 
                  WHERE oPostcode = $code 
                    AND oUserid != '$id' 
                  LIMIT 1";

        $result = mysqli_query($this->db, $query);

        return ($result && mysqli_num_rows($result) === 0);
    }
}
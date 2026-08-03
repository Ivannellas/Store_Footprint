<?php

class AddUserAccount
{
    private mysqli $db;

    public ?string $oUserid = null;
    public ?string $oFullname = null;
    public ?string $oUsername = null;
    public ?string $oPassword = null;
    public ?string $oPosition = null;
    public ?int $oPostcode = null;
    public ?int $oActive = null;

    public function __construct(mysqli $conn)
    {
        $this->db = $conn;
    }

/*==============================================================
//   Add a new user account                                   //
=============================================================*/   
    public function AddUserAccount(): bool
    {
        $query = "INSERT INTO tbl_user (
                    oUserid, 
                    ofullname, 
                    oUsername, 
                    oPassword, 
                    oPosition, 
                    oPostcode, 
                    oActive
                    
                  ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param(
                "sssssii",
                $this->oUserid,
                $this->oFullname,
                $this->oUsername,
                $this->oPassword,
                $this->oPosition,
                $this->oPostcode,
                $this->oActive
                
            );

            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } else {
            error_log("Failed to add user account: " . $this->db->error);
            return false;
        }
    }
}
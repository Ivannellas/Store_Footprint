<?php
require '../entity/user_model.php';

class UserController
{
    private mysqli $dbConn;

    public function __construct(mysqli $conn)
    {
        $this->dbConn = $conn;
    }

/*=============================================================
//   Checker if a user has permission for a specific module  //
=============================================================*/
    public function checkActionPermission(string $userId, string $moduleId, string $action): bool {
        $allowedAccess = [
            'Main'=> 'oMain', 
            'Add'=> 'oAdd', 
            'Edit'=> 'oEdit', 
            'View'=> 'oView', 
            'Save'=> 'oSave', 
            'Post'=> 'oPost', 
            'Cancel'=> 'oCancel', 
            'Print'=> 'oPrint', 
            'Discount'=> 'oDiscount', 
            'Send'=> 'oSend', 
            'Salesassistant'=> 'oSalesassistant', 
            'Supervisor'=> 'oSupervisor', 
            'Manager'=> 'oManager', 
            'Audit'=> 'oAudit'
        ];

        $cleanAction = ucfirst(str_replace('o', '', $action));

        if (!array_key_exists($cleanAction, $allowedAccess)) {
            return false;
        }

        $dbColumn = $allowedAccess[$cleanAction];

        $query = "SELECT a.{$dbColumn} 
                AS permission_flag 
                  FROM tbl_user u
                  INNER JOIN tbl_access a 
                  ON u.oUserid = a.oUserid
                  INNER JOIN tbl_module m 
                  ON a.oModuleid = m.oModuleid
                  WHERE u.oUserid = ? 
                    AND a.oModuleid = ? 
                    AND u.oActive = 1 
                  LIMIT 1";

        if ($stmt = $this->dbConn->prepare($query)) {
            $stmt->bind_param("ss", $userId, $moduleId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row && (int)$row['permission_flag'] === 1) {
                return true;
            }
        }

        return false;
    }

/*==========================================
//    make display of user accounts       //
=========================================*/ 
    public function renderUserManagement(): array
    {
        $userModel = new User($this->dbConn);
        return $userModel->getAllUsers();
    }

/*===========================================
//    retrieve search list                //
=========================================*/
    public function searchUserManagement(string $searchTerm): array
    {
        $userModel = new User($this->dbConn);
        return $userModel->SearchUsers($searchTerm);
    }

/*===========================================
    get all user ID for viewing           //
=========================================*/
    public function AlluserId(string $userId)
    {
        $id = mysqli_real_escape_string($this->dbConn, $userId);
        $query = "SELECT oUserid, 
                        oUsername, 
                        oFullname, 
                        oPosition, 
                        oPostcode, 
                        oActive 
                        FROM tbl_user 
                        WHERE oUserid = '$id'";
        $result = mysqli_query($this->dbConn, $query);
        return mysqli_fetch_assoc($result);
    }
}
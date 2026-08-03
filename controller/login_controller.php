<?php
require_once __DIR__ . '/../entity/user_model.php';

class LoginController {
    private mysqli $dbConn;

    public function __construct(mysqli $conn) {
        $this->dbConn = $conn;
    }


/*================================================================
//   Checker if a user has permission for a specific module     //
============================================================== */    
    private function establishSession(string $id, string $name, string $role) {
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_id']      = $id;
        $_SESSION['user_name']    = $name;
        $_SESSION['user_role']    = $role;
    }

/*==============================================================
//  process login credentials and establish session if valid  //
=============================================================*/
    public function processLogin(array $postData): string {
        $username = trim($postData['username'] ?? '');
        $password = trim($postData['password'] ?? '');

        $userModel = new User($this->dbConn);

        if (empty($username)) {
            $pref = $userModel->adminPreference();
            
            if ($pref && $password === $pref['oPasspharse']) {
                $this->establishSession('ADMIN_SYSTEM', $pref['oCompany'], 'ADMIN');
                header("Location: ../intro_page.php");
                exit;
            }
        } 
        // If not super admin, check user credentials
        else {
            $user = $userModel->authenticateUser($username, $password);
            
            if ($user) {
                $this->establishSession($user['oUserid'], $user['oFullname'], 'USER');
                header("Location: ../intro_page.php");
                exit;
            }
        }

        return "Invalid login credentials.";
    }

    
}
<?php

class User
{
    private mysqli $db;

    public function __construct(mysqli $conn)
    {
        $this->db = $conn;
    }

/*==============================================================
//   User Authentication                                      //
=============================================================*/   
public function authenticateUser(string $username, string $password)
    {
        $username = mysqli_real_escape_string($this->db, $username);
        $password = mysqli_real_escape_string($this->db, $password);

        $query = "SELECT * FROM tbl_user 
                WHERE oUsername = '$username' 
                AND oPassword = '$password' 
                AND oActive = 1 ";
        $result = mysqli_query($this->db, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return false;
    }

/*==============================================================
//   Super admin authentication                               //
=============================================================*/   
public function adminPreference()
    {
        $query = "SELECT oPasspharse, 
                         oCompany 
                FROM tbl_preferences ";
        $result = mysqli_query($this->db, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return false;
    }

/*==============================================================
//   get user to display                                      //
=============================================================*/   
public function getAllUsers(): array
    {
        $query = "SELECT oUserid, 
                    oUsername, 
                    oFullname, 
                    oActive 
            FROM tbl_user 
            ORDER BY oUserid ASC";
        $result = mysqli_query($this->db, $query);

        $users = [];
        if ($result) {
            value: while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
        }
        return $users;
    }

/*==============================================================
//   Search users                                             //
=============================================================*/   
public function SearchUsers(string $searchTerm): array
    {
        $users = [];
        $query = "SELECT oUserid, oUsername, oFullname, oActive 
                  FROM tbl_user 
                  WHERE oUserid LIKE ? 
                     OR oFullname LIKE ? 
                     OR oUsername LIKE ? 
                  ORDER BY oUserid ASC";

        if ($stmt = $this->db->prepare($query)) {
            $likeTerm = '%' . $searchTerm . '%';
            $stmt->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $stmt->close();
        }
        return $users;
    }
}
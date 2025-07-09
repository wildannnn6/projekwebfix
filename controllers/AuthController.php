<?php

include '../config/database.php';
include '../models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function login($username, $password) {
        if($this->user->login($username, $password)) {
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['username'] = $this->user->username;
            $_SESSION['role'] = $this->user->role;
            $_SESSION['full_name'] = $this->user->full_name;
            $_SESSION['logged_in'] = true;
            
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
        header("Location: ../views/login.php");
        exit();
    }

    public function checkAuth() {
        if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header("Location: ../views/login.php");
            exit();
        }
    }

    public function checkRole($allowed_roles) {
        $this->checkAuth();
        if(!in_array($_SESSION['role'], $allowed_roles)) {
            header("Location: ../views/dashboard.php");
            exit();
        }
    }
}

if(isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_start();
    session_destroy();
    header("Location: ../views/login.php");
    exit();
}
?>

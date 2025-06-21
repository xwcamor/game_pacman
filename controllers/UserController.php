<?php
session_start();
require_once '../config/database.php';
require_once '../models/User.php';

class UserController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function register($username, $password) {
        $this->user->username = $username;
        $this->user->password = $password;

        if($this->user->register()) {
            return true;
        }
        return false;
    }

    public function login($username, $password) {
        $this->user->username = $username;
        $this->user->password = $password;

        $user = $this->user->login();
        if($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
        header("Location: ../views/login.php");
        exit();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
?> 
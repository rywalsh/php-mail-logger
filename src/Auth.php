<?php
/**
 * Admin Authentication Class
 */
class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, email, password_hash FROM admins WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        if ($result && password_verify($password, $result['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $result['id'];
            $_SESSION['admin_email'] = $result['email'];

            // Update last login
            $updateStmt = $this->db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param('i', $result['id']);
            $updateStmt->execute();

            return true;
        }

        return false;
    }

    public function logout() {
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public function getAdminEmail() {
        return $_SESSION['admin_email'] ?? null;
    }

    public function changePassword($adminId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
        $stmt->bind_param('si', $hashedPassword, $adminId);
        return $stmt->execute();
    }
}

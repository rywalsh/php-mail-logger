<?php
class Messages {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($name, $email, $subject, $message, $ip, $pageUrl) {
        $stmt = $this->db->prepare(
            "INSERT INTO messages (name, email, subject, message, ip_address, page_url) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssss', $name, $email, $subject, $message, $ip, $pageUrl);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function markSent($id) {
        $stmt = $this->db->prepare("UPDATE messages SET mail_sent = 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function countRecentByIp($ip, $minutes = 10) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) c FROM messages WHERE ip_address = ? AND created_at > (NOW() - INTERVAL ? MINUTE)"
        );
        $stmt->bind_param('si', $ip, $minutes);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['c'];
    }

    public function count() {
        return (int) $this->db->query("SELECT COUNT(*) c FROM messages")->fetch_assoc()['c'];
    }

    public function page($page, $perPage) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT * FROM messages ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $perPage, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}

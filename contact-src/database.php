<?php
class Database {
    private static $instance;
    private $conn;

    private function __construct() {
        $c = Config::get('db');
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->conn = new mysqli($c['host'], $c['user'], $c['pass'], $c['name']);
        $this->conn->set_charset('utf8mb4');
    }

    public static function getInstance() {
        return self::$instance ??= new self();
    }

    public function getConnection() {
        return $this->conn;
    }
}

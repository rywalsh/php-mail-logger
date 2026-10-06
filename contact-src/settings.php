<?php
class Settings {
    const COLOR_DEFAULTS = [
        'color_primary' => '#2563eb',
        'color_button_text' => '#ffffff',
        'color_text' => '#222222',
        'color_background' => '#ffffff',
        'color_border' => '#bbbbbb',
    ];
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function get($key, $default = '') {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['setting_value'] : $default;
    }

    public function set($key, $value) {
        $stmt = $this->db->prepare(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        $stmt->bind_param('ss', $key, $value);
        return $stmt->execute();
    }

    public function colors() {
        $out = [];
        foreach (self::COLOR_DEFAULTS as $k => $default) {
            $v = $this->get($k, $default);
            $out[$k] = preg_match('/^#[0-9a-f]{6}$/i', $v) ? $v : $default;
        }
        return $out;
    }
}

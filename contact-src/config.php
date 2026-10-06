<?php
class Config {
    private static $data;

    public static function get($key, $default = null) {
        self::$data ??= require __DIR__ . '/settings.local.php';
        return self::$data[$key] ?? $default;
    }
}

<?php

namespace library;

//TODO: use .env files for storing configurations
$GLOBALS['config'] = [
    'mysql' => [
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'db' => 'vstream',
        'charset' => 'utf8mb4'
    ],
    'session' => [
        'session_name' => 'user',
        'csrf_token' => 'token',
        'user_name' => 'unknown'
    ],
    'timezone' => 'Europe/Amsterdam'
];

class config {
    private function __construct() {}

    public static function get(string $path, string $variable_name = 'config') {
        if (!$path) {
            return false;
        }

        $config = $GLOBALS[$variable_name];
        $path = explode('/', $path);

        foreach ($path as $bit) {
            if (!isset($config[$bit])) {
                LOG_WARNING('Configuration does not exist');
                return false;
            }
            $config = $config[$bit];
        }

        return $config;
    }
}
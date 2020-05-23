<?php

namespace library;

class session {
    public static function create($key, $value) : void {
        $_SESSION[$key] = $value;
    }

    public static function regenerate() {
        session_regenerate_id();
    }

    public static function delete($key) : void {
        if (self::exists($key)) {
            unset($_SESSION[$key]);
        }
    }

    public static function delete_all() : void {
        session_destroy();
    }

    public static function get($key) {
        return self::exists($key) ? $_SESSION[$key] : null;
    }

    public static function exists($key) : bool {
        if (!isset($_SESSION[$key])) {
            LOG_WARNING("Session does not exist: $key");
            return false;
        }
        return true;
    }
}
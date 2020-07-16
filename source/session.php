<?php

namespace library;

$temp_sessions = [];

function session_exists(string $key) : bool {
    return isset($_SESSION[$key]) ? true : false;
}

function session_get(string $key) {
    return session_exists($key) ? $_SESSION[$key] : null;
}

function session_set(string $key, $value) : void {
    $_SESSION[$key] = $value;
}

function session_remove(string $key) : void {
    if (session_exists($key)) {
        unset($_SESSION[$key]);
    }
}

function session_once(string $key, $value) : void {
    $temp_sessions[] = $key;
    if (!in_array($key, $temp_sessions)) {
        session_set($key, $value);
    }
}
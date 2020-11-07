<?php

namespace source;

function session_isset(string $key) : bool {
    return isset($_SESSION[$key]);
}

function session_get(string $key) {
    return session_isset($key) ? $_SESSION[$key] : null;
}

function session_set(string $key, $value) : void {
    $_SESSION[$key] = $value;
}

function session_remove(string $key) : void {
    if (session_isset($key)) {
        unset($_SESSION[$key]);
    }
}

if (!session_isset('SESSION_TEMP')) {
    session_set('SESSION_TEMP', []);
}

function session_once(string $key, $value) : void {
    if (!in_array($key, $_SESSION['SESSION_TEMP'])) {
        session_set($key, $value);
        $_SESSION['SESSION_TEMP'][] = $key;
    }
}

function session_clear_temp() : void {
    foreach (session_get('SESSION_TEMP') ?? [] as $key) {
        session_remove($key);
    }
    session_set('SESSION_TEMP', []);
}

function auth_check() : bool {
    return session_isset(env('SESSION_AUTH'));
}
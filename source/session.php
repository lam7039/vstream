<?php

namespace library;

function session_exists(string $key) : bool {
    return isset($_SESSION[$key]);
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

//TODO: fix session once and clear
function session_once(string $key, $value) : void {
    if (!session_exists(env('SESSION_TEMP'))) {
        session_set(env('SESSION_TEMP'), []);
    }
    if (!in_array($key, session_get(env('SESSION_TEMP')))) {
        session_get(env('SESSION_TEMP'))[] = $key;
        session_set($key, $value);
    }
}

function session_clear_temp() : void {
    foreach (session_get(env('SESSION_TEMP')) ?? [] as $temp_session) {
        session_remove($temp_session);
    }
    if (!session_get(env('SESSION_TEMP')) ?? []) {
        session_set(env('SESSION_TEMP'), []);
    }
}
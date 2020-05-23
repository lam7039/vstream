<?php

namespace library\session;

function session_exists($key) : bool {
    return isset($_SESSION[$key]) ? true : false;
}

function session_get($key) {
    return session_exists($key) ? $_SESSION[$key] : null;
}

function session_set($key, $value) : void {
    $_SESSION[$key] = $value;
}

function session_remove($key) : void {
    if (session_exists($key)) {
        unset($_SESSION[$key]);
    }
}
<?php

namespace library;

use function library\session\session_get;
use function library\session\session_remove;
use function library\session\session_set;

function token_generate() : string {
    $token = bin2hex(openssl_random_pseudo_bytes(64));
    session_set(CONFIG('session/csrf_token'), $token);
    return $token;
}

function token_check(string $token) : bool {
    if (session_get(CONFIG('session/csrf_token')) === $token) {
        session_remove(CONFIG('session/csrf_token'));
        return true;
    }
    return false;
}
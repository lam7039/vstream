<?php

namespace library;

function token_generate() : string {
    $token = bin2hex(openssl_random_pseudo_bytes(64));
    session_set(CONFIG('SESSION_CSRF'), $token);
    return $token;
}

function token_check(string $token) : bool {
    if (session_get(CONFIG('SESSION_CSRF')) === $token) {
        session_remove(CONFIG('SESSION_CSRF'));
        return true;
    }
    return false;
}
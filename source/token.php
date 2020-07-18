<?php

namespace library;

function token_generate() : string {
    $token = bin2hex(openssl_random_pseudo_bytes(64));
    session_set(env('SESSION_CSRF'), $token);
    return $token;
}

function token_check(string $token) : bool {
    if (session_get(env('SESSION_CSRF')) === $token) {
        session_remove(env('SESSION_CSRF'));
        return true;
    }
    return false;
}
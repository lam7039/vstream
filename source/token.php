<?php

namespace library;

class token {
    
    public static function generate() : string {
        $token = bin2hex(openssl_random_pseudo_bytes(64));
        session::create(CONFIG('session/csrf_token'), $token);
        return $token;
    }

    public static function check(string $token) : bool {
        if (session::get(CONFIG('session/csrf_token')) === $token) {
            session::delete(CONFIG('session/csrf_token'));
            return true;
        }
        return false;
    }
}
<?php

namespace source;

function csrf_create() : string {
    session_remove('token');
    if (!session_isset('token')) {
        session_set('token', bin2hex(random_bytes(32)));
    }
    return session_get('token');
}

function csrf_check() : bool {
    return isset($_POST['token']) && hash_equals(session_get('token'), $_POST['token']);
}

function auth_check() : bool {
    return session_isset(env('SESSION_AUTH'));
}
<?php

namespace source;

function csrf_create() : string {
    if (!session_isset('token')) {
        session_set('token', bin2hex(random_bytes(32)));
    }
    return session_get('token');
}

function csrf_check() : bool {
    if (isset($_POST['token']) && hash_equals(session_get('token'), $_POST['token'])) {
        session_remove('token');
        return true;
    }
    LOG_WARNING('CRSF Token mismatch');
    return false;
}
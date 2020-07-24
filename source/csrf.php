<?php

namespace source;

function crsf_token() : string {
    return bin2hex(random_bytes(32));
}

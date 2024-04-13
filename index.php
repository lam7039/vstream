<?php

use source\Framework;

$start = microtime(true);
set_include_path(__DIR__);

require('core/init.php');
require('routing.php');

// use function source\session_clear_temp;
// use function source\session_once;

// if (isset($response['error'])) {
//     session_once('error', $response['error']);
// }

(new Framework(
    $container,
    $request,
    $router
))->run();

// session_clear_temp();
echo microtime(true) - $start;

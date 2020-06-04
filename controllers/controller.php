<?php

namespace controllers;

interface controller {
    
}

class test_class implements controller {
    function test() : void {
        echo json_encode(['a', 'b', 'c']);
    }
}
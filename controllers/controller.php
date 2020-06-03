<?php

namespace controllers;

class controller {
    function test() : void {
        echo json_encode(['a', 'b', 'c']);
    }

    function get_browse() {
        global $route;
        echo $route->get('browse');
    }
    function get_register() {
        global $route;
        echo $route->get('register');
    }
    function get_login() {
        global $route;
        echo $route->get('login');
    }
    function get_account() {
        global $route;
        echo $route->get('account');
    }
}
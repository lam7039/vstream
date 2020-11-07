<?php

namespace controllers;

use source\request;

abstract class controller {
    protected request $request;

    public function __construct() {
        //TODO: is this request necessary?
        $this->request = new request;
    }
}
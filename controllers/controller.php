<?php

namespace controllers;

use source\request;

abstract class controller {
    protected ?request $request;

    public function __construct(request $request = null) {
        $this->request = $request;
    }
}
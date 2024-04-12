<?php

namespace controllers;

use source\page_controller;

class account extends page_controller {
    private $user = null;

    public function __construct(array $parameters = []) {
        $this->parameters['ip'] = $this->user ? long2ip($this->user->ip_address) : '';
        $this->parameters['testfor'] = [
            'first',
            'second',
            'third',
        ];
        parent::__construct($parameters);
    }
}

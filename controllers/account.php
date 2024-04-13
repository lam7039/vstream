<?php

namespace controllers;

use source\page_controller;
use source\Request;
use source\Template;

class account extends page_controller {
    private $user = null;

    public function __construct(Template $templating, Request $request) {
        $this->parameters['ip'] = $this->user ? long2ip($this->user->ip_address) : '';
        $this->parameters['testfor'] = [
            'first',
            'second',
            'third',
        ];
        parent::__construct($templating, $request);
    }
}

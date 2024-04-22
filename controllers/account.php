<?php

namespace controllers;

use source\page_controller;
use source\Request;
use source\Template;

class account extends page_controller {
    private $user = null;

    public function __construct(Template $templating, Request $request) {
        parent::__construct($templating, $request);
        $parameters = [
            'ip' => $this->user ? long2ip($this->user->ip_address) : '',
            'testfor' => [
                'first',
                'second',
                'third',
            ]
        ];
        $templating->bind_parameters($parameters);
    }
}

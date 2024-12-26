<?php

namespace controllers;

use models\user;
use source\page_controller;
use source\Request;
use source\Template;

use function source\session_get;
use function source\session_isset;

class account extends page_controller {
    private ?user $user = null; 

    public function __construct(Template $templating, Request $request) {
        parent::__construct($templating, $request);

        $this->user = new user()->find(['id' => session_get(env('SESSION_AUTH'))]);

        $parameters = [
            'username' => $this->user->username,
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

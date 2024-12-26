<?php

namespace controllers;

use models\user;
use source\page_controller;
use source\Request;
use source\Template;

use function source\session_get;
use function source\session_isset;

class account extends page_controller {
    private user $user; 

    public function __construct(Template $templating, Request $request) {
        parent::__construct($templating, $request);
        
        $this->user = new user()->find(['id' => session_get(env('SESSION_AUTH'))]);

        $templating->bind_parameters([
            'username' => $this->user->username,
            'ip' => $this->user ? long2ip($this->user->ip_address) : '',
            'testfor' => [
                'first',
                'second',
                'third',
            ]
        ]);
    }
}

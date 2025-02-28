<?php

namespace controllers;

use models\User;
use source\{PageController, Template, Request};

use function source\session_get;

class Account extends PageController {
    private User $user; 

    public function __construct(Template $templating, Request $request) {
        parent::__construct($templating, $request);

        if (!$this->request->auth_check()) {
            redirect('/browse');
        }
        
        $this->user = new User()->find(['id' => session_get(env('SESSION_AUTH'))]);

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

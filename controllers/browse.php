<?php

namespace controllers;

use models\user;
use source\page_controller;
use source\Request;
use source\Template;

use function source\session_get;

class browse extends page_controller {

    public function __construct(Template $templating, Request $request) {
        parent::__construct($templating, $request);

        $user = new user()->find(['id' => session_get(env('SESSION_AUTH'))]);
		
        $parameters = [
            'username' => $user->username ?? ''
        ];

        $templating->bind_parameters($parameters);
    }

	// public function index() : string {
		// return view('url', $params);
	// }
}

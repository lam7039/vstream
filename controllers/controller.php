<?php

namespace controllers;

use models\user;
use source\file_buffer;
use source\template;

use function source\auth_check;
use function source\session_get;

abstract class controller {
    protected $user = null;
    protected $parameters = [
        'page_favicon' => 'favicon-32x32.png',
        'page_style' => 'layout.css',
        'page_script' => 'script.js',
    ];
    protected $templating = null;

    public function __construct(string $url_page = '') {
        if (auth_check()) {
            $this->user = new user();
            $this->user = $this->user->find(['id' => session_get(env('SESSION_AUTH'))]);
        }

        $this->parameters['page_title'] = 'vstream' . ($url_page ? "| $url_page" : '');
        if ($this->user) {
            $this->parameters['username'] = $this->user->username;
        }

        $this->templating = new template($this->parameters);
    }
    
    public function index(string $response = '') {
        return $this->templating->render(new file_buffer($response));
    }
}
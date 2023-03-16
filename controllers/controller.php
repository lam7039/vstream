<?php

namespace controllers;

use source\page_buffer;
use source\template;

abstract class controller {
    protected $parameters = [
        'page_favicon' => 'favicon-32x32.png',
        'page_style' => 'layout.css',
        'page_script' => 'script.js',
    ];
    protected template $templating;

    public function __construct(string $url_page = '') {
        $this->parameters['page_title'] = 'vstream' . ($url_page ? " | $url_page" : '');
        $this->templating = new template($this->parameters);
    }
    
    public function index(string $response = '') : string {
        return $this->templating->render(new page_buffer($response));
    }
}

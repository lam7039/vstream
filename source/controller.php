<?php

namespace source;

abstract class controller {
    
}

abstract class page_controller extends controller {
    protected template $templating;
    protected $parameters = [
        'page_favicon' => 'favicon-32x32.png',
        'page_style' => 'layout.css',
        'page_script' => 'script.js',
    ];

    public function __construct (array $parameters = []) {
        array_push($this->parameters, ...$parameters);
        if (isset($parameters['url_page'])) {
            $this->parameters['page_title'] = env('PROJECT_NAME') . ($parameters['url_page'] ? " | " . $parameters['url_page'] : '');
        }
        $this->templating = new template($this->parameters);
    }
    
    public function index(string $response = '') : string {
        return $this->templating->render(new page_buffer($response));
    }
}

<?php

namespace source;

abstract class controller {
    
}

abstract class page_controller extends controller {
    protected array $parameters = [
        'page_favicon' => 'favicon-32x32.png',
        'page_style' => 'layout.css',
        'page_script' => 'script.js',
    ];

    //TODO: fix container loading so subclasses don't have to load the parent classes
    public function __construct (protected Template $templating, protected Request $request, array $parameters = []) {
        $this->parameters = array_merge($this->parameters, $parameters, [
            'page_title' => env('PROJECT_NAME') . " | " . ltrim($this->request->uri(), '/')
        ]);
        $this->templating = new template($this->parameters);
    }
    
    public function index() : string {
        return $this->templating->render(new page_buffer('./public/html' . $this->request->uri() . '.html'));
    }
}

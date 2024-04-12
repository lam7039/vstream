<?php

namespace source;

class Framework {
    private Template $template;

    public function __construct(
        public container $container,
        public request $request,
        public router $router,
        array $config = []
    ) {
        //TODO: this template object is temporary until I figure out the dependency injection container for initializing controllers, the actual initialization would be in page_controller
        $this->template = new Template([
            'page_title' => env('PROJECT_NAME') . ' | ' . $this->request->uri(),
            'page_favicon' => 'favicon-32x32.png',
            'page_style' => 'layout.css',
            'page_script' => 'script.js',
        ]);
    }

    public function run() {
        $response = $this->router->resolve($this->request->method(), $this->request->uri());
        if (is_string($response)) {
            $response = $this->template->render(new page_buffer($response));
        }
        echo $response;
    }
}

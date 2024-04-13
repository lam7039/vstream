<?php

namespace source;

class Framework {
    public function __construct(
        public container $container,
        public request $request,
        public router $router
    ) {}

    public function run() {
        $response = $this->router->resolve($this->container);
        if ($response instanceof page_controller) {
            $response = $response->index();
        }
        echo $response;
    }
}

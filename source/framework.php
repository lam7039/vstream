<?php

namespace source;

class Framework {
    public function __construct(
        private Container $container,
        private Request $request,
        private router $router
    ) {}

    public function run() {
        echo $this->router->resolve($this->container);
    }
}

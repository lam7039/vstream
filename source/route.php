<?php

namespace library;

class route {
    private array $routes = [];

    //TODO: Finish routing
    public function to (string $path, Callable $to, array $params = []) {
        if (is_callable($to)) {
            $to = $to();
        }
        $this->routes[$path] = $to;
    }

    public function get(string $path) : void {
        if (!isset($this->routes[$path])) {
            LOG_WARNING("Route '$path' does not exist");
            http_response_code(404);
            return;
        }
        if ($this->routes[$path] instanceof template) {
            echo $this->routes[$path]->render('cached_file_key');
            return;
        }
        if (!file_exists($this->routes[$path])) {

            return;
        }
        if (http_response_code() != 200) {

        }
    }
}
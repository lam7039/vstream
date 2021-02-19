<?php

namespace source;

class file_buffer {
    public string $body;
    public int $size;

    public function __construct(public string $path) {
        $this->body = file_get_contents($path);
        $this->size = strlen($this->body);
    }
}

class token_node {
    public function __construct(
        public string $type, 
        public string $expression, 
        public array $children = [], 
        public ?string $value = null
    ) {}
}

//TODO: use htmlspecialchars on variables so it doesn't interfere with tokenization
class template {
    private file_buffer $layout;

    public function __construct(array $parameters = [], string $template_path = 'public/templates/layout.html') {
        $this->layout = new file_buffer($template_path);

        if ($parameters) {
            $this->layout = $this->bind_parameters($this->layout, $parameters);
        }
        // $this->parse_syntax($this->layout);
    }

    public function bind_parameter(file_buffer $buffer, string $key, string $value) : file_buffer {
        if (!str_contains($buffer->body, "[\$$key]")) {
            LOG_INFO("Parameter [\$$key] does not exist");
            return $buffer;
        }

        // $buffer->body = str_replace("[\$$key]", $value, $buffer->body);
        $buffer->body = strtr($buffer->body, "[\$$key]", $value);
        return $buffer;
    }

    public function bind_parameters(file_buffer $buffer, array $parameters) : file_buffer {
        foreach ($parameters as $key => $value) {
            $buffer = $this->bind_parameter($buffer, $key, $value);
            // if (!str_contains($buffer->body, "[\$$key]")) {
            //     LOG_INFO("Parameter [\$$key] does not exist");
            // }
            // unset($parameters[$key]);
            // $parameters["[\$$key]"] = $value;
        }
        // strtr($buffer->body, $parameters);
        return $buffer;
    }

    public function render(file_buffer $buffer, bool $cache = false) : string {
        if ($cache) {
            $file = 'tmp/cache/' . md5($buffer->path);
            if (file_exists($file) && (filemtime($file) + 3600) > time()) {
                return file_get_contents($file);
            }
        }

        // $body = str_replace('[:yield]', $buffer->body, $this->layout->body);
        $body = strtr($this->layout->body, '[:yield]', $buffer->body);
        if ($cache) {
            file_put_contents($file, $body);
        }
        
        return $body;
    }

    private function parse_syntax(file_buffer $buffer) {
        preg_match_all('/\[\:(.*)\]/', $buffer->body, $matches);
        array_shift($matches);
        foreach ($matches as $match) {
            [$syntax, $position] = $match;
            
            [$type, $check] = explode('|', $syntax);
            switch ($type) {
                case 'if':
                    $allowed_functions = ['isset'];
                    if (!in_array($check, $allowed_functions)) {
                        break;
                    }
                    break;
                case 'else':

                    break;
                case 'endif':

                    break;
            }
            
            // $buffer->body = str_replace("[$syntax]", '', $buffer->body);
            $buffer->body = strtr($buffer->body, "[$syntax]", '');
        }
        //TODO: replace body with array in strtr
        // $buffer->body = strtr($buffer->body, [
        //     'first' => 'replace one',
        //     'second' => 'replace two',
        // ]);
    }

    private function tokenize(file_buffer $buffer) : array {
        preg_match_all('/\[\:(.*)\]/', $buffer->body, $matches, PREG_OFFSET_CAPTURE);

        $lines = explode("\n", $body);
        foreach ($lines as $line) {
            if (($position = strpos($line, '<if')) === false) {
                continue;
            }
            $position_end = strpos($line, '</if>');
            $token_if = substr($line, $position, $position_end - $position);

            
        }
        return [];
    }

    private function create_tree(array $tokens) : token_node {
        return new token_node('root', '');
    }
}
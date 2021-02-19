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

    private function tokenize(file_buffer $buffer) {
        preg_match_all('/\[\:(.*)\]/', $buffer->body, $matches, PREG_OFFSET_CAPTURE);
        array_shift($matches);

        $tokens = [];
        foreach ($matches as $match) {
            [$expression, $position] = $match;

            $token = '';
            for ($i = $position; $i < strlen($expression); $i++) {
                $char = $buffer->body[$i];
                $token .= $char;
                // Creating token tree?
                // if(in_array($token, ['if', 'for', '$'])) {
                //     $tokens[] = new token_node($token, $expression);
                //     $token = '';
                // }
            }
            
            // $buffer->body = str_replace("[$syntax]", '', $buffer->body);
            $buffer->body = strtr($buffer->body, "[$expression]", '');
        }
        //TODO: replace body with array in strtr
        // $buffer->body = strtr($buffer->body, [
        //     'first' => 'replace one',
        //     'second' => 'replace two',
        // ]);
    }

    private function parse_syntax(array $tokens) : array {
        return [];
    }

    private function create_tree(array $tokens) : token_node {
        return new token_node('root', '');
    }
}
<?php

namespace source;

// class file_buffer {
//     public string $body;
//     public int $size;

//     public function __construct(public string $path) {
//         $this->body = file_get_contents($path);
//         $this->size = strlen($this->body);
//     }
// }

class file_buffer {
    public function __construct(
        public string $path, 
        public string $body = file_get_contents($path), 
        public string $size = ($body)
    ) {}
}

class token_node {
    public function __construct(
        public string $type, 
        public string $expression, 
        public array $children = [], 
        public ?string $value = null
    ) {}
}

class template {
    private file_buffer $layout;

    public function __construct(array $parameters = [], string $template_path = 'public/templates/layout.html') {
        $this->layout = new file_buffer($template_path);

        if ($parameters) {
            $this->layout = $this->bind_parameters($this->layout, $parameters);
        }
    }

    public function bind_parameter(file_buffer $buffer, string $key, string $value) : file_buffer {
        if (!str_contains($buffer->body, "{{{$key}}}")) {
            LOG_INFO("Parameter {{{$key}}} does not exist");
            return $buffer;
        }

        $buffer->body = str_replace("{{{$key}}}", $value, $buffer->body);
        
        // if (preg_match('/(?<=<if).*(?=>)/', $buffer->body, $matches, PREG_OFFSET_CAPTURE)) {
        //     dd($matches);
        // }

        return $buffer;
    }

    public function bind_parameters(file_buffer $buffer, array $parameters) : file_buffer {
        foreach ($parameters as $key => $value) {
            $buffer = $this->bind_parameter($buffer, $key, $value);
        }

        return $buffer;
    }

    public function render(file_buffer $buffer, bool $cache = false) : string {
        if ($cache) {
            $file = 'tmp/cache/' . md5($buffer->path);
            if (file_exists($file) && (filemtime($file) + 3600) > time()) {
                return file_get_contents($file);
            }
        }

        $body = str_replace('{{{yield}}}', $buffer->body, $this->layout->body);
        if ($cache) {
            file_put_contents($file, $body);
        }

        return $body;
    }
}
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

//Legacy
class token_node {
    public int $expression_size = 0;
    public int $body_size = 0;
    public int $end_size = 0;

    public function __construct(
        public string $type, 
        public string $expression, 
        public int $offset,
        public array $children = [], 
        public ?string $value = null
    ) {
        $this->expression_size = strlen($expression);
    }
}

class _token_node {
    public function __construct(
        public string $type, 
        public string $expression, 
        public array $children = [], 
        public ?string $value = null
    ) {}
}

//Legacy
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
        
        $buffer->body = str_replace('{{yield}}', $buffer->body, $this->layout->body);
        if ($cache) {
            file_put_contents($file, $buffer->body);
        }
        
        return $buffer->body;
    }
}

class _template {
    private file_buffer $layout;
    private array $parameters = [];
    private array $lexicon = [
        'if' => 'start_expression',
        'endif' => 'end_expression',
        'for' => 'start_expression',
        'endfor' => 'end_expression',
    ];

    public function __construct(array $parameters = [], string $template_path = 'public/templates/layout.html') {
        $this->layout = new file_buffer($template_path);
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function bind_parameters(array $parameters) : void {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function render(file_buffer $buffer, bool $cache = false) : string {
        if ($cache) {
            $file = 'tmp/cache/' . md5($buffer->path);
            if (file_exists($file) && (filemtime($file) + 3600) > time()) {
                return file_get_contents($file);
            }
        }

        //TODO: also detect variables with the parser
        $buffer->body = str_replace('{{yield}}', $buffer->body, $this->layout->body);
        foreach ($this->parameters as $key => $value) {
            $buffer->body = str_replace("{{{$key}}}", $value, $buffer->body);
        }

        //TODO: gives correct output but also gives error for some reason
        $tokens = @$this->tokenizer($buffer->body);
        $tree = $this->tree($tokens);
        $buffer->body = $this->parse_tree($tree);
        // dd($output);

        if ($cache) {
            file_put_contents($file, $buffer->body);
        }
        
        return $buffer->body;
    }

    private function tokenizer(string $template) : array {
        [$left, $right] = ['', htmlspecialchars($template)];
        $tokens = [];
        while (true) {
            [$left, $right] = explode('{% ', $right, 2);
            $tokens[] = $left;
            if (!$left || !$right) {
                break;
            }
            [$left, $right] = explode(' %}', $right, 2);
            $tokens[] = $left;
            if (!$left || !$right) {
                break;
            }
        }
        return $tokens;
    }

    private function tree(array $tokens) : _token_node {
        $root = new _token_node('root', '');
        $current = $root;
        $stack = [];
        foreach ($tokens as $i => $token) {
            if (!($i % 2)) {
                $root->children[] = new _token_node('html', $token);
                continue;
            }

            foreach ($this->lexicon as $type => $category) {
                if ($type === substr($token, 0, strlen($type))) {
                    switch ($category) {
                        case 'end_expression':
                            if ($stack) {
                                $current = array_pop($stack);
                            }
                            break;
                        case 'start_expression':
                            $node = new _token_node($type, $token);
                            $current->children[] = $node;
                            $stack[] = $current;
                            $current = $node;
                            break;
                    }
                }
            }
        }

        return $root;
    }

    private function parse_tree(_token_node $node) : string {
        $output = '';
        foreach ($node->children as $child) {
            switch ($child->type) {
                case 'html':
                    $output .= $child->expression;
                    break;
                case 'if':
                    $output .= $this->expression_if($child);
                    break;
            }
        }
        return $output;
    }

    private function expression_if(_token_node $node) : string {
        $expression = substr($node->expression, 3, -1);
        dd($expression);
        $output = '';

        if ($node->children) {
            $output .= $this->parse_tree($node);
        }
        return $output;
    }

    private function apply_functions(string $expression) {
        $function = explode('(', rtrim($expression, ')'), 2);
    }
}
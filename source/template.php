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
        public array $branches = [], 
    ) {}
}

class template {
    private file_buffer $layout;
    private array $parameters = [];
    private array $lexicon = [
        'if' => 'start_expression',
        'endif' => 'end_expression',
        'for' => 'start_expression',
        'endfor' => 'end_expression',
        'yield' => 'replace',
    ];
    private array $allowed_functions = [
        'isset',
        'auth_check',
    ];

    public function __construct(array $parameters = [], string $template_path = 'public/templates/layout.html') {
        $this->layout = new file_buffer($template_path);
        $this->bind_parameters($parameters);
    }

    public function bind_parameters(array $parameters) : void {
        if (array_intersect_key($this->lexicon, $parameters)) {
            LOG_CRITICAL('A key within the added parameters exists in the lexicon');
            return;
        }
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function render(file_buffer $buffer, bool $cache = false) : string {
        if ($cache) {
            $file = 'tmp/cache/' . md5($buffer->path);
            if (file_exists($file) && (filemtime($file) + 3600) > time()) {
                return file_get_contents($file);
            }
        }

        $tokens = @$this->tokenize($this->layout->body);
        $tree = $this->create_tree($tokens);
        $buffer->body = $this->interpret_tree($tree, $buffer);

        if ($cache) {
            file_put_contents($file, $buffer->body);
        }
        
        return $buffer->body;
    }

    private function tokenize(string $template) : array {
        [$left, $right] = ['', $template];
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

    private function create_tree(array $tokens) : token_node {
        $root = new token_node('root', '');
        $current = $root;
        $stack = [];
        foreach ($tokens as $i => $token) {
            if (!($i % 2)) {
                $current->branches[] = new token_node('html', $token);
                continue;
            }
            if (isset($this->parameters[$token])) {
                $current->branches[] = new token_node('var', $this->parameters[$token]);
                continue;
            }
            foreach ($this->lexicon as $type => $category) {
                if ($type !== substr($token, 0, strlen($type))) {
                    continue;
                }
                switch ($category) {
                    case 'end_expression':
                        if ($stack) {
                            $current = array_pop($stack);
                        }
                        continue 2;
                    case 'start_expression':
                        $node = new token_node($type, $token);
                        $current->branches[] = $node;
                        $stack[] = $current;
                        $current = $node;
                        continue 2;
                    case 'replace':
                        $root->branches[] = new token_node($type, $token);
                        continue 2;
                }
            }
        }
        return $root;
    }

    private function interpret_tree(token_node $node, file_buffer $buffer = null) : string {
        $output = '';
        foreach ($node->branches as $branch) {
            $output .= match ($branch->type) {
                'yield' => $this->expression_yield($buffer),
                'html' => $branch->expression,
                'var' => $branch->expression,
                'if' => $this->expression_if($branch),
                default => '',
            };
        }
        return $output;
    }

    private function expression_yield(file_buffer $buffer) : string {
        if (!$buffer) {
            return '';
        }
        $tokens = @$this->tokenize($buffer->body);
        $tree = $this->create_tree($tokens);
        return $this->interpret_tree($tree);
    }

    private function expression_if(token_node $node) : string {
        $expression = substr($node->expression, 3, -1);
        return $this->apply_function($expression) ? $this->interpret_tree($node) : '';
    }

    private function apply_function(string $expression) : mixed {
        [$function, $parameters] = explode('(', rtrim($expression, ')'), 2);
        $not = $function[0] === '!';
        $function = $not ? ltrim($function, '!') : $function;

        if (!in_array($function, $this->allowed_functions)) {
            return false;
        }
        
        $parameters = explode(',', $parameters);
        $result = (__NAMESPACE__ . '\\' . $function)(...$parameters);
        return $not ? $result : !$result;
    }
}
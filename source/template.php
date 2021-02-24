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
        public string $value = '',
        public int $depth = 0
    ) {}
}

class template {
    private file_buffer $layout;
    private array $parameters = [];
    private array $lexicon = [
        'if' => 'start',
        'endif' => 'end',
        'for' => 'start',
        'endfor' => 'end',
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
        $tree = $this->build_tree($tokens);
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

    private function build_tree(array $tokens) : token_node {
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
                    case 'start':
                        $node = new token_node($type, ltrim($token, "$type: "));
                        $current->branches[] = $node;
                        $stack[] = $current;
                        $current = $node;
                        continue 2;
                    case 'end':
                        if ($stack) {
                            $current = array_pop($stack);
                        }
                        continue 2;
                    case 'replace':
                        $current->branches[] = new token_node($type, $token);
                        continue 2;
                }
            }
        }
        return $root;
    }

    private function interpret_tree(token_node $node, file_buffer $buffer = null, int $depth = null) : string {
        $output = '';
        if ($depth !== null) {
            return $this->interpret_for($node, $depth);
        }
        foreach ($node->branches as $branch) {
            $output .= match ($branch->type) {
                'yield' => $this->interpret_yield($buffer),
                'html' => $branch->expression,
                'var' => $branch->expression,
                'if' => $this->interpret_if($branch),
                'for'=> $this->interpret_for($branch),
                default => '',
            };
        }
        return $output;
    }

    private function interpret_yield(file_buffer $buffer) : string {
        if (!$buffer) {
            return '';
        }
        $tokens = @$this->tokenize($buffer->body);
        $tree = $this->build_tree($tokens);
        return $this->interpret_tree($tree);
    }

    private function interpret_if(token_node $node) : string {
        $check = false;
        if (str_contains($node->expression, '==')) {
            [$first, $second] = explode('==', $node->expression);
            $first = $this->get($first) ?? str_replace('\'', '', trim($first));
            $first = $this->get($second) ?? str_replace('\'', '', trim($second));
            $check = $first === $second;
        } elseif (str_contains($node->expression, '!=')) {
            [$first, $second] = explode('!=', $node->expression);
            $first = $this->get($first) ?? str_replace('\'', '', trim($first));
            $first = $this->get($second) ?? str_replace('\'', '', trim($second));
            $check = $first !== $second;
        } else {
            $check = $this->apply_function($node->expression);
        }
        return $check ? $this->interpret_tree($node) : '';
    }

    private function interpret_for(token_node $node, int $depth = null) : string {
        [$variable, $array] = explode(' in ', $node->expression, 2);
        $array_count = count($array);
        if ($depth !== null && $depth > 0) {
            $depth--;
            $this->parameters[$variable] = $this->get($array)[$array_count - $depth];
            return $this->interpret_tree($node);
        }
        if ($depth === null) {
            $depth = $array_count;
        } elseif ($depth === 0) {
            $depth = null;
            unset($this->parameters[$variable]);
        }
        return $this->interpret_tree($node, depth: $depth);
    }

    private function apply_function(string $expression) : mixed {
        [$function, $parameters] = explode('(', rtrim($expression, ')'), 2);
        $not = $function[0] === '!';
        $function = $not ? ltrim($function, '!') : $function;

        if (!in_array($function, $this->allowed_functions)) {
            return false;
        }
        
        $parameters = explode(',', $parameters);
        // $function = (__NAMESPACE__ . '\\' . $function)(...$parameters);
        // return $not ? $function : !$function;
        return match ($function) {
            'isset' => $not ? !$this->get($parameters[0]) : $this->get($parameters[0]),
            default => $not ? (__NAMESPACE__ . '\\' . $function)(...$parameters) : !(__NAMESPACE__ . '\\' . $function)(...$parameters),
        };
    }

    private function get(string $key) : mixed {
        if (!isset($this->parameters[$key])) {
            LOG_WARNING("Variable: '$key' does not exist");
            return null;
        }
        return $this->parameters[$key];
    }
}
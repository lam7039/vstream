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
        public ?string $value = null
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
        // 'yield' => 'replace',
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

        //TODO: handle yield within interpreter
        $buffer->body = str_replace('{{yield}}', $buffer->body, $this->layout->body);

        //TODO: gives correct output but also gives error for some reason
        $tokens = @$this->tokenize($buffer->body);
        $tree = $this->create_tree($tokens, $buffer);
        $buffer->body = $this->interpret_tree($tree);

        if ($cache) {
            file_put_contents($file, $buffer->body);
        }
        
        return $buffer->body;
    }

    private function tokenize(string $template) : array {
        [$left, $right] = ['', $template];
        // [$left, $right] = ['', htmlspecialchars($template)];
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

    private function create_tree(array $tokens, file_buffer $buffer = null) : token_node {
        $root = new token_node('root', '');
        $current = $root;
        $stack = [];
        foreach ($tokens as $i => $token) {
            if (!($i % 2)) {
                $root->branches[] = new token_node('html', $token);
                continue;
            }
            if (isset($this->parameters[$token])) {
                $root->branches[] = new token_node('var', $this->parameters[$token]);
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
                        break;
                    case 'start_expression':
                        $node = new token_node($type, $token);
                        $current->branches[] = $node;
                        $stack[] = $current;
                        $current = $node;
                        break;
                    // case 'replace':
                    //     $buffer_tokens = @$this->tokenize($buffer->body);
                    //     $buffer_tree = $this->create_tree($buffer_tokens);
                    //     $root->branches[] = $buffer_tree->branches;
                    //     break;
                }
            }
        }
        // dd($root);
        return $root;
    }

    private function interpret_tree(token_node $node) : string {
        $output = '';
        foreach ($node->branches as $branch) {
            $output .= match ($branch->type) {
                'var' => $branch->expression,
                'html' => $branch->expression,
                'if' => $this->expression_if($branch),
                // 'yield' => $branch->expression,
            };
        }
        return $output;
    }

    private function expression_if(token_node $node) : string {
        $expression = substr($node->expression, 3, -1);
        $output = '';

        if ($node->branches) {
            $output .= $this->interpret_tree($node);
        }
        return $output;
    }

    private function apply_functions(string $expression) {
        $function = explode('(', rtrim($expression, ')'), 2);
    }
}
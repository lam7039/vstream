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
        public array $branches = []
    ) {}
}

class template {
    private file_buffer $layout;
    private array $parameters = [];

    public function __construct(array $parameters = [], string $template_path = 'public/templates/layout.html') {
        $this->layout = new file_buffer($template_path);
        $this->bind_parameters($parameters);
    }

    public function bind_parameters(array $parameters) : void {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function render(file_buffer $buffer, bool $cache = false) : string {
        //TODO: re-cache affected files when changes occur instead of detecting it on page load 
        if ($cache /* && $file !== $buffer->body */) {
            $file = 'tmp/cache/' . md5($buffer->path);
            if (file_exists($file) && (filemtime($file) + 3600) > time()) {
                return file_get_contents($file);
            }
        }
        $buffer->body = $this->interpret_html($this->layout, $buffer);
        if ($cache) {
            file_put_contents($file, $buffer->body);
        }
        return $buffer->body;
    }

    private function tokenize(string $template) : array {
        [$left, $right] = ['', $template];
        $tokens = [];
        while (true) {
            [$left, $right] = array_pad(explode('{% ', $right, 2), 2, '');
            $tokens[] = $left;
            if (!$left || !$right) {
                break;
            }
            [$left, $right] = array_pad(explode(' %}', $right, 2), 2, '');
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
            $type = match (true) {
                $token === 'yield' => 'yield',
                $token === 'endif' => 'endif',
                $token === 'endfor' => 'endfor',
                $token === 'else' => 'else',
                substr($token, 0, 3) === 'if:' => 'if',
                substr($token, 0, 4) === 'for:' => 'for',
                default => 'var',
            };
            if (in_array($type, ['endif', 'else', 'endfor']) && $stack) {
                $current = array_pop($stack);
            }
            if (in_array($type, ['if', 'else', 'for', 'var', 'yield'])) {
                $expression = match ($type) {
                    'yield' => $token,
                    'if' => substr($token, 3),
                    'for' => substr($token, 4),
                    'var' => $token,
                    default => '',
                };
                $node = new token_node($type, trim($expression));
                $current->branches[] = $node;
                if (!in_array($type, ['var', 'yield'])) {
                    $stack[] = $current;
                    $current = $node;
                }
            }
        }
        return $root;
    }

    private function interpret_tree(token_node $node, file_buffer $buffer = null) : string {
        $output = '';
        $if_expression = '';
        foreach ($node->branches as $branch) {
            $output .= match ($branch->type) {
                'yield' => $this->interpret_html($buffer),
                'html' => $branch->expression,
                'var' => $this->get($branch->expression) ?? '',
                'if' => $this->interpret_if($branch, $if_expression),
                'else' => $this->interpret_else($branch, $if_expression),
                'for'=> $this->interpret_for($branch),
                default => '',
            };
        }
        return $output;
    }

    private function interpret_html(file_buffer $layout, file_buffer $body = null) : string {
        $tokens = $this->tokenize($layout->body);
        $tree = $this->build_tree($tokens);
        return $this->interpret_tree($tree, $body);
    }

    private function interpret_if(token_node $node, string &$if_expression = '') : string {
        $if_expression = $node->expression;
        $type = match (true) {
            str_contains($node->expression, '==') => '==',
            str_contains($node->expression, '!=') => '!=',
            default => '',
        };
        if ($type) {
            [$first, $second] = explode($type, $node->expression, 2);
            //todo: no limit on explode, split everything up in 2, then loop through it
            $first = $this->get($first) ?? str_replace('\'', '', trim($first));
            $second = $this->get($second) ?? str_replace('\'', '', trim($second));
        }
        $check = match ($type) {
            '==' => $first === $second,
            '!=' => $first !== $second,
            default => $this->apply_function($node->expression) ?? '',
        };
        return $check ? $this->interpret_tree($node) : '';
    }

    private function interpret_else(token_node $node, string $if_expression) : string {
        if (!$if_expression) {
            return '';
        }
        $not = $if_expression[0] === '!';
        $node->expression = $not ? substr($if_expression, 1) : '!' . $if_expression;
        return $this->interpret_if($node);
    }

    private function interpret_for(token_node $node) : string {
        [$local_name, $array_name] = explode(' in ', $node->expression, 2);
        $output = '';
        foreach ($this->get($array_name) as $value) {
            $this->parameters[$local_name] = $value;
            $output .= $this->interpret_tree($node);
        }
        unset($this->parameters[$local_name]);
        return $output;
    }

    private function apply_function(string $expression) : mixed {
        [$name, $parameters] = explode('(', rtrim($expression, ')'), 2);
        $not = $name[0] === '!';
        $name = $not ? substr($name, 1) : $name;
        $parameters = explode(',', $parameters);
        // $function = (__NAMESPACE__ . '\\' . $function)(...$parameters);
        $function = match ($name) {
            'isset' => $this->get(...$parameters),
            'auth_check' => auth_check(),
        };
        return $not ? !$function : $function;
    }

    private function get(string $key) : mixed {
        if (!isset($this->parameters[$key])) {
            LOG_WARNING("Variable: '$key' does not exist");
            return null;
        }
        return $this->parameters[$key];
    }
}
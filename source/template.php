<?php

namespace source;

class page_buffer {
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

class Template {
    private page_buffer $layout;
    private array $parameters = [];

    public function __construct(array $parameters = [], string $template_path = './public/templates/layout.html') {
        $this->layout = new page_buffer($template_path);
        $this->bind_parameters($parameters);
    }

    public function bind_parameters(array $parameters) : void {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function render(page_buffer $buffer, bool $cache = false) : string {
        //TODO: re-cache affected files when changes occur instead of detecting it on page load || or generate static html files with a script
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

    private function interpret_tree(token_node $node, page_buffer $buffer = null) : string {
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

    private function interpret_html(page_buffer $layout, page_buffer $body = null) : string {
        $tokens = $this->tokenize($layout->body);
        $tree = $this->build_tree($tokens);
        return $this->interpret_tree($tree, $body);
    }

    private function interpret_if(token_node $node, string &$if_expression = '') : string {
        $if_expression = $node->expression;

        $comparitor = match (true) {
            str_contains($if_expression, '==') => '==',
            str_contains($if_expression, '!=') => '!=',
            str_contains($if_expression, '<') => '<',
            str_contains($if_expression, '>') => '>',
            str_contains($if_expression, '<=') => '<=',
            str_contains($if_expression, '>=') => '>=',
            default => '',
        };
        //TODO: str_contains vs preg_match, which would be the best/fastest?

        if ($comparitor) {
            [$first, $second] = explode($comparitor, $if_expression, 2);
            //TODO: no limit on explode, split everything up in 2, then loop through it
            $first = $this->get($first) ?? str_replace('\'', '', trim($first));
            $second = $this->get($second) ?? str_replace('\'', '', trim($second));
        }
        $comparison = match ($comparitor) {
            '==' => $first === $second,
            '!=' => $first !== $second,
            '<' => $first < $second,
            '>' => $first > $second,
            '<=' => $first <= $second,
            '>=' => $first >= $second,
            default => $this->apply_function($if_expression) ?? '',
        };
        
        //TODO: make conjunctors work and also take into account parentheses
        // $conjunctor = match (true) {
        //     str_contains($if_expression, '&&') => '&&',
        //     str_contains($if_expression, '||') => '||',
        //     default => ''
        // };
        // $statements = explode($conjunctor, $if_expression);
        // foreach ($statements as $statement) {
        //     $comparitor = match (true) {
        //         str_contains($if_expression, '==') => '==',
        //         str_contains($if_expression, '!=') => '!=',
        //         str_contains($if_expression, '<') => '<',
        //         str_contains($if_expression, '>') => '>',
        //         str_contains($if_expression, '<=') => '<=',
        //         str_contains($if_expression, '>=') => '>=',
        //         default => '',
        //     };
        //     if ($comparitor) {
        //         [$first, $second] = explode($comparitor, $statement, 2);
        //         $first = $this->get($first) ?? str_replace('\'', '', trim($first));
        //         $second = $this->get($second) ?? str_replace('\'', '', trim($second));
        //     }
        //     $comparison = match ($comparitor) {
        //         '==' => $first === $second,
        //         '!=' => $first !== $second,
        //         '<' => $first < $second,
        //         '>' => $first > $second,
        //         '<=' => $first <= $second,
        //         '>=' => $first >= $second,
        //         default => $this->apply_function($statement) ?? '',
        //     };
        // }
        return $comparison ? $this->interpret_tree($node) : '';
    }

    private function interpret_else(token_node $node, string $if_expression) : string {
        if (!$if_expression) {
            LOG_WARNING('No if statement detected');
            return '';
        }
        $node->expression = str_starts_with($if_expression, '!') ? substr($if_expression, 1) : '!' . $if_expression;
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
        $not = str_starts_with($name, '!');
        $name = $not ? substr($name, 1) : $name;
        $parameters = explode(',', $parameters);
        // $function = (__NAMESPACE__ . '\\' . $function)(...$parameters);
        $function = match ($name) {
            //TODO: I splat the parameters array where the get() function only supports a single parameter
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

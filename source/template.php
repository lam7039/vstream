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
    public int $size = 0;

    public function __construct(
        public string $type, 
        public string $expression, 
        public int $offset,
        public array $children = [], 
        public ?string $value = null
    ) {
        $this->size = strlen($expression);
    }
}

//TODO: use htmlspecialchars on variables so it doesn't interfere with tokenization
class template {
    private file_buffer $layout;
    private array $lexicon = [
        'yield' => 'replace',
        'if' => 'statement_start',
        'endif' => 'statement_end',
        'for' => 'statement_start',
        'endfor' => 'statement_end',
        'isset' => 'function',
        '"' => 'string',
        '\'' => 'string',
        '(' => 'parentheses',
        ')' => 'parentheses',
        '!' =>  'not',
    ];

    public function __construct(array $parameters = [], string $template_path = 'public/templates/layout.html') {
        $this->layout = new file_buffer($template_path);

        if ($parameters) {
            $this->layout = $this->bind_parameters($this->layout, $parameters);
        }
    }
    
    public function bind_parameter(file_buffer $buffer, string $key, string $value) : file_buffer {
        if (!str_contains($buffer->body, "[:$key]")) {
            LOG_INFO("Parameter [:$key] does not exist");
            return $buffer;
        }

        $buffer->body = str_replace("[:$key]", $value, $buffer->body);
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

        $tokens = $this->tokenize($this->layout);
        $tree = $this->create_tree($tokens);
        $parse = $this->parse_syntax($buffer, $tree);

        $body = str_replace('[:yield]', $buffer->body, $this->layout->body);
        if ($cache) {
            file_put_contents($file, $body);
        }
        
        return $body;
    }

    private function tokenize(file_buffer $buffer) : array {
        preg_match_all('/\[:(.*)\]/', $buffer->body, $matches, PREG_OFFSET_CAPTURE);
        [$full_matches, $partial_matches] = $matches;

        $tokens = [];
        foreach ($partial_matches as $partial_match) {
            [$expression, $offset] = $partial_match;
            $token = '';
            for ($i = 0; $i < strlen($expression); $i++) {
                $char = $buffer->body[$offset + $i];
                $token .= $char;
                if (isset($this->lexicon[$char]) || isset($this->lexicon[$token])) {
                    $tokens[] = isset($this->lexicon[$char]) ? [$this->lexicon[$char], $char, $offset] : [$this->lexicon[$token], $token, $offset];
                    $token = '';
                }
            }
        }

        return $tokens;
    }

    private function expression_if(string $data, token_node $token) : string {
        //substr data
        return $data;
    }

    private function parse_syntax(file_buffer $buffer, token_node $token) : file_buffer {
        foreach ($token->children as $child) {
            $buffer->body = match ($child->type) {
                'if' => $this->expression_if($buffer->body, $token),
                'for' => '',
                'yield' => str_replace('[:yield]', $this->layout->body, $buffer->body),
                default => $buffer->body,
            };
        }
        return $buffer;
    }

    private function create_tree(array $tokens) : token_node {
        $root = new token_node('root', '', 0);
        $current = $root;
        $stack = [];
        foreach ($tokens as $token) {
            [$type, $expression, $offset] = $token;
            
            switch ($type) {
                case 'statement_start':
                    $node = new token_node($type, $expression, $offset);
                    $current->children[] = $node;
                    //if not var
                    array_push($stack, $current);
                    $current = $node;
                    break;
                case 'statement_end':
                    if ($stack) {
                        $current = array_pop($stack);
                        break;
                    }
                    $stack[] = $current;
                    break;
            }
        }
        
        return $root;
    }
}
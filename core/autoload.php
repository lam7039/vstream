<?php

class autoload {
    private $class_maps = [
        'file_buffer' => 'cache',
        'file_cache' => 'cache'
    ];

    public function register($class) {
        $directory = get_include_path() . '\\source\\';
        $class = basename($class);
        $path_to_file = $directory . $class . '.php';
        
        if (in_array($path_to_file, get_required_files())) {
            return;
        }
        
        if (!file_exists($path_to_file)) {
            $path_to_file = $directory . $this->class_maps[$class] . '.php';
        }

        require $path_to_file;
    }
}

spl_autoload_extensions('.php');
spl_autoload_register([new autoload, 'register']);
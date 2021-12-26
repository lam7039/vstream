<?php

namespace source;

abstract class media_buffer {
    protected string $silence;

    public int $id = 0;
    public string $type;
    public string $options;

    public string $source_path;
    public string $source_filename;
    public string $source_extension;

    public string $output_path;
    public string $output_filename;
    public string $output_extension;
    public string $output_path_full;

    public function __construct(string $source_path, string $folder, bool $silent = false) {
        $this->silence = $silent ? ' > /dev/null 2> /dev/null &' : '';
        $this->source_path = "\"$source_path\"";
        $this->source_filename = pathinfo($source_path, PATHINFO_FILENAME);
        $this->source_extension = pathinfo($source_path, PATHINFO_EXTENSION);
        $this->output_path = "public/media/{$folder}";
        $this->output_filename = preg_replace('/ ?\[.*?\] ?/', '', $this->source_filename);
        $this->output_filename = str_replace('_', ' ', $this->output_filename);
        if (!$this->output_extension) {
            $this->output_extension = 'webm';
        }
        $this->output_path_full = "\"{$this->output_path}/{$this->output_filename}.{$this->output_extension}\"" . $this->silence;
    }
}

class video_buffer extends media_buffer {
    public int $duration;
    public string $duration_time;
    public string $audio_language;
    public string $subtitles_type;
    public string $subtitles_language;
    public string $output_subtitle_path_full;

    public function __construct(string $source_path, int $duration, bool $silent = false) {
        $this->type = 'video';
        $this->duration = $duration;
        $this->duration_time = date('H:i:s', $duration);
        $this->audio_language = 'jpn';
        $this->subtitles_type = 'hard';
        $this->subtitles_language = 'eng';
        parent::__construct($source_path, 'video', $silent);
        $this->output_subtitle_path_full = "\"{$this->output_path}/{$this->output_filename}-{$this->subtitles_language}.vtt\"" . $this->silence;
    }
}

class audio_buffer extends media_buffer {
    public string $visualize;

    public function __construct(string $source_path, bool $visualize = false, bool $silent = false) {
        $this->type = 'audio';
        $this->visualize = $visualize;
        parent::__construct($source_path, 'audio', $silent);
    }
}

class image_buffer extends media_buffer {
    public function __construct(string $source_path) {
        $this->type = 'image';
        $this->output_extension = 'webp';
        parent::__construct($source_path, 'images');
    }
}

class file_buffer {
    public string $body;
    public int $size;

    public function __construct(public string $path) {
        $this->body = file_get_contents($path);
        $this->size = strlen($this->body);
    }
}
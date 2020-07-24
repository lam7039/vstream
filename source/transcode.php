<?php

namespace source;

class media_buffer {
    public int $id = 0;
    public string $source_path;
    public string $source_filename;
    public string $source_extension;
    public string $output_path;

    public function __construct(string $source_path) {
        $this->source_path = $source_path;
        $this->source_filename = pathinfo($source_path, PATHINFO_FILENAME);
        $this->source_extension = pathinfo($source_path, PATHINFO_EXTENSION);
    }
}

class video_buffer extends media_buffer {
    public string $source_path;
    public int $duration;
    public string $duration_time;

    public function __construct(string $source_path, int $duration) {
        parent::__construct($source_path);
        $this->output_extension = 'mp4';
        $this->output_path = 'Videos/' . $this->source_filename . '.mp4';
        $this->duration = $duration;
        $this->duration_time = date('H:i:s', $duration);
    }
}

class music_buffer extends media_buffer {
    public string $source_path;

    public function __construct (string $source_path) {
        parent::__construct($source_path);
        $this->output_path = 'Music/' . $this->source_filename . '.mp3';
    }
}

class transcode {
    //TODO: make this work
    //TODO: figure out interrupting and continuing transcoding at selected time without redoing the transcode, also multistream encoding
    //TODO: maybe do fully encoding and display which have and which haven't been encoded in the interface
    //TODO: follow file system for path

    private database $database;

    public function __construct(database $database) {
        $this->database = $database;
    }
    
    public function ffmpeg(media_buffer $buffer) : void {
        $interrupted_time = $this->databse->fetch("select interrupted_time from transcode_list where video_id = '$buffer->id'")->interrupted_time;

        if ($interrupted_time) {
            shell_exec("ffmpeg -i {$buffer->source_path} -c:v libx264 -preset ultrafast -crf 0 {$buffer->output_path}");
            $current_time = '00:00';
            if ($current_time > $interrupted_time) {
                $this->database->execute("update transcode_list set current_time = '$current_time'");
            }
        }
    }
    
}
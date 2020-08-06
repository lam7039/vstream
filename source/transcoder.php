<?php

namespace source;

abstract class media_buffer {
    public int $id = 0;
    public string $source_path;
    public string $source_filename;
    public string $source_extension;
    public string $output_extension;
    public string $output_path;

    public function __construct(string $source_path, string $folder = '') {
        $this->source_path = $source_path;
        $this->source_filename = pathinfo($source_path, PATHINFO_FILENAME);
        $this->source_extension = pathinfo($source_path, PATHINFO_EXTENSION);
        $this->output_path = 'public/media/';
        $this->output_path .= $folder ? $folder . '/' : '';
        $this->output_path .= "{$this->source_filename}.{$this->output_extension}";
    }
}

class video_buffer extends media_buffer {
    public int $duration;
    public string $duration_time;

    public function __construct(string $source_path, int $duration) {
        $this->output_extension = 'mp4';
        $this->duration = $duration;
        $this->duration_time = date('H:i:s', $duration);
        parent::__construct($source_path, 'videos');
    }
}

class music_buffer extends media_buffer {
    public function __construct (string $source_path) {
        $this->output_extension = 'mp3';
        parent::__construct($source_path, 'music');
    }
}

class transcoder {
    //TODO: make this work
    //TODO: figure out interrupting and continuing transcoding at selected time without redoing the transcode, also multistream encoding
    //TODO: maybe do fully encoding and display which have and which haven't been encoded in the interface
    //TODO: follow file system for path
    //TODO: transcode 360 -> 480 -> 720
    //TODO: use adaptive bit rate format streaming
    //TODO: use queue/pipelines for transcoding

    private database $database;
    private string $codec = 'libx265';


    public function __construct(database $database) {
        $this->database = $database;
    }
    
    public function ffmpeg(media_buffer $buffer) : void {
        dd($buffer);
        // $interrupted_time = $this->databse->fetch("select interrupted_time from transcode_list where video_id = '$buffer->id'")->interrupted_time;

        // if ($interrupted_time) {
            //Preview encode
            // shell_exec("ffmpeg -i {$buffer->source_path} -f image2 -c:v mjpeg public/media/images{$buffer->id}.jpg");

            //Video transcoding
            shell_exec("ffmpeg -i {$buffer->source_path} -c:v {$this->codec} -preset ultrafast -crf 0 {$buffer->output_path}");
        //     shell_exec("ffmpeg -i {$buffer->source_path} -c:v libx264 -threads 6 -preset ultrafast -crf 0 {$buffer->output_path}");
        //     $current_time = '00:00';
        //     if ($current_time > $interrupted_time) {
        //         $this->database->execute("update transcode_list set current_time = '$current_time'");
        //     }
        // }
    }
    
}

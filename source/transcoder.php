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
    //TODO: figure out interrupting and continuing transcoding at selected time without redoing the transcode, also multistream encoding
    //TODO: maybe do fully encoding and display which have and which haven't been encoded in the interface
    //TODO: if on the fly transcoding won't work, transcode 360 -> 480 -> 720
    //TODO: use adaptive bit rate format streaming
    //TODO: use queue/pipelines for transcoding

    // -c:v stands for -codec:video
    // -crf stands for constant rate factor, it has a range of 0-51, 0 is lossless, 23 is default, 51 is worst, 18 is nearly visually lossless
    private array $options = [
        '-c:v libx264',
        '-threads 6',
        '-preset ultrafast',
        '-crf 16',
    ];

    // skip waiting, but also removes stdio and stderr
    private string $silence = ' > /dev/null 2>/dev/null &';
    
    public function ffmpeg(media_buffer $buffer, bool $silent = true) : void {

        // preview image encoding
        // shell_exec("ffmpeg -i {$buffer->source_path} -f image2 -c:v mjpeg public/media/images{$buffer->id}.jpg");
        
        $options = implode(' ', $this->options);
        $silence = $silent ? $this->silence : '';
        $command = "ffmpeg -i {$buffer->source_path} {$options} {$buffer->output_path} {$silence}";
        dd($command);
        shell_exec($command);
    }
}

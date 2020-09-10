<?php

namespace source;

abstract class option_type {
    const video = 'video';
    const audio = 'audio';
    const subtitles = 'subtitles';
}

abstract class media_buffer {
    public int $id = 0;
    public string $type;

    public string $source_path;
    public string $source_filename;
    public string $source_extension;

    public string $output_path;
    public string $output_filename;
    public string $output_extension;
    public string $output_path_full;

    public function __construct(string $source_path, string $folder) {
        $this->source_path = $source_path;
        $this->source_filename = pathinfo($source_path, PATHINFO_FILENAME);
        $this->source_extension = pathinfo($source_path, PATHINFO_EXTENSION);
        $this->output_path = "public/media/{$folder}";
        $this->output_filename = $this->source_filename; //TODO: create proper filename with regex
        $this->output_path_full .= "{$this->output_path}/{$this->output_filename}.{$this->output_extension}";
    }
}

class video_buffer extends media_buffer {
    public int $duration;
    public string $duration_time;
    public string $output_subtitle_path_full;

    public function __construct(string $source_path, int $duration) {
        $this->type = option_type::video;
        $this->output_extension = 'mp4';
        $this->duration = $duration;
        $this->duration_time = date('H:i:s', $duration);
        parent::__construct($source_path, 'video');
        $this->output_subtitle_path_full = "{$this->output_path}/{$this->output_filename}.vtt";
    }
}

class audio_buffer extends media_buffer {
    public function __construct (string $source_path) {
        $this->type = option_type::audio;
        $this->output_extension = 'mp3';
        parent::__construct($source_path, 'audio');
    }
}

class transcoder {
    //TODO: figure out interrupting and continuing transcoding at selected time without redoing the transcode, also multistream encoding
    //TODO: maybe do fully encoding and display which have and which haven't been encoded in the interface
    //TODO: if on the fly transcoding won't work, transcode 360 -> 480 -> 720
    //TODO: use adaptive bit rate format streaming
    //TODO: use queue/pipelines for transcoding
    //TODO: use temporary queue, detect if script is running, if so, add to temporary queue (queue in database?)
    //TODO: check if database queue has jobs, if not run the job, if a job is running, add to database queue, if the job is done processing, check in database queue if another job has to be run 
    //TODO: if queue has multiple jobs, run queries with execute_multiple
    //TODO: separate selected subtitles and dynamically load them
    //TODO: use cover instead of extracting thumbnail from video
    //TODO: add option to upload subtitle for video

    // -c:v stands for -codec:video
    // -c:s copy stands for -codec:subtitle, copy is there so no decoding-filtering-encoding operations will, or can occur.
    // -map 3:m:language:eng choose stream, 0 is all, 1 is video, 2 is audio, 3 is subtitles
    // -crf stands for constant rate factor, it has a range of 0-51, 0 is lossless, 23 is default, 51 is worst, 18 is nearly visually lossless
    private array $options = [
        'video' => [
            'codec'     => '-c:v libx264',
            'threads'   => '-threads 6',
            'preset'    => '-preset ultrafast',
            'rate'      => '-crf 16',
        ],
        'subtitles' => [
            'map'       => '-map 0:m:language:eng',
            'codec'     => '-c:s copy',
        ],
    ];

    // skip waiting, but also removes stdio and stderr
    private string $silence = ' > /dev/null 2>/dev/null &';
    // private string $out = ' > out.log 2>&1';

    public function option_set(string $type, string $key, string $option) : void {
        $this->options[$type][$key] = $option;
    }
    
    public function ffmpeg(media_buffer $buffer, bool $silent = true) : void {
        $silence = $silent ? $this->silence : '';
        
        // video encoding
        $options = implode(' ', $this->options[$buffer->type]);
        $command = "ffmpeg -i {$buffer->source_path} $options {$buffer->output_path_full}" . $silence;
        dd($command);
        shell_exec($command);

        // subtitle extraction
        // shell_exec("ffmpeg -i video -c copy -map 0:s -f null - -v 0 -hide_banner && echo $? || echo $?"); // check if any subtitle exists
        if ($buffer->output_extension === 'mp4') {
            $this->extract_subtitles($buffer, $silence);
        }
    }

    private function extract_subtitles(video_buffer $buffer, string $silence) : void {
        $options = implode(' ', $this->options[option_type::subtitles]);
        $command  = "ffmpeg -i {$buffer->source_path} $options {$buffer->output_subtitle_path_full}" . $silence;
        dd($command);
        shell_exec($command);
    }
}

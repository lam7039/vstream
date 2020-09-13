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
        $this->output_filename = $this->source_filename; //TODO: create proper filename with regex
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
        $this->output_extension = 'webm';
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
        $this->output_extension = 'webm';
        $this->visualize = $visualize;
        parent::__construct($source_path, 'audio', $silent);
    }
}

class transcoder {
    //TODO: figure out interrupting and continuing transcoding without redoing the transcode, also multistream encoding
    //TODO: do fully encoding and display which have and which haven't been encoded in the interface
    //TODO: use queue/pipelines for transcoding
    //TODO: use temporary queue, detect if script is running, if so, add to temporary queue (queue in database?)
    //TODO: check if database queue has jobs, if not run the job, if a job is running, add to database queue, if the job is done processing, check in database queue if another job has to be run 
    //TODO: if queue has multiple jobs, run queries with execute_multiple
    //TODO: use cover instead of extracting thumbnail from video
    //TODO: add option to upload subtitle for video

    private array $options = [
        'video' => [
            'banner'        => '-hide_banner',
            'codecvideo'    => '-c:v libvpx-vp9',
            'codecaudio'    => '-c:a libopus',
            'quality'       => '-quality good',
            'speed'         => '-speed 2',
            'bitratevideo'  => '-b:v 4000k',
            'bitratefactor' => '-crf 18',
            'threading'     => '-row-mt 1',
            'threads'       => '-threads 6',
        ],
        'audio' => [
            'banner'        => '-hide_banner',
            'codecaudio'    => '-c:a libopus',
            'stream'        => '-map 0:a',
            'bitrateaudio'  => '-b:a 300k',
        ],
    ];

    private function start_process_windows(string $command) : void {
        $process = popen('start /B ' . $command, 'r');
        pclose($process);
    }

    public function option_set(string $type, string $key, string $option) : void {
        $this->options[$type][$key] = $option;
    }

    public function visualize_audio() : void {
        $this->option_set('audio', 'codecvideo', '-c:v libvpx-vp9');
        $this->option_set('audio', 'filter', '-filter_complex "showwaves=s=1280x720:mode=line:colors=white:rate=30,format=yuv420p[vid]"');
        $this->option_set('audio', 'destination', '-map "[vid]"');
        $this->option_set('audio', 'speed', '-speed 4');
        $this->option_set('audio', 'quality', '-quality good');
        $this->option_set('audio', 'bitratefactor', '-crf 24');
        $this->option_set('audio', 'bitratevideo', '-b:v 2000k');
    }
    
    public function ffmpeg(media_buffer $buffer) : void {
        $buffer->options = implode(' ', $this->options[$buffer->type]);
        $command = $this->{"build_{$buffer->type}_command"}($buffer);
        dd($command);
        
        set_time_limit(10800);

        // linux
        // shell_exec($command);

        // windows
        $this->start_process_windows($command);
    }

    public function build_audio_command(audio_buffer $buffer) : string {
        if ($buffer->visualize) {
            $this->visualize_audio();
        }
        return "ffmpeg -i {$buffer->source_path} {$buffer->options} {$buffer->output_path_full}";
    }

    public function build_video_command(video_buffer $buffer) : string {
        $command = "ffmpeg -i {$buffer->source_path} ";
        $subtitles = $buffer->subtitles_type === 'soft' ? $this->extract_subtitles($buffer) : '';
        if ($buffer->subtitles_type === 'hard') {
            $source_path = str_replace(':', '\:', $buffer->source_path);
            $source_path = str_replace('"', '\'', $source_path);
            $command .= "-c:s copy -vf \"subtitles=$source_path\" ";
        }
        $command .= "{$buffer->options} -map 0:m:language:{$buffer->audio_language} ";
        return "$command {$buffer->output_path_full}" . $subtitles;
    }

    private function extract_subtitles(video_buffer $buffer) : string {
        // if (!$this->has_subtitles($buffer->source_path)) {
        //     return '';
        // }
        return " && ffmpeg -i {$buffer->source_path} {$buffer->output_subtitle_path_full}";
    }

    // private function has_subtitles(string $path) : bool {
    //     return !boolval(shell_exec("ffprobe $path -show_streams -select_streams s 2>&1|grep language=eng"));
    //     //ffmpeg -i $path -c copy -map 0:s -f null - -v 0 -hide_banner && echo $? || echo $?
    // }
}

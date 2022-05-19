<?php

namespace App\Service;

use Illuminate\Support\Facades\Storage;

class ChannelManager
{
    const STORAGE = 'public/channels';
    const SILENCE_START_STRING = 'silence_start:';
    const SILENCE_END_STRING = 'silence_end:';
    const SILENCE_DURATION_STRING = 'silence_duration:';

    protected $channelFile = '';
    protected $times = [];

    public function __construct(string $channelFile)
    {
        $this->channelFile = $channelFile;
        $this->readContent();
    }

    public function getTimes(): array
    {
        return $this->times;
    }

    private function readContent(): void
    {
        $currentContext = self::SILENCE_START_STRING;
        $endTime = 0;
        $file = fopen(Storage::path(self::STORAGE . '/' . $this->channelFile),'r');
        while(!feof($file)) {
            $line = fgets($file);
            if (!$line) {
                break;
            }
            if ($currentContext === self::SILENCE_START_STRING) {
                $startTime = floatval($this->getStringBetween($line, self::SILENCE_START_STRING));
                $currentContext = self::SILENCE_END_STRING;
                array_push($this->times, [$endTime, $startTime]);
            } else {
                $endTime = floatval($this->getStringBetween($line, self::SILENCE_END_STRING, '|'));
                $currentContext = self::SILENCE_START_STRING;
                $silenceDuration = floatval($this->getStringBetween($line, self::SILENCE_DURATION_STRING));
            }
        }
        fclose($file);
    }

    private function getStringBetween($string, $start, $end = ''){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        if ($end === '') {
            $len = strlen($string) - $ini;
        } else {
            $len = strpos($string, $end, $ini) - $ini;
        }

        return substr($string, $ini, $len);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class ProcessController extends Controller
{
    const STORAGE = 'public/channels';
    const SILENCE_START_STRING = 'silence_start:';
    const SILENCE_END_STRING = 'silence_end:';
    const SILENCE_DURATION_STRING = 'silence_duration:';

    /**
     * Parse file.
     * @param string $fileName
     * @return array
     */
    public function parse(string $fileName)
    {
        $fileContent = $this->getFileContent($fileName);
        $this->readContent($fileContent);

        var_dump($fileName);
        return [];
    }

    public function getFileContent(string $fileName)
    {
        return fopen(Storage::path(self::STORAGE . '/' . $fileName),'r');
    }

    public function readContent($fileContent): void
    {
        $currentContext = self::SILENCE_START_STRING;
        $endTime = 0;
        $waveformArray = [];
        while(!feof($fileContent)){
            $line = fgets($fileContent);
            if ($currentContext === self::SILENCE_START_STRING) {
                $startTime = floatval($this->getStringBetween($line, self::SILENCE_START_STRING));
                $currentContext = self::SILENCE_END_STRING;
                array_push($waveformArray, [$endTime, $startTime]);
            } else {
                $endTime = floatval($this->getStringBetween($line, self::SILENCE_END_STRING, '|'));
                $currentContext = self::SILENCE_START_STRING;
            }

            // var_dump(floatval($endTime));
            // echo $line."<br>";
        }
        fclose($fileContent);

        var_dump($waveformArray);
    }

    public function getStringBetween($string, $start, $end = ''){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        if ($end === '') {
            $len = strlen($string) - $ini;
        } else {
            $len = strpos($string, $end, $ini) - $ini;
        }

        return substr($string, $ini, $len);
    }
}

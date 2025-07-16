<?php

namespace App\Service;

class FileNamingService
{
    public function getFilename(string $filename): string
    {
        $filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);

        $filename = preg_replace('/[^a-zA-Z0-9]/', '-', $filename);

        $filename = trim(preg_replace('/-+/', '-', $filename), '-');

        return strtolower($filename);
    }
}

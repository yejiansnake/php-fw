<?php

namespace app\lib\common;

class TmpFile
{
    public static function getFilePath($fileName)
    {
        $filePath = self::createFilePath($fileName);

        if (!file_exists($filePath))
        {
            return '';
        }

        return $filePath;
    }

    public static function append($fileName, $text)
    {
        $filePath = self::createFilePath($fileName);

        file_put_contents($filePath, $text, FILE_APPEND);
    }

    protected static function createFilePath($fileName)
    {
        return '/tmp/' . APP_SYS_NAME . "_{$fileName}";
    }
}
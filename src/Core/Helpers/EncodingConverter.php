<?php

namespace Griiv\SynchroEngine\Core\Helpers;

class EncodingConverter extends \php_user_filter
{
    private static $inputEncoding;
    private static $outputEncoding;

    function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = mb_convert_encoding($bucket->data, self::$outputEncoding, self::$inputEncoding);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }

    static function setInputEncoding($encoding)
    {
        self::$inputEncoding = $encoding;
    }

    static function setOutputEncoding($encoding)
    {
        self::$outputEncoding = $encoding;
    }
}
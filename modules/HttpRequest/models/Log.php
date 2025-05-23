<?php
class HttpRequest_Log_Model {
    public static function log($message) {
        $file = 'logs/httprequest.log';
        $line = date('Y-m-d H:i:s').' '.trim($message)."\n";
        file_put_contents($file, $line, FILE_APPEND);
    }
}


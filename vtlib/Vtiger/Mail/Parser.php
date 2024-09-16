<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once 'vtlib/thirdparty/Sexpr.php';

class Vtiger_Mail_Envelope_Parser {

    public function parse($string) {
        $SEXP = new DrSlump\Sexp();
        $envelope = array();

        /*
        stdClass Object
        (
            [date] => Mon, 27 Feb 2023 04:23:23 -0500
            [Date] => Mon, 27 Feb 2023 04:23:23 -0500
            [subject] => Welcome! Let's get you up and running.
            [Subject] => Welcome! Let's get you up and running.
            [message_id] => <16774898031.Cf95cC21.3472665@web2.nyi.internal>
            [toaddress] => mailtwopa <mailtwopa@fastmail.com>
            [to] => Array
                (
                    [0] => stdClass Object
                        (
                            [personal] => mailtwopa
                            [mailbox] => mailtwopa
                            [host] => fastmail.com
                        )

                )

            [fromaddress] => The Fastmail Team <support@fastmail.com>
            [from] => Array
                (
                    [0] => stdClass Object
                        (
                            [personal] => The Fastmail Team
                            [mailbox] => support
                            [host] => fastmail.com
                        )

                )

            [reply_toaddress] => support@fastmail.com
            [reply_to] => Array
                (
                    [0] => stdClass Object
                        (
                            [mailbox] => support
                            [host] => fastmail.com
                        )

                )

            [senderaddress] => The Fastmail Team <support@fastmail.com>
            [sender] => Array
                (
                    [0] => stdClass Object
                        (
                            [personal] => The Fastmail Team
                            [mailbox] => support
                            [host] => fastmail.com
                        )

                )

            [Recent] =>  
            [Unseen] =>  
            [Flagged] =>  
            [Answered] =>  
            [Deleted] =>  
            [Draft] =>  
            [Msgno] =>    1
            [MailDate] => 27-Feb-2023 04:23:25 -0500
            [Size] => 18014
            [udate] => 1677489805
        )*/

        if (preg_match("/ENVELOPE\s*(.*)/i", $string, $m)) {
            $parts = $SEXP->parse($m[1])[0];

            $envelope["date"] = $envelope["Date"] = $parts[0];
            $envelope["udate"] = strtotime($envelope["date"]);
            $envelope["subject"] = $envelope["Subject"] = $parts[1];
            $envelope["message_id"] = $parts[9];
            $envelope["to"] = array();
            foreach ($parts[5] as $to) {
                $envelope["to"][] = (object) array(
                    "personal" => $to[0],
                    "mailbox"  => $to[2],
                    "host"     => $to[3]
                );
            }
            $envelope["from"] = array();
            foreach ($parts[2] as $from) {
                $envelope["from"][] = (object) array(
                    "personal" => $from[0],
                    "mailbox"  => $from[2],
                    "host"     => $from[3]
                );
            }
            $envelope["sender"] = $envelope["from"]; // TODO - override?
        }

        return (object) $envelope;
    }
}

class Vtiger_Mail_BodyStructure_Parser {

    private static $SEXP;
    private static $SUBTYPES = ['MIXED', 'MESSAGE', 'DIGEST', 'ALTERNATIVE', 'RELATED', 'REPORT','SIGNED','ENCRYPTED','FORM DATA'];
    private static $BODYSTRUCTURE_RE = '/.*\(BODY\w{0,9} (.*)\)/i';
    private static $CONTENT_TYPE_RE = '/^\s*"(TEXT|APPLICATION|IMAGE|VIDEO|AUDIO)"/i';
    private static $MULTIPART_SUBTYPE_RE;
    
    public function __construct() {
        if (!static::$SEXP) {
            static::$SEXP = new DrSlump\Sexp();
        }
        if (!static::$MULTIPART_SUBTYPE_RE) {
            static::$MULTIPART_SUBTYPE_RE = sprintf('/^\s*"(%s)"/i', implode('|', static::$SUBTYPES));
        }
    }

    private static function typeToValue($v) {
        static $codes = array("text" => 0, "multipart" => 1, "message" => 2, "application" => 3, "audio" => 4, "image" => 5, "video" => 6, "model" => 7, "other" => 8);
        $v = strtolower($v);
        return isset($codes[$v])? $codes[$v] : $codes['other'];
    }

    private static function encodingToValue($v) {
        static $encodings = array("7bit" => 0, "8bit" => 1, "binary" => 2, "base64" => 3, "quoted-printable" => 4, "other" => 5);
        $v = strtolower($v);
        return isset($encodings[$v])? $encodings[$v]: $encodings['other'];
    }

    private static function array_insert(&$array, $position, $value) {
        $new_array = array();
        $array_len = count($array);

        if ($position < $array_len) {
            for ($i = 0; $i < $position; $i++) {
                $new_array[] = $array[$i];
            }
            $new_array[] = $value;
            for ($j = $position; $j < $array_len; $j++) {
                $new_array[] = $array[$j];
            }
        } else {
            for ($i = 0; $i < $array_len; $i++) {
                $new_array[] = $array[$i];
            }
            $new_array[] = $value;
        }
        $array = $new_array;
        return $array;
    }

    public function parse($string) {
        if (preg_match(static::$BODYSTRUCTURE_RE, $string, $m)) {
            $body = $m[1];
            $parts = [];
            foreach (static::parse_parts($string) as $parsed_part) {
                $multipart_subtype = $parsed_part[0];
                $depth = $parsed_part[1];
                $text = $parsed_part[2];

                if (preg_match(static::$CONTENT_TYPE_RE, $text, $m)) {
                    $parts[] = [$depth, $text];
                }
                if ($multipart_subtype) {
                    $i = count($parts) - 1;
                    while ($i >= 0 && $depth < $parts[$i][0]) {
                        $i -= 1;
                    }
                    static::array_insert($parts, $i+1, [$depth-1, $multipart_subtype]);
                }
            }
        }
        return static::transform_parts($parts);
    }

    private static function parse_parts($string) {
        $result = [];
        $open_paren_pos = [];
        for ($ch_pos=0, $len=strlen($string); $ch_pos < $len; ++$ch_pos) {
            $char = $string[$ch_pos];
            if ($char == '(') {
                $open_paren_pos[] = $ch_pos;
            } else if ($char == ')') {
                $start_pos = array_pop($open_paren_pos);
                $text = substr($string, $start_pos+1, $ch_pos-($start_pos+1));
                $depth = count($open_paren_pos);
                if (preg_match(static::$MULTIPART_SUBTYPE_RE, substr($string, $ch_pos+1), $m)) {
                    $result[] = [$m[1], $depth, $text];
                } else {
                    $result[] = ['', $depth, $text];
                }
            }
        }
        return $result;
    }

    private static function transform_parts($parts) {
        // sorted_parts order by max($depth = $parts[0])
        $sorted_parts = json_decode(json_encode($parts), true);
        uasort($sorted_parts, function ($a, $b) { return $a[0] < $b[0]; });
        $sorted_parts = array_values($sorted_parts);

        $part_nums = array_fill(0, $sorted_parts[0][0], -1);
        
        $tparts = [];
        foreach ($parts as $p) {
            $depth = $p[0];
            $text = $p[1];
            $is_multipart = in_array(strtoupper($text), static::$SUBTYPES);

            $partnum = 0;
            if ($depth > 1) {
                $part_nums[$depth - 2] += 1;
                $partnum = [];
                for ($i = 0; $i < $depth-1; ++$i) {
                    $partnum[] = $part_nums[$i];
                }
                $partnum = implode('.', $partnum);
            }

            if ($is_multipart) {
                $tparts = [ "type" => 1, "subtype" => "$text", "partno" => $partnum, "parts" => (object) $tparts ];
            } else {

                $ptext = static::$SEXP->parse("($text)");

                $pobj = [];
                if ($partnum) $pobj["partno"] = $partnum;
                $pobj["type"] = static::typeToValue($ptext[0]);
                if ($ptext[1] != 'NIL') $pobj["subtype"] = strtolower($ptext[1]);
                $pobj["ifsubtype"] = isset($pobj['subtype']) ? 1 : 0;
                if (isset($ptext[2]) && $ptext[2] != 'NIL') {
                    $params = [];
                    for ($i = 0, $len=count($ptext[2]); $i < $len; ) {
                        $params["attribute"] = $ptext[2][$i];
                        $params["value"] = $ptext[2][$i+1];
                        $i += 2;
                    }
                    $pobj["parameters"] = (object) $params;
                }
                $pobj["ifparameters"] = isset($pobj["parameters"]) ? 1 : 0;
                if ($ptext[3] != 'NIL') $pobj["id"] = $ptext[3];
                $pobj["ifid"] = isset($pobj['id']);
                if ($ptext[4] != 'NIL') $pobj["description"] = $ptext[4];
                $pobj["ifdescription"] = isset($pobj['description']) ? 1 : 0;
                $pobj["encoding"] = static::encodingToValue($ptext[5]);
                $pobj["bytes"] = $ptext[6];
                $pobj["lines"] = $ptext[7];
                if (isset($ptext[8]) && $ptext[8] != 'NIL') {
                    $dparams = [];
                    for ($i = 0, $len=count($ptext[8]); $i < $len; ) {
                        $dparams["attribute"] = $ptext[8][$i];
                        $dparams["value"] = $ptext[8][$i+1];
                        $i += 2;
                    }
                    $pobj["dparameters"] = (object) $dparams;
                }
                $pobj["ifdparameters"] = isset($pobj["dparameters"]) ? 1 : 0;

                if ($partnum) $tparts[$partnum] = (object)$pobj;
                else $tparts[] = (object)$pobj;
            }
        }
        // Determine Mono message or Multipart - convert to object before return.
        return (object) (isset($tparts[0]) ? $tparts[0] : $tparts);
    }
}
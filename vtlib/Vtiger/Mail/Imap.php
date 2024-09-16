<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once 'Parser.php';

class Vtiger_Mail_Imap {

    private $host;
    private $port;
    private $username;
    private $password;
    private $authType;
    private $folder;
    private $fp;
    private $count;
    private $_debug = false;
    private static $_errors;

    public static function errors() {
        return static::$_errors;
    }

    public static function iserror() {
        return !empty(static::$_errors);
    }

    public function __construct($url, $username, $password, $type = null) {

        // {host:port/imap/ssl/novalidate-cert}
        if (preg_match("/{([^:]+):([^\/]+)\/[^\/]+\/([^\/]+)\/[^\/]+}/", $url, $m)) {
            $this->host = $m[3] . "://" . $m[1];
            $this->port = $m[2];
        } else if (preg_match("/{([^:]+):([^\/]+)\/[^\/]+\/([^}]+)}/", $url, $m)) {
            // {host:port/imap/ssl}
            $this->host = $m[3] . "://" . $m[1];
            $this->port = $m[2];
        }

        // Determine folder from URL {...}FOLDER.
        if (preg_match("/\{.*\}(.+)/", $url, $m)) {
            $this->folder = $m[1];
        } else {
            $this->folder = "INBOX";
        }
        
        $this->count = 1;
        $this->username = $username;
        $this->password = $password;
        if (!is_null($type) && strtolower($type) == "oauth2") {
            $this->authType = "OAUTH2";
        } else {
            $this->authType = "LOGIN";
        }
    }

    private function debug($message, $dir = "") {
        if ($this->_debug) echo $dir . $message;
    }

    private function ensureConnect() {
        if ($this->fp) return true;
        $this->fp = fsockopen($this->host, $this->port, $errno, $errstr, 60);
        return $this->fp ? true : false;
    }

    private function sendCommand($code, $command) {
        $cmd = $code . " " . $command . "\r\n";
        $this->debug($cmd, ">");
        fwrite($this->fp, $cmd);
        static::$_errors = array();
    }

    private function readResponse($code) {
        static::$_errors = array();

        $lines = array();
        while ($line = fgets($this->fp)) {
            $this->debug($line);

            if (stripos($line, "$code OK ") !== 0 && $line != ")\r\n") { // V3 OK ... (or) FETCH (BODY[1] {count}...\r\n... )
                if (stripos($line, "$code BAD ") !== false ||  stripos($line, "$code NO ") !== false) {
                    static::$_errors[] = trim(str_replace($code, '', $line));
                } else {
                    $lines[] = $line;
                }
            }

            // Command response complete?
            if (strpos($line, $code) === 0) {
                break;
            }
        }
        $this->count++;
        return $lines;
    }

    public function doClose() {
        if ($this->fp) {
            fclose($this->fp);
            $this->fp = null;
        }
    }

    public function doLogin() {
        $connected = $this->ensureConnect();
        if (!$connected) return false;

        $username = $this->username;
        $password = $this->password;

        $command = "";
        if ($this->authType == "OAUTH2") {
            $token = base64_encode("user=$username\1auth=Bearer $password\1\1");
            $command = "AUTHENTICATE XOAUTH2 $token";
        } else {
            $command = "LOGIN $username $password";
        }
        $this->sendCommand("V".$this->count, $command);
        $res = $this->readResponse("V".$this->count);

        if (static::iserror()) {
            return false;
        }

        return true;
    }

    public function doListFolders($ref, $pattern) {
        $this->sendCommand("V".$this->count, "LIST \"\" \"$pattern\"");
        $lines = $this->readResponse("V".$this->count);
        $folders = array();
        foreach ($lines as $line) {
            if (preg_match('/[*][^"]+"\/"[ ]+(.*)/', $line, $m)) {
                $folders[] = sprintf("%s%s", $ref, trim($m[1]));
            }
        }
        return $folders;
    }

    public function doGetMailboxes($ref, $pattern) {
        $folders = $this->doListFolders($ref, $pattern);
        $boxes = array();
        foreach ($folders as $folder) {
            $boxes[] = (object) array(
                "name" => $folder,
                "delimiter" => "/"
            );
        }
        return $boxes;
    }

    public function doFolderCheck($folder) {
        $this->sendCommand("V".$this->count, "EXAMINE \"$folder\"");
        $res = $this->readResponse("V".$this->count);
        $info = array();
        foreach ($res as $line) {
            if (preg_match("/[* ]+([0-9]+)[ ]+(.*)/", $line, $m)) {
                switch(trim($m[2])) {
                    case "EXISTS": $info["Nmsgs"] = (int) $m[1]; break;
                    case "RECENT": $info["Recent"] = (int) $m[1]; break;
                }
            }
        }
        return (object) $info;
    }

    public function doSelect($folder) {
        $this->sendCommand("V".$this->count, "SELECT $folder");
        $res = $this->readResponse("V".$this->count);
        // TODO: Validate response.
    }

    public function doSearch($query) {
        $this->doSelect($this->folder);
        $this->sendCommand("V".$this->count, "SEARCH $query");
        $res = $this->readResponse("V".$this->count);
        $res = array_pop($res);
        return explode(' ', trim(substr($res, stripos($res, 'search') + 6)));
    }

    public function doFetchOverview($sequence, $flags = null) {
        $this->doSelect($this->folder);

        $this->sendCommand("V".$this->count, "FETCH $sequence (UID RFC822.SIZE FLAGS RFC822.HEADER)");
        $res = $this->readResponse("V".$this->count);

        $headers = array("subject", "from", "to", "date", "message_id", "references", "in_reply_to", "udate");

        $overviews = array();
        $current = null;

        /* TODO: Review flags
            [subject] => Subject here.
            [from] => <***>
            [to] => *** <***>
            [date] => Mon, 27 Feb 2023 04:27:21 -0500
            [message_id] => <16774900411.FCD71446.2613663@internal>
            [size] => 21732
            [uid] => 3
            [msgno] => 3
            [recent] => 0
            [flagged] => 0
            [answered] => 0
            [deleted] => 0
            [seen] => 0
            [draft] => 0
            [udate] => 1677490041
        )
        */

        $lastkey = null;
        foreach ($res as $rawline) {
            $line = trim($rawline);
            if (preg_match("/[* ]+([0-9]+) FETCH \(UID[ ]+([^ ]+)[ ]+RFC822.SIZE[ ]+([^ ]+)/", $line, $m)) {
                if ($current) {
                    $overviews[] = (object)$current;
                    $current = null;
                    $lastkey = null;
                }
                $current = array();
                $current["msgno"]= $m[1];
                $current["uid"] = $m[2];
                $current["size"] = $m[3];
                $lastkey = null;
            } else if ($line && $current != null) {
                if ($rawline[0] != " " && $rawline[0] != "\t") {
                    $colonidx = strpos($line, ":");
                    if ($colonidx !== false) {
                        $key = str_replace("-", "_", strtolower(substr($line, 0, $colonidx)));
                        if (in_array($key, $headers)) {
                            $current[$key] = substr($line, $colonidx+1);
                            if ($key == "date") {
                                // special
                                $current["udate"] = strtotime($current["date"]);
                            }
                        }
                        $lastkey = $key;
                    }
                } else if ($lastkey) {
                    if (in_array($lastkey, $headers)) {
                        if (!isset($current[$lastkey])) $current[$lastkey] = '';
                        $current[$lastkey] .= $line;
                    }
                }
            }
        }
        if ($current) {
            $overviews[] = (object) $current;
        }

        return $overviews;
    }

    public function doStatus($folder, $options) {
        $folder = preg_replace("/\{.*\}(.*)/", "$1", $folder);

        $this->sendCommand("V".$this->count, "STATUS $folder (MESSAGES RECENT UIDNEXT UNSEEN)");
        $res = $this->readResponse("V".$this->count);
        
        $statuses = array();
        if (preg_match("/[*] STATUS .*\((.*)\)/", $res[0], $m)) {
            $parts = explode(' ', $m[1]);
            $pairs = array();
            for ($i = 0, $len = count($parts); $i < $len; $i+=2) {
                $statuses[strtolower($parts[$i])] = $parts[$i+1];
            }
        }
        return $statuses;
    }


    public function doGetUID($msgno) {
        $this->doSelect($this->folder);

        $this->sendCommand("V".$this->count, "UID FETCH $msgno FLAGS");
        $res = $this->readResponse("V".$this->count);
        $uids = array();
        foreach($res as $line) {
            if (strpos($line, "*") === 0 && preg_match("/.*UID[ ]+([^)]+)/", $line, $m)) {
                $uids[] = $m[1];
            }
        }
        return count($uids) == 1 ? array_shift($uids) : $uids;
    }

    public function doGetBody($msgid) {
        $this->doSelect($this->folder);

        $this->sendCommand("V".$this->count, "FETCH $msgid RFC822.TEXT");
        $res = $this->readResponse("V".$this->count);

        if (preg_match("/FETCH.*\{([0-9]+)\}/", $res[0], $m)) {
            array_shift($res);
            $res = substr(implode('', $res), 0, $m[1]);
        } else {
            $res = implode('', $res);
        }

        return $res;
    }

    public function doFetchHeader($msgid) {
        $this->doSelect($this->folder);

        $this->sendCommand("V".$this->count, "FETCH $msgid ENVELOPE");
        $res = $this->readResponse("V".$this->count);

        $parser = new Vtiger_Mail_Envelope_Parser();
        return $parser->parse($res[0]);

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
    }

    public function doFetchBody($msgid, $partno, $flags = null) {
        $this->doSelect($this->folder);
        
        $fetch = "";
        if ((int)$partno == 0) {
            $fetch = "RFC822.HEADER";
        } else {
            $fetch = "BODY[$partno]";
        }

        $this->sendCommand("V".$this->count, "FETCH $msgid $fetch");
        $res = $this->readResponse("V".$this->count);

        if (preg_match("/FETCH.*\{([0-9]+)\}/", $res[0], $m)) {
            array_shift($res);
            $res = substr(implode('', $res), 0, $m[1]);
        } else {
            $res = implode('', $res);
        }
        return $res;
    }

    public function doFetchStructure($msgid) {
        $this->doSelect($this->folder);
        
        $this->sendCommand("V".$this->count, "FETCH $msgid BODYSTRUCTURE");
        $res = $this->readResponse("V".$this->count);
        $parser = new Vtiger_Mail_BodyStructure_Parser();
        return $parser->parse($res[0]);
    }
}

function imapv_open($url, $username, $password, $type = null) {
    if ($type == null) {
        if (stripos($url, 'office365.com') !== false) $type = "OAUTH2";
        else $type = "LOGIN";
    }

    if ($type) {
        $connector = new Vtiger_Mail_Imap($url, $username, $password, $type);
        $ok = $connector->doLogin();
        return $ok ? $connector : false;
    }
    return imap_open($url, $username, $password);
}

function imapv_close($connector) {
    return is_a($connector, 'Vtiger_Mail_Imap') ?
        $connector->doClose() :
        imap_close($connector);
}

function imapv_getmailboxes($connector, $ref, $pattern) {
    return is_a($connector, 'Vtiger_Mail_Imap') ? 
        $connector->doGetMailboxes($ref, $pattern) :
        imap_getmailboxes($connector, $ref, $pattern);
}

function imapv_listmailbox($connector, $ref, $pattern) {
    return is_a($connector, 'Vtiger_Mail_Imap') ? 
        $connector->doListFolders($ref, $pattern) :
        imap_listmailbox($connector, $ref, $pattern);
}

function imapv_list($connector, $ref, $pattern) {
    return imapv_listmailbox($connector, $ref, $pattern);
}

function imapv_check($connector) {
    return is_a($connector, 'Vtiger_Mail_Imap') ? 
        $connector->doFolderCheck("INBOX") :
        imap_check($connector);
}

function imapv_search($connector, $query) {
    return is_a($connector, 'Vtiger_Mail_Imap') ?
        $connector->doSearch($query) :
        imap_search($connector, $query);
}

function imapv_fetch_overview($connector, $sequence, $flags = null) {
    return is_a($connector, 'Vtiger_Mail_Imap') ?
        $connector->doFetchOverview($sequence, $flags) :
        imap_fetch_overview($connector, $sequence, $flags);
}

function imapv_uid($connector, $msgno) {
    return is_a($connector, 'Vtiger_Mail_Imap') ?
        $connector->doGetUID($msgno) :
        imap_uid($connector, $msgno);
}

function imapv_body($connector, $msgid) {
    return is_a($connector, 'Vtiger_Mail_Imap') ?
        $connector->doGetBody($msgid) :
        imap_body($connector, $msgid);
}

function imapv_fetchbody($connector, $msgid, $partno, $flags = null) {
    return is_a($connector, 'Vtiger_Mail_Imap') ?
        $connector->doFetchBody($msgid, $partno, $flags) :
        imap_fetchbody($connector, $msgid, $partno, $flags);
}

function imapv_status($connector, $folder, $options) {
    return is_a($connector, 'Vtiger_Mail_Imap') ?
        $connector->doStatus($folder, $options) :
        imap_status($connector, $folder, $options);
}

function imapv_fetchstructure($connector, $msgid) {
    return is_a($connector, 'Vtiger_Mail_Imap') ?
        $connector->doFetchStructure($msgid) :
        imap_fetchstructure($connector, $msgid);
}

function imapv_headerinfo($connector, $msgid) {
    return is_a($connector, 'Vtiger_Mail_Imap') ?
        $connector->doFetchHeader($msgid) :
        imap_headerinfo($connector, $msgid);
}

function imapv_clearflag_full($connector, $flags) {
    // TODO
}

function imapv_setflag_full($connector, $flags) {
    // TODO
}

function imapv_mail_move() {
    // TODO
}

function imapv_expunge() {
    // TODO
}

function imapv_delete($connector, $sequence, $flags = 0) {
    // TODO
}

function imapv_errors() {
    $errors = Vtiger_Mail_Imap::errors();
    if (is_null($errors)) {
        $errors = imap_errors();
    }
    return $errors;
}

function imapv_last_error() {
    $errors = Vtiger_Mail_Imap::errors();
    if (is_array($errors)) {
        return $errors[ count($errors) - 1 ];
    }
    return imap_last_error();
}

function imapv_utf8($v) {
    return imap_utf8($v);
}

function imapv_qprint($v) {
    return imap_qprint($v);
}

function imapv_mime_header_decode($v) {
    return imap_mime_header_decode($v);
}

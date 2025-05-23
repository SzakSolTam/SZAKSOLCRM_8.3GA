<?php
class VTHttpTask extends VTTask {
    public $executeImmediately = true;

    public function getFieldNames() {
        return array('title','description','url','method','content_type','auth_type','username','password','headers','parameters');
    }

    public function doTask($entity) {
        $url = trim($this->url);
        if(!filter_var($url, FILTER_VALIDATE_URL)) {
            return;
        }
        $method = strtoupper($this->method);
        if(!in_array($method, array('GET','POST','PUT','PATCH','DELETE','HEAD'))) {
            $method = 'POST';
        }
        $params = $this->buildParams($entity);
        $headers = $this->buildHeaders();

        $body = null;
        if($method == 'GET' || $method == 'HEAD') {
            if(!empty($params)) {
                $url .= (strpos($url,'?')===false?'?':'&').http_build_query($params);
            }
        } else {
            if($this->content_type == 'application/json') {
                $body = Zend_Json::encode($params);
            } elseif($this->content_type == 'text/xml') {
                $body = $this->arrayToXml($params);
            } else {
                $body = http_build_query($params);
            }
            if($this->content_type) {
                $headers[] = 'Content-Type: '.$this->content_type;
            }
        }

        if($this->auth_type == 'basic' && $this->username && $this->password) {
            $headers[] = 'Authorization: Basic '.base64_encode($this->username.':'.$this->password);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if($method == 'HEAD') curl_setopt($ch, CURLOPT_NOBODY, true);
        if($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        if(!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if(curl_errno($ch)) {
            $error = curl_error($ch);
            HttpRequest_Log_Model::log("Error: $error URL:$url Status:$httpCode");
        } else {
            HttpRequest_Log_Model::log("URL:$url Status:$httpCode");
        }
        curl_close($ch);
    }

    protected function buildParams($entity) {
        $params = array();
        if(!empty($this->parameters)) {
            $list = Zend_Json::decode($this->parameters);
            if(is_array($list)) {
                foreach($list as $p) {
                    $name = trim($p['name']);
                    if($name === '') continue;
                    if($p['source'] === 'field') {
                        $params[$name] = $entity->get($p['value']);
                    } else {
                        $params[$name] = $p['value'];
                    }
                }
            }
        }
        return $params;
    }

    protected function buildHeaders() {
        $headers = array();
        if(!empty($this->headers)) {
            $list = Zend_Json::decode($this->headers);
            if(is_array($list)) {
                foreach($list as $h) {
                    $name = trim($h['name']);
                    $val = trim($h['value']);
                    if($name=='' || strcasecmp($name,'Content-Type')==0 || strcasecmp($name,'Authorization')==0) continue;
                    $headers[] = $name.': '.$val;
                }
            }
        }
        return $headers;
    }

    protected function arrayToXml($arr){
        $xml = new SimpleXMLElement('<data/>' );
        foreach($arr as $k=>$v){
            $xml->addChild($k, htmlspecialchars($v));
        }
        return $xml->asXML();
    }
}


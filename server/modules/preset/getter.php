<?php

class Getter
{
    private $_data = false;
    private $_core;
    private $_required = array();

    function __construct($CONFIG)
    {
        $this->_required[] = "core";
    }

    public function required() {
        return $this->_required;
    }

    public function init($required) {
        if(isset($required["core"])) {
            $this->_core = $required["core"];
            if(method_exists($this->_core,"arrayExtract"))
                return 0;
        }
        return 1;
    }

    public function get($param, $type = 'simple', $method='POST', $notset = null)
    {
        if (!method_exists($this->_core, "arrayExtract")) {
            Route::errorLog("Getter class not ready");
            return false;
        }
        $method = strtoupper($method);
        if($_SERVER['REQUEST_METHOD'] != $method) {
            if(Route::getLogLevel()>1)
                Route::errorLog("Method of " . $_SERVER['REQUEST_METHOD'] . " request isn`t. Expected is " . $method);
            return null;
        }
        $type = strtoupper($type);
        switch ($type) {
            case "JSON" :
                if ($this->_data === false)
                    if($this->loadJSON())
                        return null;
                break;
            case "XML" :
                if ($this->_data === false)
                    if($this->loadXML())
                        return null;
                break;
            default :
                switch ($method) {
                    case "GET" :
                        $this->_data = &$_GET;
                        break;
                    case "PUT" :
                    case "DELETE" :
                        if ($this->_data === false)
                            $this->loadData();
                        break;
                    default :
                        $this->_data = &$_POST;
                }
        }
        $result = $this->_core->arrayExtract($this->_data,$param);
        if($result[0])
            return $notset;
        return $result[1];
    }

    private function loadData() {
        $this->_data = Array();
        $data = file_get_contents('php://input');
        $exploded = explode('&', $data);
        foreach($exploded as $pair) {
            $item = explode('=', $pair);
            if(count($item) == 2) {
                $this->_data[urldecode($item[0])] = urldecode($item[1]);
            }
        }
    }

    private function loadJSON() {
        $this->_data = Array();
        $data = file_get_contents('php://input');
        $obj= json_decode($data,true);
        if($obj === NULL) {
            if(Route::getLogLevel()>0)
                Route::errorLog("Error JSON decoding");
            return 1;
        }
        $this->_data = $obj;
        return 0;
    }

    private function loadXML() {
        $this->_data = Array();
        $data = file_get_contents('php://input');
        libxml_use_internal_errors(true);
        $obj= simplexml_load_string($data);
        if($obj === false) {
            if (Route::getLogLevel() > 0)
                Route::errorLog("Error XML decoding");
            return 1;
        }
        $this->_data = $obj;
        return 0;
    }
}
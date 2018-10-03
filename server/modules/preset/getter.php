<?php

class Getter
{
    private $_data = false;
    private $_core = [];
    private $_required;

    function __construct()
    {
        $this->_required = [
            "core" => ["arrayExtract"]
        ];
    }

    /**
     * Return a list of required classes
     *
     * @return array
     */

    public function required() {
        return $this->_required;
    }

    /**
     * Test for ready to work this class
     *
     * @param $required array
     *
     * @return integer - code error
     */

    public function init(&$required) {
        $this->_core = &$required["core"];
        return 0;
    }

    /**
     * Get parameters
     *
     * @param $param string|array
     * @param $type string
     * @param $method string
     * @param $not_set boolean
     *
     * @return mixed
     */

    public function get($param, $type = 'simple', $method='POST', $not_set = null)
    {
        $method = strtoupper($method);
        if($_SERVER['REQUEST_METHOD'] !== $method) {
            if(Route::getLogLevel()>1)
                Route::errorLog("Method of " . $_SERVER['REQUEST_METHOD'] . " request isn`t. Expected is " . $method);
            return null;
        }
        switch (strtoupper($type)) {
            case "JSON" :
                if ($this->_data === false) // one read & many get
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
            return $not_set;
        return $result[1];
    }

    /**
     * Load & explode data from input
     *
     */

    private function loadData() {
        $this->_data = [];
        $data = file_get_contents('php://input');
        $exploded = explode('&', $data);
        foreach($exploded as $pair) {
            $item = explode('=', $pair);
            if(count($item) == 2)
                $this->_data[urldecode($item[0])] = urldecode($item[1]);
            else
                $this->_data[urldecode($item[0])] = null;
        }
    }

    /**
     * Load & decode JSON data
     *
     * @return integer - code error
     */

    private function loadJSON() {
        $this->_data = [];
        $data = file_get_contents('php://input');
        $obj= json_decode($data,true);
        if($obj === NULL) {
            if(Route::getLogLevel() > 0)
                Route::errorLog("Error JSON decoding");
            return 1;
        }
        $this->_data = $obj;
        return 0;
    }

    /**
     * Load & decode XML data
     *
     * @return integer - code error
     */

    private function loadXML() {
        $this->_data = [];
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
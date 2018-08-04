<?php

class Session
{

    private $_ses;
    //private $_db;
    private $_destroy;
    private $_type;
    private $_core;
    private $_required = array();

    function __construct($CONFIG)
    {
        //$this->_db = $DB;
        $this->_destroy = false;
        $this->_type = $CONFIG["include"]["session"]["Type"];
        $this->_required[] = "core";
        $this->_ses = Array();
        switch ($this->_type) {
            default :
                //if (session.use_strict_mode == 0)
                    ini_set('session.use_strict_mode', 1);
                session_start();

                foreach ($_SESSION as $k => $v)
                    $this->_ses[$k] = $v;
                break;
        }
    }

    function __destruct()
    {
        switch ($this->_type) {
            default :
                session_write_close();
                break;
        }
    }

    public function required() {
        return $this->_required;
    }

    public function init($required) {
        if(isset($required["core"])) {
            $this->_core = $required["core"];
            if(method_exists($this->_core,"arrayExtract") && method_exists($this->_core,"arraySet"))
                return 0;
        }
        return 1;
    }

    private function extractData(&$param,$onlyTest = false) {
        $result = $this->_core->arrayExtract($this->_ses,$param,false,$onlyTest);
        if($onlyTest) {
            if ($result[0])
                return false;
            return true;
        }
        if($result[0])
            return null;
        else
            return $result[1];
    }

    function _isSet($index)
    {
        if($this->_destroy)
            throw new Exception('Session terminated');
        return $this->extractData($index,true);
    }

    function _get($index,$notset = null)
    {
        if($this->_destroy)
            throw new Exception('Session terminated');
        $result = $this->extractData($index);
        if(is_null($result))
            return $notset;
        return $result;
    }

    /**
     * Псевдоним для _get
     *
     * @param $index
     * @param null $default
     * @return bool|null
     */
    public function get($index, $default = null)
    {
        return $this->_get($index, $default);
    }

    /**
     * Вытащить роль из сессии
     *
     * @return bool
     */
    public function getRole()
    {
        return $this->_get(['old_session', 'web_prava']);
    }

    function _set($index, $param)
    {
        if($this->_destroy)
            throw new Exception('Session terminated');
        else {
            if ($this->_core->arraySet($this->_ses,$index,false,false,$param)==0) {
                switch ($this->_type) {
                    default :
                        $this->_core->arraySet($_SESSION,$index,false,false,$param);
                        break;
                }
                return 0;
            } else
                return 1;
        }
    }

    function _unSet($index)
    {
        if($this->_destroy)
            throw new Exception('Session terminated');
        else {
                if ($this->_core->arraySet($this->_ses,$index,false,true)==0) {
                    switch ($this->_type) {
                        default :
                            $this->_core->arraySet($_SESSION,$index,false,true);
                            break;
                    }
                    return 0;
                } else
                    return 1;
        }
    }

    function _erase()
    {
        //$this->_ses = array();
        switch ($this->_type) {
            default :
                session_destroy();
                break;
        }
        $this->_destroy = true;
        return 0;
    }

}
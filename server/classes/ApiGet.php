<?php

class ApiGet
{

    private $_conf;

    function __construct($CONFIG)
    {
        $this->_conf = isset($CONFIG["api"]["default"]) ? $CONFIG["api"]["default"] : [];
    }

    function init($param) {

    }
}
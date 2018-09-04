<?php

class ApiGet
{

    private $_conf;
    private $_param;

    function __construct($CONFIG)
    {
        $this->_conf = isset($CONFIG["api"]["default"]) ? $CONFIG["api"]["default"] : [];
    }

    function init(/*$param*/) {
        // $param["DB"]
        // $param["Remainder"]
        if(isset($_GET["cx"]) && is_int($_GET["cx"]))
            $this->_param["CountFigureX"] = $_GET["cx"];
        else
            $this->_param["CountFigureX"] = $this->_conf["CountFigureX"];
        if(isset($_GET["cy"]) && is_int($_GET["cy"]))
            $this->_param["CountFigureY"] = $_GET["cy"];
        else
            $this->_param["CountFigureY"] = $this->_conf["CountFigureY"];
        return $this->_param;
    }
}
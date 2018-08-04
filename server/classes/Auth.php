<?php

class Auth //implements iRoute
{

    private $_session;
    private $_conf;

    function __construct($CONFIG)
    {
        $this->_conf = $CONFIG;
    }

    function init($Classes)
    {
        $this->_session = $Classes["Session"];
        echo "Access deny";
    }
}
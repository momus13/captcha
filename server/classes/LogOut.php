<?php
class Logout {

    private $_session;

    function __construct()
    {
    }

    function init($Classes)
    {
        $this->_session = $Classes["Session"];
        $this->_session->_erase();
        echo '<script>window.location.replace("/");</script>';
    }
}
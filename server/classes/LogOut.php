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
        Route::redirect("/");
    }
}
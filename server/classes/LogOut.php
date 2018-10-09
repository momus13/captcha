<?php

class Logout {

    function __construct()
    {
    }

    function init($Classes)
    {
        $Classes["Session"]->_erase();
        Route::redirect("/");
    }
}
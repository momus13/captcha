<?php

class SafeLoad
{

    private $_symlink_dir;
    private $_safe_dir;
    private $_cbc;
    private $_cbm;
    private $_access_deny;
    private $_ready=false;

    function __construct($CONFIG)
    {
        if(isset($CONFIG["vendors"]) && isset($CONFIG["vendors"]["safeLoad"])) {
            $this->_symlink_dir = Route::normalizeUrl($CONFIG["vendors"]["safeLoad"]["symlinkPath"]);
            $this->_safe_dir = $CONFIG["vendors"]["safeLoad"]["safePath"];
            $this->_access_deny = $CONFIG["global"]["AccessDeny"];
            $this->_cbc = isset($CONFIG["vendors"]["safeLoad"]["callBackController"]) ? $CONFIG["vendors"]["safeLoad"]["callBackController"] : 'not Set';
            $this->_cbm = isset($CONFIG["vendors"]["safeLoad"]["callBackMethod"]) ? $CONFIG["vendors"]["safeLoad"]["callBackMethod"] : 'not Set';
            $this->_ready = true;
        }

    }

    public function main($PARAM)
    {
        if($this->_ready)
        if(count($PARAM["Remainder"])) {
            $propagation = true;
            if(isset($PARAM["Return"][$this->_cbc]) && isset($PARAM["Return"][$this->_cbc][$this->_cbm]))
                $propagation = $PARAM["Return"][$this->_cbc][$this->_cbm]($PARAM["Remainder"]);
            if($propagation) {
                $result = $PARAM["Core"]->safeLink($this->_safe_dir . '/' . implode('/', $PARAM["Remainder"]), $this->_symlink_dir . '/');
                if($result)
                    Route::redirect('/' . $result);
            }
        }
        else
            error_log("Not set safeLoad vendors");
        Route::codeError($this->_access_deny,403);
        return false;
    }
}
<?php

class CoreFunction
{
    private $_path;
    private $_salt;

    function __construct($CONFIG)
    {
        $this->_path = $CONFIG["global"]["Path"];
        $this->_salt = date('jny');
    }

    public function arrayExtract($array, $param, $dot = false, $onlyTest = false)
    {
        if($dot!==false && is_string($param) && is_string($dot))
            $param = explode($dot,$param);
        if(is_string($param))
            $param = [$param];
        if(is_array($array) && is_array($param)) {
            $link =&$array;
            for ($i = 0; $i < count($param); $i++) {
                if(!is_string($param[$i])) {
                    if(Route::getLogLevel()>0)
                        Route::errorLog("arrayExtract: not string parameter");
                    return [$i + 1];
                }
                if (isset($link[$param[$i]]))
                    $link = &$link[$param[$i]];
                else {
                    if (Route::getLogLevel() > 2)
                        Route::errorLog("arrayExtract: not set parameter " . $param[$i]);
                    return [$i + 1];
                }
        }
            if($onlyTest)
                return [0];
            return[0,$link];
        }
        elseif(Route::getLogLevel()>1)
            Route::errorLog("arrayExtract: input parameters is not array");
        return [-1];

    }

    public function arraySet(&$array, $param, $dot = false, $unset = false, $value = '')
    {
        if($dot!==false && is_string($param) && is_string($dot))
            $param = explode($dot,$param);
        if(is_string($param))
            $param = Array($param);
        if(is_array($array) && is_array($param)) {
            $link =&$array;
            $last = Array();
            $j = count($param) - 1;
            for ($i = 0; $i <= $j; $i++) {
                if(!is_string($param[$i])) {
                    if(Route::getLogLevel()>0)
                        Route::errorLog("arraySet: not string parameter");
                    return 2;
                }
                if (isset($link[$param[$i]])) {
                    $last = &$link;
                    $link = &$link[$param[$i]];
                }
                else {
                    if($unset) {
                        if (Route::getLogLevel() > 2)
                            Route::errorLog("arraySet: not set parameter " . $param[$i]);
                        return 3;
                    }
                    if($i  < $j) {
                        $link[$param[$i]] = Array();
                        $link = &$link[$param[$i]];
                    }
                    else {
                        $link[$param[$i]] = $value;
                        return 0;
                    }
                }
            }
            if($unset) {
                $tmp = $last; // delete parameter
                if(isset($tmp[$param[$j]])) {
                    unset($tmp[$param[$j]]);
                    $last = $tmp;
                }
            }
            else
                $link = $value;
            return 0;
        }
        elseif(Route::getLogLevel()>1)
            Route::errorLog("arraySet: input parameters is not array");
        return 1;

    }

    public function safeLink($target, $link) {
        $fileName =  $link . md5($target . $this->_salt);
        if(!file_exists('./' . $fileName)) {
            if (!file_exists($target)) {
                if (Route::getLogLevel() > 2)
                    Route::errorLog("safeLink: file {$target} not exists");
                return false;
            }
            if (!symlink($target,  './' .  $fileName)) {
                if (Route::getLogLevel() > 0)
                    Route::errorLog("safeLink: symlink {$fileName} don`t create");
                return false;
            }
        }
        return $fileName;
    }
}


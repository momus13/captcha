<?php

// RM Version 1.0.1

if (!file_exists("config.php")) { // Load config
    error_log("Bad loading config file");
    http_response_code(500);
    exit;
}

include('config.php');

if (!isset($_CONFIG["global"]) || !isset($_CONFIG["global"]["Path"])) { // Test global settings
    error_log("Bad config file, not set global path");
    http_response_code(500);
    exit;
}

if (!isset($_CONFIG["default"])) // set default parameters for read controllers
    $_CONFIG["default"] = Array();
if (!isset($_CONFIG["default"]["main"]))
    $_CONFIG["default"]["main"] = "main";
if (!isset($_CONFIG["default"]["LetterCase"]))
    $_CONFIG["default"]["LetterCase"] = true;
if (!isset($_CONFIG["default"]["type"]))
    $_CONFIG["default"]["type"] = "class";
if (!isset($_CONFIG["default"]["config"]))
    $_CONFIG["default"]["config"] = true;
if (!isset($_CONFIG["default"]["MaxDepth"]))
    $_CONFIG["default"]["MaxDepth"] = 10;
if (!isset($_CONFIG["default"]["global"]))
    $_CONFIG["default"]["global"] = ["DB", "Session", "Remainder"];
if (!isset($_CONFIG["default"]["html"]))
    $_CONFIG["default"]["html"] = true;
if (!isset($_CONFIG["global"]["Auth"]["Redirect"]))
    $_CONFIG["global"]["Auth"]["Redirect"] = false;
if (!isset($_CONFIG["default"]["lang"]))
    $_CONFIG["default"]["lang"] = "en";
if (!isset($_CONFIG["default"]["ParametersInit"]))
    $_CONFIG["default"]["ParametersInit"] = true;
if (!isset($_CONFIG["global"]["LogLevel"]))
    $_CONFIG["global"]["LogLevel"] = 1;
if (!isset($_CONFIG["global"]["LogPath"]))
    $_CONFIG["global"]["LogPath"] = __DIR__."/../logs/log.log";
if (!isset($_CONFIG["global"]["DateFormatLog"]))
    $_CONFIG["global"]["DateFormatLog"] = "j.n.y G:i:s";



$_console = isset($argv);
if (isset($_CONFIG["global"]["LogStream"]))
    switch (strtolower($_CONFIG["global"]["LogStream"])) {
        case "standard" :
            $_console = false;
            break;
        case "file" :
            $_console = true;
            break;
    }
Route::init($_CONFIG,$_console);

define('_CLASS_DIR',__DIR__."/../server/classes/");
define("_UPLOAD_DIR", __DIR__ . '/../files/');

if(isset($argv))
    if(count($argv)>1) {
        array_shift($argv);
        $contr = array_shift($argv);
        Route::normalizeInclude($contr,$argv);
    } else
        Route::errorLog("Not set controller name");
else {
    $_url = '';
    if (isset($_SERVER["REDIRECT_URL"])) // get URL
        $_url = Route::normalizeUrl($_SERVER["REDIRECT_URL"]);

    $_stopPropagation = false;

    $_urlArray = explode("/", $_url);
    if (isset($_CONFIG["map"]) && is_array($_CONFIG["map"])) { // get map for routing
        foreach ($_CONFIG["map"] as $k => $v) {
            $_MAP = Route::loadMap($v, $k);
            $_stopPropagation = Route::routing($_urlArray, $_MAP, isset($_CONFIG["global"]["Default"]) ? $_CONFIG["global"]["Default"] : '/', $k);
            if ($_stopPropagation)
                break;
        }
    } else
        Route::globalError("Not route map");

    if (!$_stopPropagation)
        Route::codeError($_CONFIG["global"]["NotFound"], 404);
}
// end routing

class Route
{

    private static $_path;
    private static $_conf;
    private static $_preset = Array();
    private static $_depth = 0;
    private static $_first = true;
    private static $_last = false;
    private static $_return = Array();
    private static $_private = false;
    private static $_console = false;

    public static function init($CONFIG,$CONSOLE) {
        self::$_conf = $CONFIG;
        self::$_path = $CONFIG["global"]["Path"];
        if (!isset($CONFIG["global"]["Error"]))
            self::$_conf["global"]["Error"] = '';
        self::$_console = $CONSOLE;
    }

    public static function getLogLevel() {
        return self::$_conf["global"]["LogLevel"];
    }

    private static function normalizeMapInclude($fileName) {
        $fileName = self::normalizeUrl($fileName);
        if (substr($fileName, -4) !== '.php')
            $fileName .= '/index.php';
        if (file_exists(self::$_path . $fileName)) {
            return self::$_path . $fileName;
        }
        if(self::$_conf["global"]["LogLevel"]>0)
            self::errorLog("Bad reading map/controller " . self::$_path . $fileName);
        return '';
    }

    public static function errorLog($text) {
        if(self::$_console) {
            $f = fopen(self::$_conf["global"]["LogPath"], 'a');
            if ($f) {
                fwrite($f, date(self::$_conf["global"]["DateFormatLog"])." {$text}\n");
                fclose($f);
            }
        }
        else
            error_log($text);
    }

    public static function normalizeInclude($contrName, $param = Array()) {
        if(isset($contrName["methods"]) && is_array($contrName["methods"]) && !in_array($_SERVER["REQUEST_METHOD"], $contrName["methods"]))
            return 1;
        $_cache = self::loadCacheControllers();
        if ($_cache === false || !isset($_cache[$contrName])) {
            if (isset(self::$_conf["controllers"]) && is_array(self::$_conf["controllers"])) {
                foreach (self::$_conf["controllers"] as $key => $val) {
                    $_cntrl = self::loadMap($val, $key, false);
                    if(self::loadController($_cntrl, $contrName, $param) !== false) {
                        self::saveCacheControllers($contrName, $val);
                        return 2;
                    }
                }
                self::globalError("Not found controller : {$contrName}");
            } else
                self::globalError("Not set controllers");
        } else {
            $_cntrl = self::loadMap($_cache[$contrName], "Cache", false);
            self::loadController($_cntrl, $contrName, $param);
        }
        return 0;
    }

    private static function loadController($_cntrlArray, $contrName, $param) {
        $_cntrl = $_cntrlArray[0];
        $_glob = Array();
        if (isset($_cntrlArray[1]["html"]) && is_bool($_cntrlArray[1]["html"]))
            $_glob["html"] = $_cntrlArray[1]["html"];
        else
            $_glob["html"] = self::$_conf["default"]["html"];
        if (isset($_cntrlArray[1]["first"]))
            $_glob["first"] = $_cntrlArray[1]["first"];
        elseif (isset(self::$_conf["default"]["first"]))
            $_glob["first"] = self::$_conf["default"]["first"];
        else
            $_glob["first"] = false;
        if (isset($_cntrlArray[1]["last"]))
            $_glob["last"] = $_cntrlArray[1]["last"];
        elseif (isset(self::$_conf["default"]["last"]))
            $_glob["last"] = self::$_conf["default"]["last"];
        else
            $_glob["last"] = false;
        if (isset($_cntrlArray[1]["global"]))
            $_glob["global"] = $_cntrlArray[1]["global"];
        elseif (isset(self::$_conf["default"]["global"]))
            $_glob["global"] = self::$_conf["default"]["global"];
        else
            $_glob["global"] = Array();
        if (isset($_cntrlArray[1]["ParametersInit"]) && is_bool($_cntrlArray[1]["ParametersInit"]))
            $_glob["ParametersInit"] = $_cntrlArray[1]["ParametersInit"];
        else
            $_glob["ParametersInit"] = self::$_conf["default"]["ParametersInit"];
        if (isset($_cntrl[$contrName])) {
            if (isset($_cntrl[$contrName]["file"]) && is_string($_cntrl[$contrName]["file"])) {
                self::$_depth++;
                if (self::$_depth > self::$_conf["default"]["MaxDepth"])
                    self::globalError("Limit max depth : " . self::$_depth . ", call " . $_cntrl[$contrName]["file"]);
                if (isset($_cntrl[$contrName]["type"]) && is_string($_cntrl[$contrName]["type"]))
                    $_t = $_cntrl[$contrName]["type"];
                else
                    $_t = self::$_conf["default"]["type"];
                if (isset($_cntrl[$contrName]["config"]) && is_bool($_cntrl[$contrName]["config"]))
                    $_c = $_cntrl[$contrName]["config"];
                else
                    $_c = self::$_conf["default"]["config"];
                if (isset($_cntrl[$contrName]["global"]) && is_array($_cntrl[$contrName]["global"]))
                    $_g = $_cntrl[$contrName]["global"];
                else
                    $_g = $_glob["global"];
                if (isset($_cntrl[$contrName]["html"]) && is_bool($_cntrl[$contrName]["html"]))
                    $_glob["html"] = $_cntrl[$contrName]["html"];
                if (isset($_cntrl[$contrName]["first"]))
                    $_glob["first"] = $_cntrl[$contrName]["first"];
                if (isset($_cntrl[$contrName]["last"]))
                    $_glob["last"] = $_cntrl[$contrName]["last"];
                if (isset($_cntrl[$contrName]["db"]) && is_array($_cntrl[$contrName]["db"]))
                    $_glob["db"] = $_cntrl[$contrName]["db"];
                else
                    $_glob["db"] = Array();
                if (isset($_cntrl[$contrName]["alter"]) && is_array($_cntrl[$contrName]["alter"]))
                    $_glob["alter"] = $_cntrl[$contrName]["alter"];
                else
                    $_glob["alter"] = Array();
                if (isset($_cntrl[$contrName]["ParametersInit"]) && is_bool($_cntrl[$contrName]["ParametersInit"]))
                    $_glob["ParametersInit"] = $_cntrl[$contrName]["ParametersInit"];
                self::loadControllersBefore($_glob, $_cntrl[$contrName], $param);
                $_d = Array();
                if($_glob["ParametersInit"])
                    while ($_gstep = array_shift($_g)) {
                        $lower_g = strtolower($_gstep);
                        switch ($lower_g) {
                            case "db" :
                                if(count($_glob["db"]))
                                    self::loadDB($_glob["db"],$_d[$_gstep]);
                                else {
                                    if (!isset(self::$_preset["db"]))
                                        self::loadDB(self::$_conf["include"]["db"],self::$_preset["db"]);
                                    $_d[$_gstep] = &self::$_preset["db"];
                                }
                                break;
                            case "alter" : // alter DB
                                if(count($_glob["alter"])) {
                                    $_d[$_gstep] = Array();
                                    foreach ($_glob["alter"] as $k => $v)
                                        self::loadDB($v,$_d[$_gstep][$k]);
                                }
                                break;
                            case "remainder" :
                                $_d[$_gstep] = $param;
                                break;
                            default: // standard preset class
                                self::loadRequired(Array($lower_g));
                                if(isset(self::$_preset[$lower_g]))
                                    $_d[$_gstep] = &self::$_preset[$lower_g];
                        }
                    }
                if ($_t == "class") {
                    if (isset($_cntrl[$contrName]["class"]) && is_string($_cntrl[$contrName]["class"]))
                        $_cn = $_cntrl[$contrName]["class"];
                    else {
                        $_cn = substr(self::normalizeMapInclude($_cntrl[$contrName]["file"]), 0, -4);
                        $fn = strrpos($_cn, "/");
                        if ($fn !== false) {
                            if (substr($_cn, $fn) === 'index') {
                                $_cn = substr($_cn, 0, $fn);
                                $fn = strrpos($_cn, "/");
                                if ($fn !== false)
                                    $_cn = substr($_cn, $fn + 1);
                                elseif (strlen($_cn) === 0)
                                    $_cn = 'index';
                            } else
                                $_cn = substr($_cn, $fn + 1);
                        }
                        if ($_cn == 'index')
                            self::globalError("Not set class for " . $_cntrl[$contrName]["file"]);
                        if (self::$_conf["default"]["LetterCase"])
                            $_cn = strtoupper(substr($_cn, 0, 1)) . substr($_cn, 1);
                    }
                    if (self::normalizeSimpleInclude($_cntrl[$contrName]["file"]) === 0) {
                        $_d["Return"] = self::$_return;
                        try {
                            if (!class_exists($_cn, false))
                                throw new Exception("Not create class \"{$_cn}\"");
                            if ($_c)
                                $_class = new $_cn(self::$_conf);
                            else
                                $_class = new $_cn();
                        } catch (Exception $e) {
                            self::globalError($e->getMessage());
                            return 2;
                        }
                        if (isset($_cntrl[$contrName]["main"]) && is_string($_cntrl[$contrName]["main"]))
                            $_f = $_cntrl[$contrName]["main"];
                        else
                            $_f = self::$_conf["default"]["main"];
                        try {
                            if(!method_exists($_class,$_f))
                                self::globalError("Not set method {$_f} in class \"{$_cn}\"");
                            if($_glob["ParametersInit"])
                                self::$_return[$contrName] = $_class->$_f($_d);
                            else
                                self::$_return[$contrName] = $_class->$_f();
                        } catch (Exception $e) {
                            self::globalError("Error in method {$_f} in class \"{$_cn}\"\n {$e->getMessage()}");
                        }
                        self::loadControllersAfter($_glob, $_cntrl[$contrName], $param);
                        return 0;
                    } else
                        return 1;
                } else {
                    if ($_c)
                        $_d["Config"] = self::$_conf;
                    $_d["Return"] = self::$_return;
                    $GLOBALS = array_merge($GLOBALS, $_d);
                    $_result = self::normalizeSimpleInclude($_cntrl[$contrName]["file"],true); // load controller
                    if (isset($GLOBALS["Return"][$contrName]))
                        self::$_return[$contrName] = $GLOBALS["Return"][$contrName];
                    self::loadControllersAfter($_glob, $_cntrl[$contrName], $param);
                    return $_result;
                }
            } else
                self::globalError("Not set file controller: {$contrName}");
        }
        else
            self::globalError("Not set controller '{$contrName}' in file controllers");
        return false;
    }

    private static function loadControllersBefore($_glob, $_cntrl, $param) {
        if ($_glob["html"] && self::$_first) {
            if(self::$_private && !isset(self::$_preset["session"]))
                self::loadPreSet("session");
            self::$_first = false;
            echo "<!DOCTYPE html><html lang=\"" . self::$_conf["default"]["lang"] . "\">";
            if ($_glob["first"] !== false)
                self::normalizeInclude($_glob["first"], $param);
        }

        if (isset($_cntrl["before"]))
            self::loadControlFromList($_cntrl["before"], $param);
    }

    private static function loadControllersAfter($_glob, $_cntrl, $param) {
        if (isset($_cntrl["after"]))
            self::loadControlFromList($_cntrl["after"], $param);
        self::$_depth--;
        if ($_glob["html"] && self::$_depth === 0 && !self::$_last) {
            self::$_last = true;
            if ($_glob["last"] !== false)
                self::normalizeInclude($_glob["last"], $param);
            echo "</html>";
        }
    }

    private static function saveCacheControllers($controller, $fileName) {
        $_p = (isset(self::$_conf["global"]["cache"]) ? self::$_conf["global"]["cache"] : self::$_path) . "cachecnt.php"; // path to cache file
        if (!file_exists($_p)) {
            $f = fopen($_p, 'w');
            if ($f) {
                fwrite($f, "<?php\n\$_cache=Array(\"{$controller}\"=>\"{$fileName}\");\n");
                fclose($f);
                return true;
            } else
                if(self::$_conf["global"]["LogLevel"]>0)
                    self::errorLog("Don`t create file cache to controllers {$_p}");
        } else {
            $i = 0;
            while ($i++ < 3) {
                $f = fopen($_p, 'a');
                if ($f) {
                    fwrite($f, "\$_cache[\"{$controller}\"]=\"{$fileName}\";\n");
                    fclose($f);
                    return true;
                }
                usleep(5000);
            }
            if(self::$_conf["global"]["LogLevel"]>0)
                self::errorLog("Don`t save in file cache to controllers {$_p}");
        }
        return false;
    }

    private static function loadCacheControllers() {
        $_p = (isset(self::$_conf["global"]["cache"]) ? self::$_conf["global"]["cache"] : self::$_path) . "cachecnt.php"; // path to cache file
        if (file_exists($_p)) {
            try {
                include($_p);
                if (isset($_cache))
                    return $_cache;
            } catch (Exception $e) {
                if(self::$_conf["global"]["LogLevel"]>0)
                    self::errorLog("Error include file cache to controllers {$_p}");
            }
        }
        return false;
    }

    private static function loadControlFromList($list, $param) {
        if (is_array($list))
            foreach ($list as $v)
                self::normalizeInclude($v, $param);
    }

    private static function normalizeSimpleInclude($fileName,$manyload = false) {
        {
            $fileName = self::normalizeUrl($fileName);
            if (substr($fileName, -4) !== '.php')
                $fileName .= '/index.php';
            if (file_exists(self::$_path . $fileName)) {
                if($manyload)
                    include (self::$_path . $fileName);
                else
                    include_once (self::$_path . $fileName);
                return 0;
            }
            if(self::$_conf["global"]["LogLevel"]>0)
                self::errorLog("Bad reading class " . self::$_path . $fileName);
        }
        return 1;
    }

    private static function normalizeEcho($fileName) {
        $fileName = self::normalizeUrl($fileName);
        if (substr($fileName, -4, 4) !== ".htm" && substr($fileName, -5, 5) != ".html")
            $fileName .= '/index.html';
        if (file_exists(self::$_path . $fileName)) {
            $f = fopen(self::$_path . $fileName, 'r');
            if ($f) {
                while (($buffer = fgets($f, 4096)) !== false)
                    echo $buffer;
                if (!feof($f) && self::$_conf["global"]["LogLevel"]>0)
                    self::errorLog("Bad reading html file " . self::$_path . $fileName);
                fclose($f);
            }
            return 0;
        }
        return 1;
    }

    public static function normalizeUrl($url) {
        if (substr($url, 0, 1) === '/')
            $url = substr($url, 1);
        if (substr($url, -1, 1) === '/')
            $url = substr($url, 0, -1);
        return $url;
    }

    public static function globalError($txt = "Anonymous error", $err = null) {
        if (is_null($err))
            $err = self::$_conf["global"]["Error"];
        if (strlen($err))
            self::normalizeEcho($err);
        http_response_code(500);
        self::errorLog($txt);
        exit;
    }

    public static function codeError($file, $code) {
        if (isset($file))
            self::normalizeEcho($file);
        http_response_code($code);
        exit;
    }

    public static function redirect($url) {
        header('Location: ' . $url, true, 302);
        exit;
    }

    public static function routing($urlArray, $map, $redirectError, $private) {
        $_tmpUrl = array_shift($urlArray);
        if ($_tmpUrl === null)
            $_tmpUrl = '';
        foreach ($map as $link => $route) {
            if (self::normalizeUrl($link) == $_tmpUrl) {
                if (is_array($route)) {
                    if (isset($route["map"]) && is_bool($route["map"]) && $route["map"] && isset($route["file"]) && is_string($route["file"])) { // loading next map
                        return self::routing($urlArray, self::loadMap($route["file"], $_tmpUrl), $redirectError, $private);
                    } else
                        return self::routing($urlArray, $route, $redirectError, $private);
                }
                if (is_string($route)) {
                    if ($private === 'private')
                        self::$_private = true;
                    if (self::$_private && isset(self::$_conf["global"]["Login"])) {
                        if (!isset(self::$_preset["session"]))
                            self::loadPreSet("session");
                        $next = true;
                        if (!self::$_preset["session"]->_isset(self::$_conf["global"]["Login"])) { // Not auth in private mode
                            if (isset(self::$_conf["global"]["Auth"]["Controller"])) {
                                if (self::normalizeInclude(self::$_conf["global"]["Auth"]["Controller"], $urlArray) === 0) {
                                    if (self::$_return[self::$_conf["global"]["Auth"]["Controller"]] === true) {
                                        $next = false;
                                        if(!self::$_preset["session"]->_isset(self::$_conf["global"]["Login"]))
                                            self::$_preset["session"]->_set(self::$_conf["global"]["Login"],"Success, not set login");
                                    }
                                } else
                                    self::globalError("Not ready authorize controller");
                            }
                            if ($next) {
                                if (isset(self::$_conf["global"]["Auth"]["Redirect"]) && self::$_conf["global"]["Auth"]["Redirect"] && isset(self::$_conf["global"]["Auth"]["Url"]))
                                    self::redirect(self::$_conf["global"]["Auth"]["Url"]);
                                else
                                    self::codeError(self::$_conf["global"]["AccessDeny"], 403);
                            }
                            elseif(isset(self::$_conf["global"]["Auth"]["SucsUrl"]) && self::$_conf["global"]["Auth"]["SucsUrl"]) {
                                $_req=$_SERVER["REQUEST_URI"];
                                self::redirect(substr($_req,0,strpos($_req,"?")));
                            }
                        }
                    }
                    if (self::normalizeInclude($route, $urlArray) === 0)
                        return true;
                    else {
                        if(self::$_conf["global"]["LogLevel"]>1)
                            self::errorLog("Bad routing \"{$link}\" {$route}");
                        self::redirect($redirectError);
                    }
                }
            }
        }
        return false;
    }

    public static function loadMap($file, $map, $m = true) {
        $tmp = self::normalizeMapInclude($file);
        if (strlen($tmp)) {
            include($tmp);
            if ($m) {
                if (isset($_MAP) && is_array($_MAP))
                    return $_MAP;
                else
                    self::globalError("Bad map routing \"{$map}\"");
            } else {
                if (isset($_CNTRL) && is_array($_CNTRL)) {
                    if (isset($_GENL) && is_array($_GENL))
                        return Array($_CNTRL, $_GENL);
                    else
                        return Array($_CNTRL, Array());
                } else
                    self::globalError("Bad controller routing \"{$map}\"");
            }
        } else
            self::globalError("Not load \"{$map}\" " . ($m ? "map" : "controller") . " routing");
        return false;
    }

    private static function loadDB(&$_conf,&$_db) {
        if (isset($_conf) && isset($_conf["Class"])  && isset($_conf["Path"]) && !isset($_db))
            if (self::normalizeSimpleInclude($_conf["Path"]) === 0) // Include database
                try {
                    $cl = $_conf["Class"];
                    $_db = new $cl($_conf, self::$_path . self::normalizeUrl($_conf["Path"]) . "/");
                } catch (Exception $e) {
                    self::globalError("Not possible create class \"{$cl}\" in preset DataBase \n{$e->getMessage()}");
                }
        if (!isset($_db))
            self::globalError("Not found class database");
        elseif(isset($_conf["Required"]) && isset($_conf["Init"]))
            self::setRequired($_db,$_conf["Required"],$_conf["Init"]);
    }

    private static function loadPreSet($preset) {
        if (isset(self::$_conf["include"]) && isset(self::$_conf["include"][$preset]) && isset(self::$_conf["include"][$preset]["Class"]) && isset(self::$_conf["include"][$preset]["File"]) && !isset(self::$_preset[$preset]))
            if (self::normalizeSimpleInclude(self::$_conf["include"][$preset]["File"]) === 0) { // Include output
                $cl = self::$_conf["include"][$preset]["Class"];
                try {
                    if(!isset(self::$_conf["include"][$preset]["Config"]) || self::$_conf["include"][$preset]["Config"])
                        self::$_preset[$preset] = new $cl(self::$_conf);
                    else
                        self::$_preset[$preset] = new $cl();
                } catch (Exception $e) {
                    self::globalError("Not possible create class \"{$cl}\" in preset {$preset}\n{$e->getMessage()}");
                }
            }
        if (!isset(self::$_preset[$preset]))
            self::globalError("Not found or not set path to preset class ".$preset);
        elseif(isset(self::$_conf["include"][$preset]["Required"]) && isset(self::$_conf["include"][$preset]["Init"]))
            self::setRequired(self::$_preset[$preset],self::$_conf["include"][$preset]["Required"],self::$_conf["include"][$preset]["Init"]);
    }

    private static function loadRequired($required) {
        foreach ($required as $r) {
            $r=strtolower($r);
            if(isset(self::$_conf["include"][$r])) {
                if (!isset(self::$_preset[$r])) {
                    if ($r === 'db') // database not standard loaded
                        self::loadDB(self::$_conf["include"]["db"], self::$_preset["db"]);
                    else
                        self::loadPreSet($r);
                }
            }
            else {
                self::globalError("Not set preset class \"{$r}\"");
            }
        }
        return true;
    }

    private static function setRequired(&$class, $required, $init) {
        try {
            if(!method_exists($class,$required))
                self::globalError("Not set method {$required} in class \"{$class}\"");
            $_r = $class->$required();
        }
        catch(Exception $e) {
            self::globalError("Error in method {$required}\n {$e->getMessage()}");
        }
        if(count($_r)) {
            self::loadRequired(array_keys($_r));
            $required = Array();
            foreach ($_r as $r => $value) {
                $required[$r] = &self::$_preset[strtolower($r)];
                foreach ($value as $method)
                    if(!method_exists($required[$r], $method))
                        self::globalError("Class {$r} not resolved method {$method}");
            }
            $err = $class->$init($required);
            if($err)
                self::globalError("Class not ready, error: {$err}");
        }
        return true;
    }
}
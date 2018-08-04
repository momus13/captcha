<?php

interface dbConnect {
    public function connect();
    public function disconnect();
    public function execute($sql_array,$count_rows,$parametrs);
}

class DbConnection
{
    private $_type;
    private $_con_ar = array();
    private $_db=false;
    private $_ready=false;
    private $_required = array();

    function __construct($CONSTR,$DBPATH)
    {
        $TYPE = "pg";
        if(is_array($CONSTR)) {
            if(isset($CONSTR["Host"]))
                $this->_con_ar["Host"] = $CONSTR["Host"];
            if(isset($CONSTR["Port"]))
                $this->_con_ar["Port"] = $CONSTR["Port"];
            if(isset($CONSTR["User"]))
                $this->_con_ar["User"] = $CONSTR["User"];
            if(isset($CONSTR["Pass"]))
                $this->_con_ar["Password"] = $CONSTR["Pass"];
            if(isset($CONSTR["DB"]))
                $this->_con_ar["DB"] = $CONSTR["DB"];
            if(isset($CONSTR["Schem"]))
                $this->_con_ar["Schema"] = $CONSTR["Schem"];
            if(isset($CONSTR["Opt"]))
                $this->_con_ar["Options"] = $CONSTR["Opt"];
            if(isset($CONSTR["Type"]))
                $TYPE = $CONSTR["Type"];
            if(isset($CONSTR["Url"]))
                $this->_con_ar["Url"] = $CONSTR["Url"];
        }
        else
            throw new Exception('Not DB paramets');

        switch ($TYPE) {
            case "pg" :
                $this->_type = 0;
                break;
            case "my" :
                $this->_type = 1;
                break;
            case "wa" :
                $this->_type = 10;
                break;
            case 'Mock':
                $this->_type = 11;
                break;
            default :
                throw new Exception('Unsupported DB type');
        }
        switch ($this->_type) {
            case 0 :
                $param = Array();
                if(isset($this->_con_ar["Schema"]))
                    $param["schem"] = $this->_con_ar["Schema"];
                include($DBPATH."pg.php");
                $this->_db = new PG((isset($this->_con_ar["Host"]) ? "host=".$this->_con_ar["Host"] : "") .
                    (isset($this->_con_ar["Port"]) ? " port=".$this->_con_ar["Port"] : "") .
                    (isset($this->_con_ar["DB"]) ? " dbname=".$this->_con_ar["DB"] : "") .
                    (isset($this->_con_ar["User"]) ? " user=".$this->_con_ar["User"] : "") .
                    (isset($this->_con_ar["Password"]) ? " password=".$this->_con_ar["Password"] : "") .
                    (isset($this->_con_ar["Options"]) ? " options='".$this->_con_ar["Options"]."'" : ""), $param);
                $this->_ready = true;
                break;
            case 1 :
                $param = Array();
                $param[] = isset($this->_con_ar["Host"]) ? $this->_con_ar["Host"] : "";
                $param[] = isset($this->_con_ar["User"]) ? $this->_con_ar["User"] : "";
                $param[] = isset($this->_con_ar["Password"]) ? $this->_con_ar["Password"] : "";
                $param[] = isset($this->_con_ar["DB"]) ? $this->_con_ar["DB"] : "";
                $param[] = isset($this->_con_ar["Port"]) ? $this->_con_ar["Port"] : "3306";
                include($DBPATH."mysql.php");
                $this->_db = new MySQL($param);
                $this->_ready = true;
                break;
            case 10 :
                $param = Array();
                $param[] = isset($this->_con_ar["Url"]) ? $this->_con_ar["Url"] : "";
                $param[] = isset($this->_con_ar["User"]) ? $this->_con_ar["User"] : "";
                $param[] = isset($this->_con_ar["Password"]) ? $this->_con_ar["Password"] : "";
                $param[] = isset($this->_con_ar["ConnectTimeout"]) ? $this->_con_ar["ConnectTimeout"] : 60;
                $param[] = isset($this->_con_ar["Timeout"]) ? $this->_con_ar["Timeout"] : 180;
                $param[] = isset($this->_con_ar["Post"]) ? $this->_con_ar["Post"] : true;
                $param[] = isset($this->_con_ar["SSLVerifypper"]) ? $this->_con_ar["SSLVerifypper"] : false;
                $param[] = isset($this->_con_ar["Salt"]) ? $this->_con_ar["Salt"] : "TODO";
                include($DBPATH."webapi.php");
                $this->_db = new WebApi($param);
                $this->_required[] = "Output";
                break;
            case 11:
                include ($DBPATH . 'Mock.php');
                $this->_db = new Mock([]);
                break;
        }
    }

    function __destruct()
    {
        if($this->_db!==false)
            $this->_db->disconnect();
    }

    public function required() {
        return $this->_required;
    }

    public function init($required) {
        if($this->_db!==false) {
            $not_ready = $this->_db->init($required);
            if($not_ready)
                return $not_ready;
            else
                $this->_ready = true;
            return 0;
        }
        return 1;
    }

    public function execute($sql) {
        if($this->_ready) {
            if ($this->_db !== false) {
                if (isset($sql["text"]) && is_string($sql["text"])) {
                    if (isset($sql["name"]) && is_array($sql["name"]))
                        for ($i = 0; $i < count($sql["name"]); $i++)
                            if ($this->_type == 0)
                                $sql["text"] = str_replace("%N" . ($i + 1) . "%", "\"" . $sql["name"][$i] . "\"", $sql["text"]); // for postgres
                            else
                                $sql["text"] = str_replace("%N" . ($i + 1) . "%", "" . $sql["name"][$i] . "", $sql["text"]);
                    $rows = 0;
                    if (isset($sql["count"]) && is_int($sql["count"]))
                        $rows = $sql["count"];
                    if (isset($sql["param"]) && is_array($sql["param"]))
                        $result = $this->_db->execute($sql["text"], $rows, $sql["param"]);
                    else
                        $result = $this->_db->execute($sql["text"], $rows);
                    if ($result[0]) {
                        if ($rows)
                            return $result[1];
                        else
                            return true;
                    }
                } elseif (isset($sql) && is_string($sql)) {
                    $result = $this->_db->execute($sql);
                    if ($result[0] && count($result[1]))
                        return $result[1];
                    return $result[0];
                } else
                    Route::errorLog("Not sql text");
            } else
                Route::errorLog("Not create DB class");
        }
        else
            Route::errorLog("DB class not ready");
        return false;
    }
}
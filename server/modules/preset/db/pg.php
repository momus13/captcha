<?php

class PG implements dbConnect
{
    private $_con_string;
    private $_schem = '';
    private $_connection=false;

    function __construct($CONSTR,$_adv = Array())
    {
        $this->_con_string = $CONSTR;
        if(isset($_adv["schem"]))
            $this->_schem = $_adv["schem"];
    }

    public function connect()
    {
        try {
            if($this->_connection = pg_connect($this->_con_string)) {
                if (strlen($this->_schem))
                    if (!$this->execute("SET search_path = \"{$this->_schem}\", pg_catalog;", 0))
                        Route::errorLog("Don't set default schemas: {$this->_schem}");
                return true;
            }
            else {
                Route::errorLog("Don`t connect to DB\nConnection sting: {$this->_con_string}" );
                return false;
            }
        }
        catch (Exception $e) {
            Route::errorLog("Don`t connect to DB\nConnection sting: {$this->_con_string}\n{$e->getMessage()}");
            $this->_connection = false;
            return false;
        }
    }

    public function disconnect()
    {
        if($this->_connection!==false)
            return pg_close($this->_connection);
    }

    public function execute($sql,$count_rows = 0,$param = Array())
    {
        if($this->_connection===false)
            $this->connect();
        if($this->_connection!==false) {
            if(is_array($param) && count($param))
                $result = pg_query_params($this->_connection,$sql,$param);
            else {
                $result = pg_query($this->_connection, $sql);
                $count_rows = -1;
            }
            if($result!==false) {
                $i=0;
                $rows = array();
                while (($count_rows<0 || $i++<$count_rows) && $row = pg_fetch_assoc($result))
                    $rows[] = $row;
                return [true,$rows];
            }
            else {
                Route::errorLog("Error in sql: {$sql}\n" . pg_last_error());
                return [false];
            }
        }
        else {
            return [false];
        }
    }

}
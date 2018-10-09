<?php

class MySQL implements dbConnect
{
    private $_con_string;
    private $_connection = false;

    function __construct($CONSTR)
    {
        $this->_con_string = $CONSTR;
    }

    public function connect()
    {
        try {
            $this->_connection = mysqli_connect($this->_con_string[0], $this->_con_string[1], $this->_con_string[2], $this->_con_string[3], $this->_con_string[4]);
            if (!$this->_connection) {
                Route::errorLog("Don`t connect to DB\nConnection sting: {$this->_con_string}");
                return false;
            }
            return true;
        } catch (Exception $e) {
            Route::errorLog("Don`t connect to DB\nConnection sting: {$this->_con_string}\n{$e->getMessage()}");
            $this->_connection = false;
            return false;
        }
    }

    public function disconnect()
    {
        if ($this->_connection !== false)
            return mysqli_close($this->_connection);
    }

    public function execute($sql, $count_rows = 0, $param = Array())
    {
        if ($this->_connection === false)
            $this->connect();
        if ($this->_connection !== false) {
            if (is_array($param) && count($param)) {
                $p = [];
                $p[] = "";
                foreach ($param as $item) {
                    if (is_float($item))
                        $p[0] .= "d";
                    elseif (is_int($item))
                        $p[0] .= "i";
                    else
                        $p[0] .= "s";
                    $p[] = &$item;
                }
                $result = $this->mysqli_prepared_query($this->_connection, $sql, $p, $count_rows);
                if ($result === false) {
                    Route::errorLog("Error in sql: " . $sql . "\n" . mysqli_error($this->_connection));
                    return [false];
                }
                return [true, $result];
            } else {
                $r = mysqli_query($this->_connection, $sql);
                if (!$r) {
                    Route::errorLog("Error in sql: " . $sql . "\n" . mysqli_error($this->_connection));
                    return [false];
                }
                $n = mysqli_num_rows($r);
                $result = array();
                if ($n > 0)
                    while ($row = $r->fetch_object())
                        $result[] = $row;
                $r->close();
            }
            return [true, $result];
        } else {
            return [false];
        }
    }

    private function mysqli_prepared_query($link, $sql, $params, $limit = -1)
    {
        if ($stmt = mysqli_prepare($link, $sql)) {
            $bindParamsMethod = new ReflectionMethod('mysqli_stmt', 'bind_param');
            $bindParamsMethod->invokeArgs($stmt, $params);
            $queryResult = array();
            if (mysqli_stmt_execute($stmt)) {
                $resultMetaData = mysqli_stmt_result_metadata($stmt);
                if ($resultMetaData) {
                    $stmtRow = array();
                    $rowReferences = array();
                    while ($field = mysqli_fetch_field($resultMetaData))
                        $rowReferences[] = &$stmtRow[$field->name];
                    mysqli_free_result($resultMetaData);
                    $bindResultMethod = new ReflectionMethod('mysqli_stmt', 'bind_result');
                    $bindResultMethod->invokeArgs($stmt, $rowReferences);
                    $i = 0;
                    while (mysqli_stmt_fetch($stmt) && ($limit < 0 || $i++ < $limit)) {
                        $row = array();
                        foreach ($stmtRow as $key => $value)
                            $row[$key] = $value;
                        $queryResult[] = $row;
                    }
                    mysqli_stmt_free_result($stmt);
                } else
                    $queryResult = mysqli_stmt_affected_rows($stmt);
            } else
                $queryResult = false;
            mysqli_stmt_close($stmt);
            return $queryResult;
        } else
            return false;
    }

}
<?php

class Output
{
    /**
     * @var string язык
     */
    private $_lang;

    /**
     * @var string путь к папке
     */
    private $_path;

    /**
     * @var bool|array
     */
    private $_error = false;

    /**
     * @var bool|array
     */

    private $_file = false;

    /**
     * Output constructor.
     *
     * @param array $CONFIG конфиг
     */
    function __construct($CONFIG)
    {
        $this->_lang = $CONFIG["default"]["lang"];
        $this->_path = $CONFIG["include"]["output"]["Path"];
    }

    /**
     * @param array $data данные
     * @param string $type тип ответа
     * @param int $error код ошибки
     * @param string $err_text текст ошибки для записи в логи
     *
     * @return array|int|string
     */

    public function print_t($data, $type = 'JSON', $error = 0, $err_text = "")
    {
        return $this->_print($data, $error, $err_text, $type);
    }

    /**
     * @param array $data данные
     * @param int $error код ошибки
     * @param string $err_text текст ошибки для записи в логи
     * @param string $type тип ответа
     *
     * @return array|int|string
     */

    public function print_e($data, $type = 'JSON', $error = 0, $err_text = "")
    {
        return $this->_print($data, $error, $err_text, $type);
    }

    /**
     * @param array $data
     * @param int|bool $error
     * @param string $err_text
     * @param string $type
     *
     * @return mixed
     */

    private function _print($data, $error, $err_text , $type)
    {
        if (!is_array($data))
            return 1;
        if (!is_int($error) && !is_bool($error)) {
            if (Route::getLogLevel() > 0)
                Route::errorLog("Not standard error type: " . $error);
            $error = 0;
        }
        $send = Array();
        if(!(is_bool($error) && $error === false)) {
            $send["data"] = $data;
            $send["error"] = Array("code" => $error);
            if ($error) {
                if (Route::getLogLevel() > 0) {
                    if (count($err_text) === 0)
                        $err_text = "Not set user error text for Err_code=" . $error;
                    Route::errorLog("User error : " . $err_text);
                }
                if ($this->_file === false) {
                    if (file_exists($this->_path . "/lang/" . $this->_lang . ".php")) { // loading list of message error, for send to users
                        include($this->_path . "/lang/" . $this->_lang . ".php");
                        $this->_file = getMessageList();
                    } else {
                        if (Route::getLogLevel() > 1)
                            Route::errorLog("Don`t exist file containing a language : " . $this->_path . "/lang/" . $this->_lang . ".php");
                        $send["error"]["message"] = "Sorry, your language is missing";
                    }
                }
                if ($this->_file !== false)
                    if (isset($this->_file[$error]))
                        $send["error"]["message"] = $this->_file[$error];
                    else {
                        if (Route::getLogLevel() > 2)
                            Route::errorLog("Error code is not set : " . $error . " for lang " . $this->_path);
                        $send["error"]["message"] = "Sorry, description for your language is missing";
                    }
            }
        }
        else
            $send = $data;

        switch (strtoupper($type)) {
            case "JSON" :
                header("Content-Type: application/json;charset=utf-8");
                echo json_encode($send);
                return 0;
                break;
            case "XML" :
                header("Content-Type: application/xml;charset=utf-8");
                echo xmlrpc_encode($send);
                return 0;
                break;
            case "JSON_TO_STRING" :
                return json_encode($send);
                break;
            default :
                return $send;
        }
    }
}
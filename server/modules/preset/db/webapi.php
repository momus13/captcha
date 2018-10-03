<?php

class WebApi implements dbConnect
{
    private $_url;
    private $_login;
    private $_pass;
    private $_con_t;
    private $_timeout;
    private $_post;
    private $_ssl_v;
    private $_salt;
    private $token=false;
    private $_out;

    function __construct($CONSTR)
    {
        $this->_url = $CONSTR[0];
        $this->_login = $CONSTR[1];
        $this->_pass = $CONSTR[2];
        $this->_con_t = $CONSTR[3];
        $this->_timeout = $CONSTR[4];
        $this->_post = $CONSTR[5];
        $this->_ssl_v = $CONSTR[6];
        $this->_salt = $CONSTR[7];
    }

    public function connect()
    {
        $post_data = "gettoken=1";
        if (strlen($this->_login))
            $post_data .= "&login=" . $this->_login;
        if (strlen($this->_pass))
            $post_data .= "&pass=" . $this->_pass;
        $result = $this->curl($post_data,false);
        if($result!==false && isset($result['token'])) {
            $this->token = sha1($this->_salt.$result['token']);
            return true;
        }
        return false;
    }

    public function disconnect()
    {
        return true;
    }

    public function required() {
        return [
            "Output" => ["print_t"]
        ];
    }

    public function init(&$required) {
        $this->_out = &$required["Output"];
        return 0;
    }

    public function execute($post_data, $count_rows = 0, $param = Array())
    {
        if($this->token!==false || $this->connect()) {

            for ($i = 0; $i < count($param); $i++)
                $post_data = str_replace("$" . ($i + 1), "'" . $param[$i] . "'", $post_data);

            $result = $this->curl($this->_out->print_t(Array('token' => $this->token, 'query' => $post_data),0,'', 'JSON_TO_STRING'));

            if ($result === false)
                return [false];
            return [true, $result];
        }
        return [false];
    }

    private function curl($data, $json = true)
    {
        if (strlen($this->_url)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->_ssl_v);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $this->_url);
            curl_setopt($ch, CURLOPT_POST, $this->_post);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_con_t);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            if($json)
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8','Content-Length: ' . strlen($data)));
            $result = json_decode(curl_exec($ch), TRUE);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                Route::errorLog("Error in url: {$this->_url}\n   HTTPcode:{$httpCode}\n" . curl_errno($ch) . " : " . curl_error($ch));
                curl_close($ch);
                return false;
            }
            curl_close($ch);
            return $result;
        } else
            return false;
    }
}
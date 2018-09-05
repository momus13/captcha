<?php

class ApiGet
{

    private $_conf;
    private $_param;
    private $_path;
    private $_result;

    function __construct($CONFIG)
    {
        $this->_conf  = isset($CONFIG["api"]["default"]) ? $CONFIG["api"]["default"] : [];
        $this->_param = isset($CONFIG["api"]["parameters"]) ? $CONFIG["api"]["parameters"] : [];
        $this->_path  = $CONFIG["global"]["Path"];
    }

    public function init(/*$param*/) {
        // $param["DB"]
        // $param["Remainder"]
        if(isset($_GET["cx"]) && is_int($_GET["cx"]) && $_GET["cx"] > 2 && $_GET["cx"] <= $this->_conf["MaxFigureX"])
            $this->_result["CountFigureX"] = $_GET["cx"];
        else
            $this->_result["CountFigureX"] = $this->_conf["CountFigureX"];
        if(isset($_GET["cy"]) && is_int($_GET["cy"]) && $_GET["cy"] > 2 && $_GET["cy"] <= $this->_conf["MaxFigureY"])
            $this->_result["CountFigureY"] = $_GET["cy"];
        else
            $this->_result["CountFigureY"] = $this->_conf["CountFigureY"];
        if(isset($_GET["mf"]) && is_int($_GET["mf"]) && $_GET["mf"] > -1
            && $_GET["cy"] < ($this->_result["CountFigureX"]*$this->_result["CountFigureY"]/2))
            $this->_result["MaxExtraFigure"] = $_GET["mef"];
        else
            $this->_result["MaxExtraFigure"] = $this->_conf["MaxExtraFigure"];
        if(isset($_GET["co"]) && is_int($_GET["co"]) && $_GET["co"] > 0 && $_GET["co"] <= $this->_conf["MaxCorrect"])
            $this->_result["Correct"] = $_GET["co"];
        else
            $this->_result["Correct"] = (int) ($this->_conf["MaxCorrect"] / 3);
        if(isset($_GET["bs"]) && is_int($_GET["bs"]) && $_GET["bs"] > $this->_conf["MinBlockPixel"]
            && $_GET["bs"] <= $this->_conf["MaxBlockPixel"])
            $this->_result["BlockSize"] = $_GET["bs"];
        else
            $this->_result["BlockSize"] = (int) (($this->_conf["MinBlockPixel"] + $this->_conf["MaxBlockPixel"]) / 2);
        if(isset($_GET["qu"]) && is_int($_GET["qu"]) && $_GET["qu"] > $this->_conf["MinQuality"]
            && $_GET["qu"] <= $this->_conf["MaxQuality"])
            $this->_result["Quality"] = $_GET["bs"];
        else
            $this->_result["Quality"] = (int) (($this->_conf["MinQuality"] + $this->_conf["MaxQuality"]) / 2);
        if(isset($_GET["mm"]) && is_bool($_GET["mm"]))
            $this->_result["MayBeMinus"] = $_GET["mm"];
        else
            $this->_result["MayBeMinus"] = $this->_conf["MayBeMinus"];
        if(isset($_GET["na"]) && is_int($_GET["na"]) && $_GET["na"] >= $this->_conf["MinAnswer"]
        && $_GET["na"] < $this->_conf["MaxAnswer"])
            $this->_result["MinAnswer"] = $_GET["na"];
        else
            $this->_result["MinAnswer"] = $this->_conf["MinAnswer"];
        if(isset($_GET["xa"]) && is_int($_GET["xa"]) && $_GET["xa"] > $this->_conf["MinAnswer"]
            && $_GET["xa"] <= $this->_conf["MaxAnswer"])
            $this->_result["MaxAnswer"] = $_GET["xa"];
        else
            $this->_result["MaxAnswer"] = $this->_conf["MaxAnswer"];
        if(isset($_GET["bc"]) && is_string($_GET["bc"])) {
            $a = explode(',',$_GET["bc"]);
            if(count($a) === 3) {
                $this->_result["BodyColor"][] = $this->setColor($a[0]);
                $this->_result["BodyColor"][] = $this->setColor($a[1]);
                $this->_result["BodyColor"][] = $this->setColor($a[2]);
            }
            else
                $this->_result["BodyColor"] = $this->_conf["BodyColor"];
        }
        else
            $this->_result["BodyColor"] = $this->_conf["BodyColor"];
        if(isset($_GET["bf"]) && is_bool($_GET["bf"]))
            $this->_result["FileBody"] = $this->_path . $this->_param["PathFon"] . "cats.jpg";
        // поставить файл пользователя
        else
            $this->_result["FileBody"] = $this->_path . $this->_param["PathFon"] . $this->_param["FileBody"];
        if(isset($_GET["mc"]) && is_bool($_GET["mc"]) && $_GET["mc"])
            $this->_result["MyColor"] = 0;
        else
            $this->_result["MyColor"] = 1;
        if(isset($_GET["cl"]) && is_string($_GET["cl"])) {
            $this->_result["Colors"] = array();
            $b = array_keys($this->_param["ColorsList"]);
            foreach (((array)$_GET["cl"]) as $item) {
                $a = strtolower($item);
                if(in_array($a, $b))
                    $this->_result["Colors"][] = $a;
            }
            if(count($this->_result["Colors"]) === 0)
                $this->_result["Colors"] = $this->_param["Colors"];
        }
        else
            $this->_result["Colors"] = $this->_param["Colors"];
        $this->_result["ColorsList"] = $this->_param["ColorsList"];
        return $this->_result;
    }

    private function setColor($color) {
        if(!is_int($color))
            return 255;
        if($color<0)
            return 0;
        if($color>255)
            return 255;
        return $color;
    }
}
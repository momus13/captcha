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

        $this->_result["ColorsList"] = $this->_param["ColorsList"];
        $this->_result["SizesList"] = $this->_param["SizesList"];
        $this->_result["BodiesList"] = $this->_param["BodiesList"];
        $this->_result["SumsList"] = $this->_param["SumsList"];

        if(isset($_GET["cx"]) && is_numeric($_GET["cx"]) && $_GET["cx"] > 2 && $_GET["cx"] <= $this->_conf["MaxFigureX"])
            $this->_result["CountFigureX"] = (int) $_GET["cx"];
        else
            $this->_result["CountFigureX"] = $this->_conf["CountFigureX"];

        if(isset($_GET["cy"]) && is_numeric($_GET["cy"]) && $_GET["cy"] > 2 && $_GET["cy"] <= $this->_conf["MaxFigureY"])
            $this->_result["CountFigureY"] = (int) $_GET["cy"];
        else
            $this->_result["CountFigureY"] = $this->_conf["CountFigureY"];

        if(isset($_GET["mf"]) && is_numeric($_GET["mf"]) && $_GET["mf"] > -1
            && $_GET["cy"] < ($this->_result["CountFigureX"]*$this->_result["CountFigureY"]/2))
            $this->_result["MaxExtraFigure"] = (int) $_GET["mef"];
        else
            $this->_result["MaxExtraFigure"] = $this->_conf["MaxExtraFigure"];

        if(isset($_GET["co"]) && is_numeric($_GET["co"]) && $_GET["co"] > 0 && $_GET["co"] <= $this->_conf["MaxCorrect"])
            $this->_result["Correct"] = (int) $_GET["co"];
        else
            $this->_result["Correct"] = ($this->_conf["MaxCorrect"] / 3);

        if(isset($_GET["bs"]) && is_numeric($_GET["bs"]) && $_GET["bs"] > $this->_conf["MinBlockPixel"]
            && $_GET["bs"] <= $this->_conf["MaxBlockPixel"])
            $this->_result["BlockSize"] = (int) $_GET["bs"];
        else
            $this->_result["BlockSize"] = (int) (($this->_conf["MinBlockPixel"] + $this->_conf["MaxBlockPixel"]) / 2);

        if(isset($_GET["qu"]) && is_numeric($_GET["qu"]) && $_GET["qu"] > $this->_conf["MinQuality"]
            && $_GET["qu"] <= $this->_conf["MaxQuality"])
            $this->_result["Quality"] = (int) $_GET["bs"];
        else
            $this->_result["Quality"] = (int) (($this->_conf["MinQuality"] + $this->_conf["MaxQuality"]) / 2);

        if(isset($_GET["mm"]) && is_bool($_GET["mm"]))
            $this->_result["MayBeMinus"] = $_GET["mm"];
        else
            $this->_result["MayBeMinus"] = $this->_conf["MayBeMinus"];

        if(isset($_GET["na"]) && is_numeric($_GET["na"]) && $_GET["na"] >= $this->_conf["MinAnswer"]
        && $_GET["na"] < $this->_conf["MaxAnswer"])
            $this->_result["MinAnswer"] = (int) $_GET["na"];
        else
            $this->_result["MinAnswer"] = $this->_conf["MinAnswer"];

        if(isset($_GET["xa"]) && is_numeric($_GET["xa"]) && $_GET["xa"] > $this->_conf["MinAnswer"]
            && $_GET["xa"] <= $this->_conf["MaxAnswer"])
            $this->_result["MaxAnswer"] = (int) $_GET["xa"];
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

        if(isset($_GET["cl"]) && is_string($_GET["cl"])) {
            $this->_result["Colors"] = array();
            $b = array_keys($this->_param["ColorsList"]);
            foreach (explode(",",strtolower($_GET["cl"])) as $item) {
                if(in_array($item, $b) && !in_array($item, $this->_result["Colors"]))
                    $this->_result["Colors"][] = $item;
                if($item === "my") {
                    // прочесть и добавить цвет пользователя
                    $this->_result["Colors"][] = "user";
                    $this->_result["ColorsList"]["user"] = '255128255';
                    $this->_result["MyColor"]["user"] = 'custom';
                }
            }
            if(count($this->_result["Colors"]) === 0)
                $this->_result["Colors"] = $this->_conf["Colors"];
        }
        else
            $this->_result["Colors"] = $this->_conf["Colors"];

        if(isset($_GET["sz"]) && is_string($_GET["sz"])) {
            $this->_result["Sizes"] = array();
            $b = array_keys($this->_param["SizesList"]);
            foreach (explode(",",strtolower($_GET["sz"])) as $item) { // may be repeat size
                if(in_array($item, $b) && $this->_result["BlockSize"] - 3 > $this->_param["SizesList"][$item])
                    $this->_result["Sizes"][] = $item;
            }
            if(count($this->_result["Sizes"]) === 0)
                $this->_result["Sizes"] = $this->_conf["Sizes"];
        }
        else
            $this->_result["Sizes"] = $this->_conf["Sizes"];

        if(isset($_GET["bl"]) && is_string($_GET["bl"])) {
            $this->_result["Bodies"] = array();
            $b = array_keys($this->_param["BodiesList"]);
            foreach (explode(",",strtolower($_GET["bl"])) as $item) {
                if(in_array($item, $b) && !in_array($item, $this->_result["Bodies"]))
                    $this->_result["Bodies"][] = $item;
                if($item === "my") {
                    // прочесть и добавить фигуру пользователя
                    $this->_result["Bodies"][] = "user";
                    $this->_result["BodiesList"]["user"] = 1;
                    $this->_result["MyBody"]["user"] = 'custom';
                }
            }
            if(count($this->_result["Bodies"]) === 0)
                $this->_result["Bodies"] = $this->_conf["Bodies"];
        }
        else
            $this->_result["Bodies"] = $this->_conf["Bodies"];

        $this->_result["Lang"] = $this->_path . $this->_param["PathLang"];
        if(isset($_GET["lg"]) && is_string($_GET["lg"])) {
            $a = strtolower($_GET["lg"]);
            if(in_array($a, array_keys($this->_param["LangList"])))
                $this->_result["Lang"] .= $this->_param["LangList"][$a];
            else
                $this->_result["Lang"] .= $this->_param["LangList"][$this->_conf["Lang"]];
        }
        else
            $this->_result["Lang"] .= $this->_param["LangList"][$this->_conf["Lang"]];

        if(isset($_GET["cq"]) && is_numeric($_GET["cq"]) && $_GET["cq"] > 0 && $_GET["cq"] < 4)
            $this->_result["CountQuest"] = (int) $_GET["cq"];
        else
            $this->_result["CountQuest"] = $this->_conf["CountQuest"];

        if(isset($_GET["td"]) && is_string($_GET["td"])) {
            $this->_result["Sums"] = array();
            $b = array_keys($this->_param["SumsList"]);
            foreach (explode(",",strtolower($_GET["td"])) as $item) {
                if(in_array($item, $b) && !in_array($item, $this->_result["Sums"]))
                    $this->_result["Sums"][] = $item;
            }
            if(count($this->_result["Sums"]) === 0)
                $this->_result["Bodies"] = $this->_conf["Sums"];
        }
        else
            $this->_result["Sums"] = $this->_conf["Sums"];

        return $this->_result;
    }

    private function setColor($color) {
        if(!is_numeric($color))
            return 255;
        if($color<0)
            return 0;
        if($color>255)
            return 255;
        return (int) $color;
    }
}
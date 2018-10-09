<?php

class ApiGet
{

    private $_conf;
    private $_param;
    private $_path;

    function __construct($CONFIG)
    {
        $this->_conf  = isset($CONFIG["api"]["default"]) ? $CONFIG["api"]["default"] : [];
        $this->_param = isset($CONFIG["api"]["parameters"]) ? $CONFIG["api"]["parameters"] : [];
        $this->_path  = $CONFIG["global"]["Path"];
    }

    public function init($param) {

        $id = $this->getToken($param["DB"]);
        $path = $this->_path . $this->_param["PathUsers"] . $id . ".php";
        if($id && is_file($path))
            require $path;
        $result["ID"] = $id;

        $result["ColorsList"] = $this->_param["ColorsList"];
        $result["SizesList"] = $this->_param["SizesList"];
        $result["BodiesList"] = $this->_param["BodiesList"];
        $result["SumsList"] = $this->_param["SumsList"];

        if(isset($_GET["cx"]) && is_numeric($_GET["cx"]) && $_GET["cx"] > 2 && $_GET["cx"] <= $this->_conf["MaxFigureX"])
            $result["CountFigureX"] = (int) $_GET["cx"];
        elseif(!isset($result["CountFigureX"]))
            $result["CountFigureX"] = $this->_conf["CountFigureX"];

        if(isset($_GET["cy"]) && is_numeric($_GET["cy"]) && $_GET["cy"] > 2 && $_GET["cy"] <= $this->_conf["MaxFigureY"])
            $result["CountFigureY"] = (int) $_GET["cy"];
        elseif(!isset($result["CountFigureY"]))
            $result["CountFigureY"] = $this->_conf["CountFigureY"];

        if(isset($_GET["mf"]) && is_numeric($_GET["mf"]) && $_GET["mf"] > -1
            && $_GET["cy"] < ($result["CountFigureX"]*$result["CountFigureY"]/2))
            $result["MaxExtraFigure"] = (int) $_GET["mef"];
        elseif(!isset($result["MaxExtraFigure"]))
            $result["MaxExtraFigure"] = $this->_conf["MaxExtraFigure"];

        if(isset($_GET["co"]) && is_numeric($_GET["co"]) && $_GET["co"] > 0 && $_GET["co"] <= $this->_conf["MaxCorrect"])
            $result["Correct"] = (int) $_GET["co"];
        elseif(!isset($result["Correct"]))
            $result["Correct"] = ($this->_conf["MaxCorrect"] / 3);

        if(isset($_GET["bs"]) && is_numeric($_GET["bs"]) && $_GET["bs"] > $this->_conf["MinBlockPixel"]
            && $_GET["bs"] <= $this->_conf["MaxBlockPixel"])
            $result["BlockSize"] = (int) $_GET["bs"];
        elseif(!isset($result["BlockSize"]))
            $result["BlockSize"] = (int) (($this->_conf["MinBlockPixel"] + $this->_conf["MaxBlockPixel"]) / 2);

        if(isset($_GET["qu"]) && is_numeric($_GET["qu"]) && $_GET["qu"] > $this->_conf["MinQuality"]
            && $_GET["qu"] <= $this->_conf["MaxQuality"])
            $result["Quality"] = (int) $_GET["bs"];
        elseif(!isset($result["Quality"]))
            $result["Quality"] = (int) (($this->_conf["MinQuality"] + $this->_conf["MaxQuality"]) / 2);

        if(isset($_GET["mm"]) && is_bool($_GET["mm"]))
            $result["MayBeMinus"] = $_GET["mm"];
        elseif(!isset($result["MayBeMinus"]))
            $result["MayBeMinus"] = $this->_conf["MayBeMinus"];

        if(isset($_GET["mz"]) && is_bool($_GET["mz"]))
            $result["MayBeZero"] = $_GET["mz"];
        elseif(!isset($result["MayBeZero"]))
            $result["MayBeZero"] = $this->_conf["MayBeZero"];

        if(isset($_GET["na"]) && is_numeric($_GET["na"]) && $_GET["na"] >= $this->_conf["MinAnswer"]
        && $_GET["na"] < $this->_conf["MaxAnswer"])
            $result["MinAnswer"] = (int) $_GET["na"];
        elseif(!isset($result["MinAnswer"]))
            $result["MinAnswer"] = $this->_conf["MinAnswer"];

        if(isset($_GET["xa"]) && is_numeric($_GET["xa"]) && $_GET["xa"] > $this->_conf["MinAnswer"]
            && $_GET["xa"] <= $this->_conf["MaxAnswer"])
            $result["MaxAnswer"] = (int) $_GET["xa"];
        elseif(!isset($result["MaxAnswer"]))
            $result["MaxAnswer"] = $this->_conf["MaxAnswer"];

        if(isset($_GET["bc"]) && is_string($_GET["bc"])) {
            $a = explode(',',$_GET["bc"]);
            if(count($a) === 3) {
                $result["BodyColor"][] = $this->setColor($a[0]);
                $result["BodyColor"][] = $this->setColor($a[1]);
                $result["BodyColor"][] = $this->setColor($a[2]);
            }
            else
                $result["BodyColor"] = $this->_conf["BodyColor"];
        }
        elseif(!isset($result["BodyColor"]))
            $result["BodyColor"] = $this->_conf["BodyColor"];

        if(isset($_GET["bf"]) && is_bool($_GET["bf"]) && is_file($this->_path . $this->_param["PathUsers"] . $id . ".jpg"))
            $result["FileBody"] = $this->_path . $this->_param["PathUsers"] . $id . ".jpg";
        elseif(!isset($result["FileBody"]))
            $result["FileBody"] = $this->_path . $this->_param["PathFon"] . $this->_param["FileBody"];

        if(isset($_GET["lg"]) && is_string($_GET["lg"])) {
            $a = strtolower($_GET["lg"]);
            if(in_array($a, array_keys($this->_param["LangList"])))
                $result["Lang"] = $a;
            else
                $result["Lang"] = $this->_conf["Lang"];
        }
        elseif(!isset($result["Lang"]))
            $result["Lang"] = $this->_conf["Lang"];
        $result["PathLang"] = $this->_path . $this->_param["PathLang"] . $this->_param["LangList"][$result["Lang"]];

        if(isset($_GET["cl"]) && is_string($_GET["cl"])) {
            $result["Colors"] = array();
            $b = array_keys($this->_param["ColorsList"]);
            foreach (explode(",",strtolower($_GET["cl"])) as $item) {
                if(in_array($item, $b) && !in_array($item, $result["Colors"]))
                    $result["Colors"][] = $item;
                if($item === "my" && $result["ID"] && isset($result["MyColor"]) && isset($result["MyLang"][$result["Lang"]])) {
                    $result["Colors"][] = "user";
                    $result["ColorsList"]["user"] = $result["MyColor"];
                    $result["Color"] = $result["MyLang"][$result["Lang"]];
                }
            }
            if(count($result["Colors"]) === 0)
                $result["Colors"] = $this->_conf["Colors"];
        }
        elseif(!isset($result["Colors"]))
            $result["Colors"] = $this->_conf["Colors"];

        if(isset($_GET["sz"]) && is_string($_GET["sz"])) {
            $result["Sizes"] = array();
            $b = array_keys($this->_param["SizesList"]);
            foreach (explode(",",strtolower($_GET["sz"])) as $item) { // may be repeat size
                if(in_array($item, $b) && $result["BlockSize"] - 3 > $this->_param["SizesList"][$item])
                    $result["Sizes"][] = $item;
            }
            if(count($result["Sizes"]) === 0)
                $result["Sizes"] = $this->_conf["Sizes"];
        }
        elseif(!isset($result["Sizes"]))
            $result["Sizes"] = $this->_conf["Sizes"];

        if(isset($_GET["bl"]) && is_string($_GET["bl"])) {
            $result["Bodies"] = array();
            $b = array_keys($this->_param["BodiesList"]);
            foreach (explode(",",strtolower($_GET["bl"])) as $item) {
                if(in_array($item, $b) && !in_array($item, $result["Bodies"]))
                    $result["Bodies"][] = $item;
                /*if($item === "my") {
                    // прочесть и добавить фигуру пользователя
                    $result["Bodies"][] = "user";
                    $result["BodiesList"]["user"] = 1;
                    $result["MyBody"]["user"] = 'custom';
                }*/
            }
            if(count($result["Bodies"]) === 0)
                $result["Bodies"] = $this->_conf["Bodies"];
        }
        elseif(!isset($result["Bodies"]))
            $result["Bodies"] = $this->_conf["Bodies"];

        if(isset($_GET["cq"]) && is_numeric($_GET["cq"]) && $_GET["cq"] > 0 && $_GET["cq"] < 4)
            $result["CountQuest"] = (int) $_GET["cq"];
        elseif(!isset($result["CountQuest"]))
            $result["CountQuest"] = $this->_conf["CountQuest"];

        if(isset($_GET["td"]) && is_string($_GET["td"])) {
            $result["Sums"] = array();
            $b = array_keys($this->_param["SumsList"]);
            foreach (explode(",",strtolower($_GET["td"])) as $item) {
                if(in_array($item, $b) && !in_array($item, $result["Sums"]))
                    $result["Sums"][] = $item;
            }
            if(count($result["Sums"]) === 0)
                $result["Bodies"] = $this->_conf["Sums"];
        }
        elseif(!isset($result["Sums"]))
            $result["Sums"] = $this->_conf["Sums"];

        if(isset($_GET["xml"]) && is_bool($_GET["xml"]))
            $result["Echo"] = (bool) $_GET["xml"];

        $result["FileElements"] = $this->_path . $this->_param["PathElement"];
        $result["PathResult"] = $this->_param["PathResult"];

        if($result["ID"] && isset($_GET["save"]))
            $this->saveUser($result,$path,array_keys($this->_conf));

        return $result;
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
    
    public function createColor($param) {
        $id = $this->getToken($param["DB"]);
        if($id) {
            if (isset($_GET["bc"]) && is_string($_GET["bc"])) {
                $a = explode(',', $_GET["bc"]);
                if (count($a) === 3) {
                    $redColor = $this->setColor($a[0]);
                    $greenColor = $this->setColor($a[1]);
                    $blueColor = $this->setColor($a[2]);
                    $c = (string)$redColor . '_' . (string)$greenColor . '_' . (string)$blueColor;
                    $pathBase = $this->_path . $this->_param["PathStamp"];
                    $pathColor = $this->_path . $this->_param["PathElement"] . $c;
                    if (is_dir($pathColor))
                        return 3;
                    if (!mkdir($pathColor, 0760, true))
                        return 4;
                    foreach ($this->_param["SizesList"] as $size => $a)
                        foreach ($this->_param["BodiesList"] as $type => $a)
                            for ($i = 1; $i <= $a; $i++) {
                                $figureName = $size . $type . $i;
                                $handle = fopen($pathBase . $figureName . ".gif", "rb");
                                if ($handle === false)
                                    return 2;
                                $contents = fread($handle, 1024);
                                fclose($handle);
                                $contents[13] = chr($redColor);
                                $contents[14] = chr($greenColor);
                                $contents[15] = chr($blueColor);
                                $d = DIRECTORY_SEPARATOR;
                                $handle = fopen("{$pathColor}{$d}{$figureName}.gif", 'wb');
                                if ($handle === false)
                                    return 3;
                                fwrite($handle, $contents);
                                fclose($handle);
                            }
                    $colors = [];
                    $colors["MyColor"] = $c;
                    foreach (array_keys($this->_param["LangList"]) as $key)
                        if(isset($_GET[$key]) && is_string($_GET[$key]))
                            $colors["MyLang"][$key] = $_GET[$key];
                    $this->saveUserAdd($colors,"{$this->_path}{$this->_param["PathUsers"]}{$id}.php");
                } else
                    return 2;
            }
            return 1;
        }
        return 1000;
    }

    private function getToken($db) {
        if(isset($_GET["token"]) && is_string($_GET["token"]) && strlen($_GET["token"]) === 40) {
            $sql = [
                "text" => "select id from account where `status`>0 and `token`=?",
                "param" => [ $_GET["token"] ],
                "count" => 1
            ];
            $r = $db->execute($sql);
            if(is_array($r))
                return $r[0]["id"];
            return 0;
        }
        return 0;
    }

    private function saveUser($params,$file,$key) {
        $p = [];
        if (file_exists($file)) {
            require $file;
            if (isset($result) && isset($result["MyColor"]) && isset($result["MyLang"])) {
                $p["MyColor"] = $result["MyColor"];
                $p["MyLang"] = $result["MyLang"];
            }
        }
        $f = fopen($file, 'w');
        if ($f) {
            foreach ($params as $k => $v)
                if (in_array($k, $key))
                    $p[$k] = $v;
            $p = var_export($p, true);
            fwrite($f, "<?php \$result={$p};");
            fclose($f);
            return 0;
        } else
            return 1;
    }

    private function saveUserAdd($params,$file) {
        if (file_exists($file)) {
            require $file;
            if (isset($result) && is_array($result))
                foreach ($result as $k => $v)
                    if(!isset($params[$k]))
                        $params[$k] = $v;
            }
        $f = fopen($file, 'w');
        if ($f) {
            $p = var_export($params, true);
            fwrite($f, "<?php \$result={$p};");
            fclose($f);
            return 0;
        } else
            return 1;
    }
}
<?php

class Api {

    function __construct() {
    }

    public function init($parameters) {
//        global $pathRoot,$langs,$type_list,$color_list,$size_list,$sum_list;
//        require_once $pathRoot.'config'.DIRECTORY_SEPARATOR.'default.php';
        $count_figure_x = $parameters["CountFigureX"];
        $count_figure_y = $parameters["CountFigureY"];
        $count_figure_base = $count_figure_x * $count_figure_y;
        $max_extra_figure = $parameters['MaxExtraFigure'];
        $correct = $parameters['Correct'];
        $block_size = $parameters['BlockSize'];
        $quality = $parameters['Quality'];
        $maybe_minus = $parameters['MayBeMinus'];
        $maybe_zero = $parameters['MayBeZero'];
        $min_answer = $parameters['MinAnswer'];
        $max_answer = $parameters['MaxAnswer'];
        $bodyc = $parameters['BodyColor'];

        if(is_string($parameters['bodyf']))
            $bodyf=$parameters['bodyf'];
        $my_color=1;
        if(is_array($parameters['mycolor']))
            $my_color=0;
        if($parameters['colors']===0)
            $parameters['colors'] = $colors;
        $colors = array();
        $col_index = array();
        $this->create_massive($parameters['colors'],$color_list,$colors,$col_index,$my_color);
        if($parameters['lang']>0 && $parameters['lang']<=count($langs))
            $lang=$parameters['lang'];
        if($parameters['count_quest']>0 && $parameters['count_quest']<4)
            $count_quest=$parameters['count_quest'];
        if($parameters['types']==0)
            $parameters['types'] = $bodys;
        $bodys = array();
        $bod_index = array();
        $this->create_massive($parameters['types'],$type_list,$bodys,$bod_index,2,false);
        $devs = array();
        $dev_index = array();
        $this->create_massive($parameters['sum'],$sum_list,$devs,$dev_index,1);

        require_once $pathRoot.'config'.DIRECTORY_SEPARATOR.$langs[--$lang].'.php';
        if(!$my_color)
            foreach($parameters['mycolor'] as $index_color => $name_color)
            {
                $col_index[] = count($acolor);
                $colors[] = $index_color;
                $acolor[]=$name_color;
            }
        $figures = array();
        foreach($size_list as $key => $value)
            if($value<$block_size-4)
                $figures[] = $key;
        $correct=$correct/100;
        $count_adev=count($devs);
        $count_size=count($figures);
        $count_type=count($bodys);
        $count_color=count($colors);
        $noautogif=array();
        $all_variety=array();
        $reault_massive = array();

        $count_figure=$count_figure_base;
        if($max_extra_figure>0)
            $max_extra_figure=rand(0,$max_extra_figure);

        $count_figure+=$max_extra_figure;


        // start questing generation
        for($i=0;$i<$count_color;$i++)
            for ($j = 0; $j < $count_type; $j++)
                $all_variety[] = Array("color"=>$i,"body"=>$j);

        shuffle($all_variety);

        $questing = array();

        for($j=0;$j<$count_quest;$j++)
        {

            $rnddev=$dev_index[rand(0,$count_adev-1)];

            $maxRand = (int)(($count_figure -2) / ($count_quest - $j) / 2);

            $rnd1 = array_shift($all_variety);
            $rndcolor1 = $rnd1["color"];
            $rndbody1 = $rnd1["body"];

            $rnd1=rand(isset($min_answer) && $min_answer >0 ? $min_answer : 0, isset($max_answer) && $max_answer>0 ? $max_answer : $maxRand);

            $rnd2 = array_shift($all_variety);
            $rndcolor2 = $rnd2["color"];
            $rndbody2 = $rnd2["body"];
            $rnd2=rand(isset($min_answer) && $min_answer >0 ? $min_answer : 0, isset($max_answer) &&  $max_answer>0 ? $max_answer : $maxRand);
            switch ($rnddev) {
                case 0:
                    if($rnd1+$rnd2===0 && !$maybe_zero)
                        $rnd2++;
                    break;
                case 1:
                    if($rnd1-$rnd2===0 && !$maybe_zero)
                        $rnd1++;
                    elseif($rnd1-$rnd2<0 && !$maybe_minus) {
                        $swp=$rnd2;
                        $rnd2=$rnd1;
                        $rnd1=$swp;
                    }
                    break;
                case 2:
                    if($rnd1===0 && !$maybe_zero)
                        $rnd1++;
                    if($rnd2===0 && !$maybe_zero)
                        $rnd2++;
                    break;
            }

            $questing[]=array("b1"=>$rndbody1,"b2"=>$rndbody2,"c1"=>$rndcolor1,"c2"=>$rndcolor2, "r1"=>$rnd1, "r2"=>$rnd2, "d"=>$rnddev);
            $count_figure -= $rnd1 + $rnd2;
            for ($i = 0; $i < $rnd1; $i++)
                $noautogif[] = Array("color" => $rndcolor1, "body" => $rndbody1);
            for ($i = 0; $i < $rnd2; $i++)
                $noautogif[] = Array("color" => $rndcolor2, "body" => $rndbody2);
        }

        for($i=$count_figure;$i>0;) {
            $rnd = array_shift($all_variety);
            $rndcolor = $rnd["color"];
            $rndbody = $rnd["body"];
            $rnd = rand(1,$i);
            if(count($all_variety)===0)
                $rnd=$i;
            else
                $rnd = rand(1,$i/3);
            for ($j = 0; $j < $rnd; $j++)
                $noautogif[] = Array("color" => $rndcolor, "body" => $rndbody);
            $i-=$rnd;
        }

        for($i=0;$i<$count_quest;$i++) {
            $c1=$questing[$i]["c1"];
            $c2=$questing[$i]["c2"];
            $r1=$questing[$i]["r1"];
            $r2=$questing[$i]["r2"];
            $b1=$questing[$i]["b1"];
            $b2=$questing[$i]["b2"];
            $d=$questing[$i]["d"];
            $result = array();
            $result['quest']='';
            $result['quest'] .= $strpleasenter.' '.$strcount.' ';
            $a=true;
            if(rand(1,$count_type+1)>=$count_type) {
                $r1 += $this->all_figure($b1,$c1,$noautogif,false);
                $result['quest'] .=  $strfigures;
                $a=false;
            }
            if($count_color>1) {
                if(rand(1,$count_color+1)<$count_color)
                    $result['quest'] .=  $acolor[$col_index[$c1]].' ';
                else {
                    if($a) {
                        $result['quest'] .= $strfigures;
                        $r1 += $this->all_figure($b1,$c1,$noautogif);
                    }
                    else
                        $r1 = $count_figure_base+$max_extra_figure;
                }
            }
            if($a)
                $result['quest'] .=  $figure[$bod_index[$b1]];
            else
                $result['quest'] .=  $strfigure;
            $a=true;
            $result['quest'] .= ' '. $adev[$d].' '.$strcount.' ';
            if(rand(1,$count_type+1)>=$count_type && $d !== 1) {
                $r2 += $this->all_figure($b2,$c2,$noautogif,false);
                $result['quest'] .= $strfigures;
                $a=false;
            }
            if($count_color>1) {
                if(rand(1,$count_color+1)<$count_color || ($d === 1 && !($maybe_zero && $maybe_minus)))
                    $result['quest'] .= $acolor[$col_index[$c2]].' ';
                else {
                    if($a) {
                        $result['quest'] .= $strfigures;
                        $r2 += $this->all_figure($b2,$c2,$noautogif);
                    }
                    else
                        $r2 = $count_figure_base+$max_extra_figure;

                }
            }
            if($a)
                $result['quest'] .= $figure[$bod_index[$b2]];
            else
                $result['quest'] .= $strfigure;
            switch ($d) {
                case 0:
                    $result['result']=$r1+$r2;
                    break;
                case 1:
                    $result['result']=$r1-$r2;
                    break;
                case 2:
                    $result['result']=$r1*$r2;
                    break;
            }
            $questing[$i] = $result;
        }

        shuffle($noautogif);

        // start generate picture
        $thumb = imagecreatetruecolor($count_figure_x*$block_size,$count_figure_y*$block_size);
        if($bodyf!='') {
            $path=$pathRoot.'fon'.DIRECTORY_SEPARATOR.$bodyf;
            $img = imagecreatefromjpeg($path);  /* TO DO */
            if($img===false)
                $bodyf='';
            else
                imagecopy($thumb, $img, 0, 0, 0, 0, $count_figure_x*$block_size, $count_figure_y*$block_size);
        }
        if($bodyf=='')	{
            $iColor = imagecolorallocate($thumb, $bodyc[0], $bodyc[1], $bodyc[2]);
            imagefill($thumb, 0, 0, $iColor); }

        for($i = 0; $i < $count_figure_y; $i++)
        {
            for($j = 0; $j < $count_figure_x; $j++)
            {
                $size=$figures[rand(0,$count_size-1)];
                $type=$bodys[$noautogif[$i*$count_figure_x+$j]["body"]];
                $color=$colors[$noautogif[$i*$count_figure_x+$j]["color"]];
                $path=$pathRoot.'image'.DIRECTORY_SEPARATOR.$color.DIRECTORY_SEPARATOR.$size.$type.((int)rand(1,$type_list[$type])).".gif";
                $img = imagecreatefromgif($path);
                $hw=$size_list[$size];
                $h=$hw*$correct;
                $cor=$block_size-$hw+$h;
                $x_cor=rand(-$h,$cor);
                $y_cor=rand(-$h,$cor+$h);
                imagecopy($thumb, $img, $block_size*$j+$x_cor, $block_size*$i+$y_cor, 0, 0, $hw, $hw);
                imagedestroy($img);
            }
        }

        if($max_extra_figure)
        {
            $count_figure=$count_figure_base+$max_extra_figure;
            $c=$block_size*$count_figure_x/$max_extra_figure;
            for($i = $count_figure_base; $i < $count_figure; $i++)
            {
                $size=$figures[rand(0,$count_size-1)];
                $type=$bodys[$noautogif[$i]["body"]];
                $color=$colors[$noautogif[$i]["color"]];
                $path=$pathRoot.'image'.DIRECTORY_SEPARATOR.$color.DIRECTORY_SEPARATOR.$size.$type.((int)rand(1,$type_list[$type])).".gif";
                $img = imagecreatefromgif($path);
                $hw=$size_list[$size];
                $cor=$c*($i-$count_figure_base);
                if($hw<$c)
                    $x_cor=rand($cor,$cor+$c-$hw);
                else
                    $x_cor=$cor;
                $y_cor=rand(0,$block_size*$count_figure_y-$hw);
                imagecopy($thumb, $img, $x_cor, $y_cor, 0, 0, $hw, $hw);
                imagedestroy($img);
            }
        }
        $rand = dechex(CRC32(time()));
        $rand = substr($rand,rand(0,6)).dechex($parameters['id']).substr($rand,0,rand(2,8));
        imagejpeg($thumb,'img'.DIRECTORY_SEPARATOR.$rand,$quality);

        // finish picture

        $reault_massive['link'] = $rand;

        $reault_massive['ask'] = $questing;
        return $reault_massive;
    }
    private function all_figure($body,$color,$tmp,$allBody=true) {
        $result = 0;
        if($allBody) {
            for ($i = 0; $i < count($tmp); $i++)
                if ($tmp[$i]["color"] !== $color && $tmp[$i]["body"] === $body)
                    $result++;
        }
        else {
            for ($i = 0; $i < count($tmp); $i++)
                if ($tmp[$i]["color"] === $color && $tmp[$i]["body"] !== $body)
                    $result++;
        }
        return $result;
    }

    private function create_massive(&$str,&$massive,&$t,&$t_index,$cnt,$val=true)
    {
        $mas_key=array_keys($massive);
        $mas_ind=array_flip($mas_key);
        if(is_array($str))
        {
            foreach($str as $i)
            {
                $j=$mas_ind[$i];
                if(isset($j) && !in_array($j,$t_index))
                {
                    if($val)
                        $t[] = $massive[$i];
                    else
                        $t[] = $i;
                    $t_index[] = $j;
                }
            }
            if(count($t)<$cnt) goto def;
        }
        else
        {
            def:	if($val)
            $t = array_values($massive);
        else
            $t = $mas_key;
            $t_index = array_values($mas_ind);
        }
        return;
    }
}
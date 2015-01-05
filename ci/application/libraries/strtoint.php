<?php 
class Strtoint{

    public function toint($_String){
        $ret    =   0;
        for($i=0;$i<4;++$i)
        {
            $one_code   =   strtoupper($_String{$i});
            $one_int    =   ord($one_code)-65;
            $ret+=pow(26,3-$i)*$one_int;
        }
        return $ret;
    }

    public function tostr($_Int)
    {
        $ret    =   '';
        for($i=0;$i<4;++$i)
        {
            $one_code   =   $_Int%26;
            //echo '|'.$one_code.'|';
            
            if($i==2    &&  $one_code>23)
            {
                $_Int += 2;
                $one_code   =   $_Int%26;
                //echo 'a';
            }
            $asc_code   =   $one_code+65;
            //echo '|'.$asc_code.'|';
            $ret    =   chr($asc_code).$ret;
            $_Int   =   ($_Int-$one_code)/26;
        }
        return $ret;
    }

    public function cny($ns)    //人民币变大写
    {
        static $cnums=array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖"), 
        $cnyunits=array("圆","角","分"), 
        $grees=array("拾","佰","仟","万","拾","佰","仟","亿");
        if(strpos($ns,'.')>0)
        {
            list($ns1,$ns2)=explode(".",$ns,2);
        }
        else
        {
            $ns1    =   $ns;
            $ns2    =   0;
        }
        $ns2=array_filter(array($ns2[1],$ns2[0])); 
        $ret=array_merge($ns2,array(implode("",$this->_cny_map_unit(str_split($ns1),$grees)),"")); 
        $ret=implode("",array_reverse($this->_cny_map_unit($ret,$cnyunits))); 
        return str_replace(array_keys($cnums),$cnums,$ret); 
    }

    public function _cny_map_unit($list,$units)
    { 
        $ul=count($units); 
        $xs=array(); 
        foreach (array_reverse($list) as $x)
        { 
            $l=count($xs); 
            if ($x!="0" || !($l%4))
            {
                $n=($x=='0'?'':$x);
                if(($l-1)%$ul   >=   0)
                {
                    $n.=$units[($l-1)%$ul];
                }
            }
            else
            {
                if(is_array($xs[0]))
                {
                    $n=is_numeric($xs[0][0])?$x:''; 
                }
                else
                {
                    $n='';
                }
                
            }
            array_unshift($xs,$n); 
        } 
        return $xs; 
    }
}

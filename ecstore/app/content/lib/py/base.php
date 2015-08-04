<?php


class content_py_base 
{
    
    private $_obj = null;

    function __construct() 
    {
        $this->_obj = fopen(dirname(__FILE__) . '/py.dat', 'rb');
        $this->_prefix = 100;
        $this->_fi_off = 128;
        $this->_se_off = 64;
        $this->_fi = 126;
        $this->_se = 190;
        $this->_po = 9;
        $this->_st = $this->_prefix + $this->_fi * $this->_se * $this->_po;
    }//End Function

    public function get_array($string, $encoding='') 
    {
        if($encoding){
            if(function_exists('mb_convert_encoding')){
                $string = mb_convert_encoding($string, 'GBK', $encoding);
            }elseif(function_exists('iconv')){
                $string = iconv($encoding, 'GBK', $string);
            }else{
                $string = kernel::single('base_charset')->utf2local($string, 'zh');
            }
        }
        $flow = array();
        for ($i=0;$i<strlen($string);$i++)
        {
            if (ord($string[$i]) >= 0x81 and ord($string[$i]) <= 0xfe) 
            {
                $h = ord($string[$i]);
                if (isset($string[$i+1])) 
                {
                    $i++;
                    $l = ord($string[$i]);
                    
                    $table = $this->get_table($h, $l);

                    if ($table) 
                    {
                        array_push($flow,$table);
                    }
                    else 
                    {
                        array_push($flow,$h);
                        array_push($flow,$l);
                    }
                }
                else 
                {
                    array_push($flow,ord($string[$i]));
                }
            }
            else
            {
                array_push($flow,ord($string[$i]));
            }
        }
        
        $data = array();
        foreach($flow AS $k=>$v){
            $data[] = (is_numeric($v)) ? chr($v) : $v[0];
        }

        return $data;
    }//End Function

    private function get_table($h, $l) 
    {
        $off = $this->_prefix + ($h - $this->_fi_off) * $this->_se * $this->_po;
        $off += ($l - $this->_se_off) * $this->_po;

        fseek($this->_obj, $off);
        $str = unpack('a'.$this->_po, fread($this->_obj, $this->_po));
        $str = $str[1];
        $wid = intval(substr($str, 0, 7));
        $len = intval(substr($str, 7, 2));
        
        if($wid > 0 && $len > 0){

            fseek($this->_obj, $wid);
            $str = unpack('a'.$len, fread($this->_obj, $len));
            $arr = explode(',',  $str[1]);
            return $arr;
        }else{

            return false;
        }
    }//End Function

    
}//End Class

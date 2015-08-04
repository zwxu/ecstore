<?php

 
/**
 * 生成验证码code的接口
 * 
 * @version 0.1
 * @package ectools.lib
 */
class ectools_barcode{
	
	/**
	 * 得到code的进入的接口代码
	 * @param mixed 进入的code内容
	 * @param string code处理的接口的方法
	 * @return 处理后的code数据
	 */
    function get($data,$code=39){
        $func = 'code_'.$code;
        if(method_exists($this,$func)){
            return $this->$func($data);
        }else{
            return $data;
        }
    }
    
	/**
	 * 验证码的具体实现方法
	 * @param string 制作验证码的数据
	 * @return string code的html的结果
	 */
    function code_39($data){
        
        $slen = strlen($data);
        $lib['0'] = '0001101000';
        $lib['1'] = '1001000010';
        $lib['2'] = '0011000010';
        $lib['3'] = '1011000000';
        $lib['4'] = '0001100010';
        $lib['5'] = '1001100000';
        $lib['6'] = '0011100000';
        $lib['7'] = '0001001010';
        $lib['8'] = '1001001000';
        $lib['9'] = '0011001000';
        $lib['*'] = '0100101000';

        $code = $lib['*'];
        $row1 = '<td rowspan="2" valign="top" style="padding:0px;border:none">'.$this->code_39_line(0,1,90).'</td>';
        $cell='';
        for($j=1;$j<10;$j++){
            $cell.=$this->code_39_line($code{$j},$j%2!=1,60);
        }
        $row1 .= '<td style="padding:0px;border:none">'.$cell.'</td>';
        $row2 ='<td style="text-align:center;font-size:9px;padding:0px;border:none">*</td>';

        for($i=0;$i<$slen;$i++){
            if($code = $lib[$data{$i}]){
                $cell='';
                for($j=0;$j<10;$j++){
                    $cell.=$this->code_39_line($code{$j},$j%2!=1,60);
                }
                $row1.='<td style="padding:0px;border:none">'.$cell.'</td>';
            }else{
                $row1.='';
            }
            $row2.='<td style="text-align:center;font-size:9px;padding:0px;border:none">'.$data{$i}.'</td>';
        }

        $row2 .='<td style="text-align:center;font-size:9px;padding:0px;border:none">*</td>';
        $code = $lib['*'];
        $cell = '';
        for($j=0;$j<8;$j++){
            $cell.=$this->code_39_line($code{$j},$j%2!=1,60);
        }
        $row1 .= '<td style="padding:0px;border:none">'.$cell.'</td>';
        $row1 .= '<td rowspan="2" valign="top" style="padding:0px;border:none">'.$this->code_39_line(0,1,90).'</td>';

        return "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:auto;border:none\"><tr>{$row1}</tr><tr>{$row2}</tr></table>";
    }
	
    /**
     * 生成验证码的html的image行
     * @param int 宽度制定为5或者2pt
     * @param int 背景图片的是否使用
     * @param int height 单位px
     * @return string html结果
     */
    function code_39_line($i,$b,$h){
        $file = $b?'black.gif':'transparent.gif';
        return '<img src="'.app::get('ectools')->res_url.'/'.$file.'" class="x-barcode" width="'.($i?5:2).'pt" height="'.$h.'px" />';
    }
}
?>

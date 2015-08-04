<?php

 
class base_view_compiler{

    function compile_modifier_default($attrs,$compiler,$bondle_var_only){
        list($string, $default ) = explode(',',$attrs);
        if($default===''){
            $default = '\'\'';
        }
        if($bondle_var_only){
            $compiler->_end_fix_quote($string);
            eval($s='$rst ='.str_replace('$this->bundle_vars','$compiler->bundle_vars',$string).';');
            if($rst){
                return var_export($rst,1);
            }else{
                return $default;
            }
        }else{
            return '((isset('.$string.') && \'\'!=='.$string.')?'.$string.':'.$default.')';
        }
    }
    
    function compile_ecos_logo(){
        return '?>Powered By <a href="http://www.shopex.cn" target="_blank">ECOS</a><?php';
    }

    function compile_math($attrs, &$compiler) {
        if(($attrs['equation']{0}=='\'' || $attrs['equation']{0}=='"') && $attrs['equation']{0}==$attrs['equation'][strlen($attrs['equation'])-1]){
            $equation = $attrs['equation'];
        }else{
            $equation = '"'.$attrs['equation'].'"';
        }
    
        $format = $attrs['format'];
        $assign = $attrs['assign'];
    
        unset($attrs['equation'],$attrs['format'],$attrs['assign']);
    
        foreach($attrs as $k=>$v){
            $re['/([^a-z])'.$k.'([^a-z])/i'] = '$1('.$v.')$2';
        }
        $equation = substr(preg_replace(array_keys($re),array_values($re),$equation),1,-1);
        if($format){
            $equation = 'sprintf('.$format.','.$equation.')';
        }
        if($assign){
            $equation = '$this->_vars['.$assign.']='.$equation;
        }
        return 'echo ('.$equation.');';
    }
}

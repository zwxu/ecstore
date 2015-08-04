<?php

 
class b2c_view_compiler{
    
    public function compile_modifier_gimage($ident){
        list($ident) = explode(',',$ident);
        return "substr($ident,0,strpos($ident,'|'))";
    }

    public function compile_modifier_ship_name($attrs) {
        //todo 得到货币的cur_name
         if(!strpos($attrs,',') || false!==strpos($attrs,',')){
            return $attrs = 'app::get(\'b2c\')->model(\'dlytype\')->get_shipping_name('.$attrs.')';
        }
    }
    
    public function compile_modifier_order_remark($attrs) {
        //todo 得到货币的cur_name
         if(!strpos($attrs,',') || false!==strpos($attrs,',')){
            return $attrs = 'app::get(\'b2c\')->model(\'orders\')->get_order_remark_display('.$attrs.')';
        }
    }
    
    public function compile_modifier_ship_area($attrs) {
        if(!strpos($attrs,',') || false!==strpos($attrs,',')){
            return $attrs = 'app::get(\'ectools\')->model(\'regions\')->change_regions_data('.$attrs.')';
        }
    }
}

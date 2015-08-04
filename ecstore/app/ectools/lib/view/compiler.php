<?php

 
class ectools_view_compiler{

    function compile_modifier_cur($attrs,&$compile) {
        //todo ҪһҲ
        /*if(!strpos($attrs,',') || false!==strpos($attrs,',')){
            return $attrs = 'app::get(\'ectools\')->model(\'currency\')->changer('.$attrs.')';
        }*/

        //todo
        //由于系统中乱用了cur或者cur_odr，所以暂时将两个函数返回值改成同样的，---@lujy
        return $this->compile_modifier_cur_odr($attrs,$compile);
    }

    public function compile_modifier_cur_odr($attrs,&$compile) {
        //todo 需要将货币汇率也缓存
        if(!strpos($attrs,',') || false!==strpos($attrs,',')){
            $arr_attributes = explode(',', $attrs);
            if (count($arr_attributes) <= 2)
            {
                if (count($arr_attributes) < 2)
                {
                    $attrs .= ',$_COOKIE["S"]["CUR"],false,false,app::get(\'b2c\')->getConf(\'system.money.decimals\'),app::get(\'b2c\')->getConf(\'system.money.operation.carryset\')';
                }
                else
                    $attrs .= ',false,false,app::get(\'b2c\')->getConf(\'system.money.decimals\'),app::get(\'b2c\')->getConf(\'system.money.operation.carryset\')';
            }
            elseif (count($arr_attributes) < 4)
            {
                $attrs .= ',false,app::get(\'b2c\')->getConf(\'system.money.decimals\'),app::get(\'b2c\')->getConf(\'system.money.operation.carryset\')';
            }
            else
            {
                $attrs .= ',app::get(\'b2c\')->getConf(\'system.money.decimals\'),app::get(\'b2c\')->getConf(\'system.money.operation.carryset\')';
            }

            return $attrs = 'app::get(\'ectools\')->model(\'currency\')->changer_odr('.$attrs.')';
        }
    }

    public function compile_modifier_cur_name($attrs) {
        //todo 得到货币的cur_name
         if(!strpos($attrs,',') || false!==strpos($attrs,',')){
            return $attrs = 'app::get(\'ectools\')->model(\'currency\')->get_cur_name('.$attrs.')';
        }
    }
    public function compile_modifier_pay_name($attrs) {
        //todo 需要将货币汇率也缓存
        if(!strpos($attrs,',') || false!==strpos($attrs,',')){
            return $attrs = 'app::get(\'ectools\')->model(\'payment_cfgs\')->get_app_display_name('.$attrs.')';
        }
    }

    public function compile_modifier_operactor_name($attrs) {
        if (!strpos($attrs,',') || false!==strpos($attrs,',')){
            return $attrs = 'app::get(\'pam\')->model(\'account\')->get_operactor_name('.$attrs.')';
        }
    }
}

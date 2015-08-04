<?php

 
/**
 * 订单促销规则处理 service
 * $ 2010-05-11 13:28 $
 */
class b2c_sales_solution_process
{
    
    
    /**
     * 获取模板列表信息
     * @params $is_order 是否是订单促销
     */
    public function getTemplateList($is_order=true) {
        $flag = $is_order;
        $aResult = array();
        foreach(kernel::servicelist('b2c_promotion_solution_tpl_apps') as $object) {
            if(method_exists($object, 'get_status')) {
                if(!$object->get_status()) {
                    continue;
                }
            }
            $aResult[get_class($object)] = $object->name;
            if( method_exists($object,'allow') )
                $arr_allow[get_class($object)] = $object->allow($flag); //入参 订单：true|||商品促销：false
        }
        
        $tmp = array('goods'=>app::get('b2c')->_('符合应用条件的商品'));
        if($flag) $tmp['order'] = app::get('b2c')->_('订单');

        foreach( $tmp as $type => $val) {
            foreach($aResult as $class_name => $name) {
                #if( isset($arr_allow[$class_name]) && !$arr_allow[$class_name] ) continue;
                if( isset($arr_allow[$class_name]) ) {
                    if( $arr_allow[$class_name]!=$type ) continue;
                    if( $flag && $arr_allow[$class_name]=='goods' ) continue;
                }
                $aTemp[$type][$class_name] = $val . $name;
            }
        }
        return $aTemp;
    }
    
    
     public function getTemplate($tpl_name,$aData = array(), $type='') {
        $oTC = kernel::single($tpl_name);
        $t = $oTC->config($aData[$tpl_name]); 
        return ($type=='goods' ? app::get('b2c')->_('商品') : app::get('b2c')->_('订单') ) . $oTC->config($aData[$tpl_name]);
    }
    
    public function getType($aData = array(), $key) {
        if(!$aData)return false;
        return $aData[$key]['type'];
    }
    
    
    
}

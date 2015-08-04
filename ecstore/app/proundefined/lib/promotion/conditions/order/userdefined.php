<?php

 
class proundefined_promotion_conditions_order_userdefined{
    var $tpl_name = "用户自定义订单促销模板";

    function getConfig($aData = array()) {
        /////////////////////////////////////// conditions ////////////////////////////////////////////////
        $aConfig['conditions']['type'] = 'auto';
        $aConfig['conditions']['info'] = array();
        ///////////////////////////////// action_conditions ///////////////////////////////////////////////
        $aConfig['action_conditions']['type'] = 'auto';
        $aConfig['action_conditions']['info'] = array();

        return $aConfig;
    }
}


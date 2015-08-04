<?php

 
/**
 * 获取购物车信息(没有优惠处理的) first
 * $ 2010-04-28 20:29 $
 */
class b2c_cart_process_get implements b2c_interface_cart_process {
    private $app;

    public function __construct(&$app){
        $this->app = $app;
    }

    
    public function get_order() {
        return 99;
    }
    
    public function process($aData,&$aResult = array(),$aConfig = array()){
        if(empty($aResult)) {// 购物车时候的处理
            $this->_cart_process($aData,$aResult,$aConfig);
        } else {// 订单修改时候的处理
            $this->_order_process($aData,$aResult,$aConfig);
        }
        $this->app->model('cart')->count_objects($aResult);

    }

    // 购物车里的处理
    private function _cart_process($aData,&$aResult,$aConfig){
        foreach(kernel::servicelist('b2c_cart_object_apps') as $object) {
            if(!is_object($object)) continue;
            if( method_exists($object,'get_order') ) 
                $index = $object->get_order();
            else $index = 10;
            while(true) {
                if( !isset($tmp[$index]) )break;
                $index++;
            }
            $tmp[$index] = $object;
        }
        krsort($tmp);
        
        $aResult['isNeedAddress'] = true;
        $aResult['isNeedDelivery'] = true;
        foreach($tmp as $object) {
            if( method_exists($object,'get_type') )
                $type_name = $object->get_type();
            else
                $type_name = array_pop(explode('_',get_class($object))); // 购物车项类型
            $aResult['object'][$type_name] = $object->getAll(true, $aData);
            foreach ($aResult['object'][$type_name] as $key => $objData) {
                if (isset($objData['goods_kind']) && $objData['goods_kind'] == '3rdparty') {
                    foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                        if ($processor->goodsKindDetail() == $objData['goods_kind_detail'] && $processor->isCustom('order_delivery')) {
                            $aResult['isNeedAddress'] = $processor->isNeedAddress();
                            $aResult['isNeedDelivery'] = $processor->isNeedDelivery();
                            break;
                        }
                    }
                }
            }
        }
    }

    // 订单修时的处理
    private function _order_process($aData,$aResult,$aConfig){
        // 订单修改时的处理
    }
}


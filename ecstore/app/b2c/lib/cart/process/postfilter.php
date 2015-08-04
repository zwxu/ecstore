<?php

 
/**
 * 购物车统处理 forth step 调用lib/cart/postfilter下的处理 订单促销规则过滤
 * $ 2010-04-28 20:29 $
 */
class b2c_cart_process_postfilter implements b2c_interface_cart_process {
    private $app;

    public function __construct(&$app){
        $this->app = $app;
    }
    
    public function get_order() {
        return 80;
    }

    public function process($aData,&$aResult = array(),$aConfig = array()){
        foreach(kernel::servicelist('b2c_cart_postfilter_apps') as $object) {
            if(!is_object($object)) continue;
            $object->filter($aData,$aResult,$aConfig);
        }
        $this->app->model('cart')->count_objects($aResult);
    }
}
?>

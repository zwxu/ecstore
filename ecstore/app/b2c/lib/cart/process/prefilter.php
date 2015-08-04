<?php

 
/**
 * 购物预过滤 second step 调用lib/cart/prefilter下的处理
 * $ 2010-04-28 20:29 $
 */
class b2c_cart_process_prefilter implements b2c_interface_cart_process {
    private $app;

    public function __construct(&$app){
        $this->app = $app;
    }
    
    public function get_order() {
        return 90;
    }

    public function process($aData,&$aResult = array(),$aConfig = array()){
        foreach(kernel::servicelist('b2c_cart_prefilter_apps') as $object) {
            if(!is_object($object)) continue;
            $object->filter($aResult,$aConfig);
        }

        $this->app->model('cart')->count_objects($aResult);
    }
}
?>

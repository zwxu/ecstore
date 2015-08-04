<?php

 
/**
 * 获取购物车信息(没有优惠处理的) first
 * $ 2010-04-28 20:29 $
 */
class business_cart_checkout {
    private $app;

    public function __construct(&$app){
        $this->app = $app;
    }

    
    public function get_order() {
        return 50;
    }
    
    public function check_app_id(&$app_id){
        $app_id = $this->app->app_id;
    }

}


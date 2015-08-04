<?php
  
class cellphone_cart_coupon extends cellphone_cellphone
{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;

        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    function params_to_add($params,&$data=array())
    {
        if(empty($params) || $params['type'] != 'coupon') return;
        $params['products'] = json_decode($params['products'],1);
        $data = array(
            'coupon'=>(string)$params['goods_id'],
            'store_id'=>(string)$params['products']['store_id'],
            'num'=>$params['num'],
            'coupon',
        );
    }
}
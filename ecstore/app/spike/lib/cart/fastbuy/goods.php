<?php

class spike_cart_fastbuy_goods{
    /** 
    * return 立即购买cart
    */
    public function get_fastbuy_arr($goods,$coupon,&$aResult){
        $oCartGoods=kernel::single('spike_cart_object_goods');
        $oCartCoupon=kernel::single('spike_cart_object_coupon');
        
        //$oCartGoods->no_database=true;
        if(is_array($goods) && !empty($goods)){
            $goods_status=$oCartGoods->add_object($goods); //添加商品
            $aResult['object']['goods']= $oCartGoods->getAll(true);
            
        }
        if(is_array($coupon) && !empty($coupon)){
            $coupon_status=$oCartCoupon->add_object($coupon); //添加优惠券
            $aResult['object']['coupon']=$oCartCoupon->getAll(true); //添加优惠券
        }
        
        $config=array();
        $oCartGoods->count($aResult);

        //商品促销
        $goods_process=kernel::single('b2c_cart_process_prefilter');
        $goods_process->process($aData,$aResult,$config);

        //订单促销
        $order_process=kernel::single('b2c_cart_process_postfilter');
        $order_process->process($aData,$aResult,$config);

        //显示抢购促销
        $timebuy_process=kernel::single('spike_cart_process_goods');
        $timebuy_process->process($aData,$aResult,$config);

        //店铺信息
        $business_process=kernel::single('business_cart_process_business');
        $business_process->process($aData,$aResult,$config);
        
    }
}
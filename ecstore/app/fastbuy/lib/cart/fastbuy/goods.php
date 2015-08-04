<?php

class fastbuy_cart_fastbuy_goods{
	/** 
	* return 立即购买cart
	*/
	public function get_fastbuy_arr($goods,$coupon,&$aResult){
		$oCartGoods=kernel::single('fastbuy_cart_object_goods');
		$oCartCoupon=kernel::single('fastbuy_cart_object_coupon');
        
        $aResult['isNeedAddress'] = true;
        $aResult['isNeedDelivery'] = true;
		//$oCartGoods->no_database=true;
		if(is_array($goods) && !empty($goods)){
			$goods_status=$oCartGoods->add_object($goods); //添加商品
			$aResult['object']['goods']= $oCartGoods->getAll(true);
            foreach ($aResult['object']['goods'] as $key => $objData) {
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
		if(is_array($coupon) && !empty($coupon)){
		    $coupon_status=$oCartCoupon->add_object($coupon); //添加优惠券
		    $aResult['object']['coupon']=$oCartCoupon->getAll(true); //添加优惠券
		}
		
		$config=array();
		$oCartGoods->count($aResult);
		//商品促销
		$goods_process=kernel::single('b2c_cart_process_prefilter');
		$goods_process->process($aData,$aResult,$config);

		//显示抢购促销
		$timebuy_process=kernel::single('timedbuy_cart_process_goods');
		$timebuy_process->process($aData,$aResult,$config,'timedbuy');

		//订单促销
		$order_process=kernel::single('b2c_cart_process_postfilter');
		$order_process->process($aData,$aResult,$config);
		

		//店铺信息
		$business_process=kernel::single('business_cart_process_business');
		$business_process->process($aData,$aResult,$config);
		
	}
}
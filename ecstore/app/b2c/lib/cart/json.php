<?php

 
/**
 * 购物车JSON数据
 * $ 2010-04-28 20:02 $
 */
class b2c_cart_json {
    
	function get_json($aData)
	{
		#echo "<pre>";print_r(($aData));
		$json['res_url'] = 'statics';
		$json['guest_enabled'] = 'true';
		$json['register_type'] = array("goods", "adjunct", "gift", "giftpackage", "coupon");
		foreach($aData['aCart']['object']['goods'] as $k_goods=>$v_goods)
		{
			unset($v_goods['min_buy']);
			$agoods[$k_goods] = $v_goods;
			$agoods[$k_goods]['products'] = $v_goods['obj_items']['products'][0];
			$agoods[$k_goods]['products']['quantity'] = $v_goods['obj_items']['products']['quantity'];
			unset($agoods[$k_goods]['obj_items']);
			
		}
		foreach($aData['aCart']['promotion']['order'] as $k_order_promotion=>$v_order_promotion)
		{
			$apromotion['order'][] = $v_order_promotion;
		}
		foreach($aData['aCart']['promotion']['goods'] as $k_order_promotion=>$v_order_promotion)
		{
			$apromotion['goods'][] = $v_order_promotion;
		}
		$json = $aData;
		$json['aCart'] = $aData['aCart']['object'];
		$json['aCart']['order_amount']['subtotal'] = $this->get_cur_order($aData['aCart']['subtotal']);
		$json['aCart']['order_amount']['subtotal_price'] = $this->get_cur_order($aData['aCart']['subtotal_price']);
		$json['aCart']['order_amount']['subtotal_discount'] = $this->get_cur_order($aData['aCart']['subtotal_discount']);
		$json['aCart']['order_amount']['discount_amount_order'] = $this->get_cur_order($aData['aCart']['discount_amount_order']);
		$json['aCart']['order_amount']['discount_amount'] = $this->get_cur_order($aData['aCart']['discount_amount']);
		$json['aCart']['order_amount']['subtotal_prefilter_after'] = $this->get_cur_order($aData['aCart']['subtotal_prefilter_after']);
		$json['aCart']['order_amount']['subtotal_prefilter'] = $this->get_cur_order($aData['aCart']['subtotal_prefilter']);
		$json['aCart']['order_amount']['promotion_subtotal'] = $this->get_cur_order($aData['aCart']['promotion_subtotal']);
		$json['login'] = $json['login'] == 'nologin' ?  false : true;
		$json['aCart']['goods'] = $agoods;
		$json['aCart']['promotion'] = $apromotion;
		return json_encode($json);
	}
	
	function get_cur_order($money)
	{
		$currency = app::get('ectools')->model('currency');
		return $currency->changer_odr($money,$_COOKIE["S"]["CUR"],true,false,app::get('b2c')->getConf('system.money.decimals'),app::get('b2c')->getConf('system.money.operation.carryset'));
	}
}

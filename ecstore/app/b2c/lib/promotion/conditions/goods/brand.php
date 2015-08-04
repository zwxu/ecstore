<?php

 
class b2c_promotion_conditions_goods_brand{
    var $tpl_name = "商品品牌";
    var $tpl_type = 'config';  // 类型 分为 html(写死的html) | config(可选项的) | auto(全开放的配置)

    function getConfig($aData = array()) {
        return  array(
                  'type'=> 'b2c_sales_goods_aggregator_combine',
                  'aggregator'=> 'all',
                  'conditions'=> array(
                                   0 => array(
                                           'type' =>'b2c_sales_goods_item_goods',
                                           'attribute' => 'goods_brand_id'
                                         )
                                 )
                );
    }
	
	/**
	 * 校验参数是否正确
	 * @param mixed 需要校验的参数
	 * @param string error message
	 * @return boolean 是否成功
	 */
	public function verify_form($data=array(), &$msg='')
	{
		if (!$data) return true;
		
		if (!isset($data['conditions']['conditions'][0]['value']) || !$data['conditions']['conditions'][0]['value'])
		{
			$msg = app::get('b2c')->_('请选择商品品牌！');
			return false;
		}
		
		return true;
	}
}
?>

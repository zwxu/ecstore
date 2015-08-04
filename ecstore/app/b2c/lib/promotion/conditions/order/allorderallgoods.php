<?php

 
class b2c_promotion_conditions_order_allorderallgoods{
    var $tpl_name = "对所有订单给予优惠";
    var $type = 'order'; //标识显示那类优惠方案 enum('goods','order'); 为空在显示全部

    var $whole = true; // 如果是false可以不用设置
    function getConfig($aData = array()) {
        $___b2ca=app::get('b2c')->_("对所有订单给予优惠");
        return <<<EOF
    <h4>$___b2ca</h4>
    <input type="hidden" name="conditions[type]" value="b2c_sales_order_aggregator_combine" />
    <input type="hidden" name="conditions[aggregator]" value="all" />
    <input type="hidden" name="conditions[value]" value="1" />

    <input type="hidden" name="conditions[conditions][0][type]" value="b2c_sales_order_item_order" />
    <input type="hidden" name="conditions[conditions][0][attribute]" value="order_subtotal" />
    <input type="hidden" name="conditions[conditions][0][operator]" value=">=" />
    <input type="hidden" name="conditions[conditions][0][value]" value="0" />

    <input type="hidden" name="action_conditions[type]" value="b2c_sales_order_aggregator_item" />
    <input type="hidden" name="action_conditions[aggregator]" value="all" />
    <input type="hidden" name="action_conditions[value]" value="1" />

    <input type="hidden" name="action_conditions[conditions][0][type]" value="b2c_sales_order_item_goods" />
    <input type="hidden" name="action_conditions[conditions][0][attribute]" value="goods_buy_price" />
    <input type="hidden" name="action_conditions[conditions][0][operator]" value=">=" />
    <input type="hidden" name="action_conditions[conditions][0][value]" value="0" />
EOF;
    }
	
	/**
	 * 校验参数是否正确
	 * @param mixed 需要校验的参数
	 * @param string error message
	 * @return boolean 是否成功
	 */
	public function verify_form($data=array(), &$msg='')
	{
		return true;
	}
}


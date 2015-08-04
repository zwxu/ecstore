<?php

 
class b2c_promotion_conditions_order_subtotalallgoods{
    public $tpl_name = "当订单商品总价满X时,对所有商品优惠";
    public $whole = true;
    public $type = 'order'; //标识显示那类优惠方案 enum('goods','order'); 为空在显示全部

    //////////////////////////////////////////////////////////////////////////
    // 过滤条件 订单规则 值未显示 {$aData['conditions'][0]['value']} to {$aData['conditions']['conditions'][0]['value']}
    ///////////////////////////////////////////////////////////////////////////
    public function getConfig($aData = array()) {
        $___b2cd=app::get('b2c')->_('订单金额满');
        return <<<EOF
        $___b2cd
    <input type="hidden" name="conditions[type]" value="b2c_sales_order_aggregator_combine" />
    <input type="hidden" name="conditions[aggregator]" value="all" />
    <input type="hidden" name="conditions[value]" value="1" />
    <input type="hidden" name="conditions[conditions][0][type]" value="b2c_sales_order_item_order" />
    <input type="hidden" name="conditions[conditions][0][attribute]" value="order_subtotal" />
    <input type="hidden" name="conditions[conditions][0][operator]" value=">=" />
    <input type="text" name="conditions[conditions][0][value]" size="3" vtype="required&&unsigned" value="{$aData['conditions']['conditions'][0]['value']}" />

    <input type="hidden" name="action_conditions[type]" value="b2c_sales_order_aggregator_item" />
    <input type="hidden" name="action_conditions[aggregator]" value="all" />
    <input type="hidden" name="action_conditions[value]" value="1" />
    <input type="hidden" name="action_conditions[conditions][0][type]" value="b2c_sales_order_item_goods" />
    <input type="hidden" name="action_conditions[conditions][0][attribute]" value="goods_price" />
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
		if (!$data) return true;
		
		if (!isset($data['conditions']['conditions'][0]['value']) || !$data['conditions']['conditions'][0]['value'])
		{
			$msg = app::get('b2c')->_('需要限定订单满的金额！');
			return false;
		}
		
		if (!preg_match('/^\d*$/', $data['conditions']['conditions'][0]['value']) || $data['conditions']['conditions'][0]['value'] <= 0)
		{
			$msg = app::get('b2c')->_('提交的金额不是数字或者金额小于0了！');
			return false;
		}
		
		return true;
	}
}


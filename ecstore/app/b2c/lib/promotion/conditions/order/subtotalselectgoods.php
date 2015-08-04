<?php


class b2c_promotion_conditions_order_subtotalselectgoods{
    public $tpl_name = "当订单商品总价满X,对指定的商品优惠";

    public function getConfig($aData = array()) {
        /////////////////////////////////////// conditions ////////////////////////////////////////////////
        $aConfig['conditions']['type'] = 'html';
        $aConfig['conditions']['info'] = '';

        ///////////////////////////////// action_conditions ///////////////////////////////////////////////
        $aConfig['action_conditions']['type'] = 'config';
        $aConfig['action_conditions']['info'] = array(
                                                'aggregator'=>'all',
                                                'value'=>1,
                                                'conditions'=>array(
                                                                array(
                                                                    'type'=>'b2c_sales_order_item_goods',
                                                                    'attribute'=>'goods_goods_id'
                                                                )
                                                              )
                                             );
        return $aConfig;
    }

    public function getTemplate($aData = array(),$type = 'conditions') {
         $___b2cd=app::get('b2c')->_('订单金额满');

        
        $o = kernel::single('b2c_frontpage');

        if((! $aData['storeid_filter']) &&   $aData['store_id']){
             $store_id = explode(',',$aData['store_id']);

             foreach($store_id as $key => $val) {
                 if ($val == '') unset($store_id[$key]);
             }
             sort($store_id);
             $aData['storeid_filter'] =array('store_id'=> $store_id);

        }


        $o->pagedata['storeid_filter'] = $aData['storeid_filter'];
        $o->pagedata['isfront'] = $aData['isfront'];


        switch($type) {
            case 'conditions':
                return  <<<EOF
        $___b2cd
    <input type="hidden" name="conditions[type]" value="b2c_sales_order_aggregator_combine" />
    <input type="hidden" name="conditions[aggregator]" value="all" />
    <input type="hidden" name="conditions[value]" value="1" />
    <input type="hidden" name="conditions[conditions][0][type]" value="b2c_sales_order_item_order" />
    <input type="hidden" name="conditions[conditions][0][attribute]" value="order_subtotal" />
    <input type="hidden" name="conditions[conditions][0][operator]" value=">=" />
    <input type="text" name="conditions[conditions][0][value]" size="3" vtype="required&&digits" value="{$aData['conditions'][0]['value']}" />
EOF;
                break;
        }
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

		/** 订单够满金额 **/
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
		/** end **/

		/** 订单制定条件约束 **/
		if (!isset($data['action_conditions']['conditions'][0]['value']) || !$data['action_conditions']['conditions'][0]['value'] || !is_array($data['action_conditions']['conditions'][0]['value']))
		{
			$msg = app::get('b2c')->_('请指定商品！');
			return false;
		}
		/** end **/

		return true;
	}
}


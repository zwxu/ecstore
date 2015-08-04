<?php

 
class proundefined_promotion_conditions_order_subtotaluserdefinedgoods{
    public $tpl_name = "当订单商品总价满X,对指定的商品(自定义)优惠";

    public function getConfig($aData = array()) {
        /////////////////////////////////////// conditions ////////////////////////////////////////////////
        $aConfig['conditions']['type'] = 'html';
        $aConfig['conditions']['info'] = '';

        ///////////////////////////////// action_conditions ///////////////////////////////////////////////
        $aConfig['action_conditions']['type'] = 'auto';
        $aConfig['action_conditions']['info'] = array();
        return $aConfig;
    }

    public function getTemplate($aData = array(),$type = 'conditions') {
       $___b2cd=app::get('b2c')->_('订单金额满');
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
}


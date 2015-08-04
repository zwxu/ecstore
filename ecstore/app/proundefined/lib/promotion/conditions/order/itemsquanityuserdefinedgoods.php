<?php


class proundefined_promotion_conditions_order_itemsquanityuserdefinedgoods{
    public $tpl_name = "当订单商品数量满X,对指定的商品(自定义)优惠";


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
        $___b2cs=app::get('b2c')->_('购物车商品数量满');

        switch($type) {
            case 'conditions':
                return  <<<EOF
        $___b2cs
    <input type="hidden" name="conditions[type]" value="b2c_sales_order_aggregator_combine" />
    <input type="hidden" name="conditions[aggregator]" value="all" />
    <input type="hidden" name="conditions[value]" value="1" />
    <input type="hidden" name="conditions[conditions][0][type]" value="b2c_sales_order_item_order" />
    <input type="hidden" name="conditions[conditions][0][attribute]" value="order_items_quantity" />
    <input type="hidden" name="conditions[conditions][0][operator]" value=">=" />
    <input type="text" name="conditions[conditions][0][value]" size="3" vtype="required&&digits" value="{$aData['conditions'][0]['value']}" />
EOF;
                break;
        }
    }
}


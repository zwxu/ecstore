<?php

 
class b2c_promotion_conditions_order_demo{
    var $tpl_name = "订单促销模板demo";
/*
    // whole = true 整块的
    var $whole = true;
    function getConfig($aData = array()) {
        return <<<EOF
<input name="conditions[type]" value="b2c_sales_order_aggregator_combine" type="hidden">
<input name="conditions[aggregator]" value="all" type="hidden">
<input name="conditions[value]" value="1" type="hidden">

<input name="conditions[conditions][0][type]" value="b2c_sales_order_item_order" type="hidden">
<input name="conditions[conditions][0][attribute]" value="order_subtotal" type="hidden">
<input name="conditions[conditions][0][operator]" value=">=" type="hidden">
<input name="conditions[conditions][0][value]" size="4" value="{$aData['conditions']['conditions'][0]['value']}" type="text" vtype="required&&number">
=< 订单总金额 <
<input name="conditions[conditions][1][type]" value="b2c_sales_order_item_order" type="hidden">
<input name="conditions[conditions][1][attribute]" value="order_subtotal" type="hidden">
<input name="conditions[conditions][1][operator]" value="<" type="hidden">
<input name="conditions[conditions][1][value]" size="4" value="{$aData['conditions']['conditions'][1]['value']}" type="text" vtype="required&&number">

<input name="action_conditions[type]" value="b2c_sales_order_aggregator_item" type="hidden">
<input name="action_conditions[aggregator]" value="all" type="hidden">
<input name="action_conditions[value]" value="1" type="hidden">
<input name="action_conditions[conditions][0][type]" value="b2c_sales_order_item_goods" type="hidden">
<input name="action_conditions[conditions][0][attribute]" value="goods_price" type="hidden">
<input name="action_conditions[conditions][0][operator]" value=">=" type="hidden">
<input name="action_conditions[conditions][0][value]" value="1" type="hidden">
EOF;
    }
    */

    /*
    // html 全html($this->whole = false)
    function getConfig($aData = array()) {
        /////////////////////////////////////// conditions ////////////////////////////////////////////////
        $aConfig['conditions']['type'] = 'html';  // 类型 分为 html(写死的html) | config(可选项的) | auto(全开放的配置)
        $aConfig['conditions']['info'] = <<<EOF
订单促销模板demo(conditions)这里写hidden项就OK了 写死的
EOF;

        ///////////////////////////////// action_conditions ///////////////////////////////////////////////
        $aConfig['action_conditions']['type'] = 'html';  // 类型 分为 html(写死的html) | config(可选项的) | auto(全开放的配置)
        $aConfig['action_conditions']['info'] = <<<EOF
订单促销模板demo(action_conditions)这里写hidden项就OK了 写死的
EOF;
        return $aConfig;
    }*/

    /*
    // config 全配置
    function getConfig($aData = array()) {
        /////////////////////////////////////// conditions ////////////////////////////////////////////////
        $aConfig['conditions']['type'] = 'config';  // 类型 分为 html(写死的html) | config(可选项的) | auto(全开放的配置)
        $aConfig['conditions']['info'] = array(
                                            'conditions'=> array(
                                                                array(
                                                                    'type'=>'b2c_sales_order_item_goods',
                                                                    'attribute'=>'goods_goods_id',
                                                                ),
                                                                array(
                                                                    'type'=>'b2c_sales_order_item_order',
                                                                    'attribute'=>'order_subtotal',
                                                                ),
                                                                array(
                                                                    'type'=>'b2c_sales_order_aggregator_subselect',
                                                                    'conditions'=>array(
                                                                                        array(
                                                                                            'type'=>'b2c_sales_order_item_goods',
                                                                                            'attribute'=>'goods_name',
                                                                                        ),
                                                                                        array(
                                                                                            'type'=>'b2c_sales_order_item_goods',
                                                                                            'attribute'=>'goods_weight',
                                                                                        ),
                                                                    )
                                                                ),
                                                              0=>'item_goods_id',
                                                              1=>'order_subtotal',
                                                              2=> array(
                                                                    'type'=>'subselect',   // 这个必段要写咯(没有实现由condition 去判断type的, 不写的话默认是'combine') 可以选择的是 subselect|found|combine 三种咯
                                                                    'conditions'=>array( // 如果type是 subselect|found 条件必须是商品项的属性
                                                                                     0=>'item_name',
                                                                                     1=>'item_weight',
                                                                                  )
                                                                  ),
                                                              3=> array(
                                                                    'conditions'=> array(
                                                                                      0=>'item_cost',
                                                                                      1=>'order_subtotal',
                                                                                   ),
                                                              )
                                                           ),
                                         );

        ///////////////////////////////// action_conditions ///////////////////////////////////////////////
        $aConfig['action_conditions']['type'] = 'config';  // 类型 分为 html(写死的html) | config(可选项的) | auto(全开放的配置)
        $aConfig['action_conditions']['info'] = array(
                                                  'conditions'=> array( // 这里只能是 订单商品项的信息
                                                                    'item_name',
                                                                    'item_goods_id',
                                                                    'item_quantity',
                                                                    array(
                                                                        'type'=>'product_combine',
                                                                        'conditions'=>array(
                                                                                         'item_weight',
                                                                                         'item_price',
                                                                                         'item_buy_price'
                                                                                      ),
                                                                    ),
                                                                 ),
                                                );
         return $aConfig;
    }*/

    /*
    // auto
    function getConfig($aData = array()) {
     /////////////////////////////////////// conditions ////////////////////////////////////////////////
        $aConfig['conditions']['type'] = 'auto';
        $aConfig['conditions']['info'] = null;
        ///////////////////////////////// action_conditions ///////////////////////////////////////////////
        $aConfig['action_conditions']['type'] = 'auto';
        $aConfig['action_conditions']['info'] = null;
        return $aConfig;
    }*/

    // 半配置半auto
    function getConfig($aData = array()) {
        /////////////////////////////////////// conditions ////////////////////////////////////////////////
        $aConfig['conditions']['type'] = 'config';  // 类型 分为 html(写死的html) | config(可选项的) | auto(全开放的配置)
        $aConfig['conditions']['info'] = array(
                                            'conditions'=> array(
                                                                array(
                                                                    'type'=>'b2c_sales_order_item_order',
                                                                    'attribute'=>'order_subtotal'
                                                                )
                                                           ),
                                         );

       /////////////////////////////////////// action_conditions ////////////////////////////////////////////////
        $aConfig['action_conditions']['type'] = 'auto';
        $aConfig['action_conditions']['info'] = '';
        return $aConfig;
    }
}


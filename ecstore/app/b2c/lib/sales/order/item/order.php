<?php

 
/**
 * order_info (cart)
 * $ 2010-05-9 16:14 $
 */
class b2c_sales_order_item_order extends b2c_sales_order_item
{
    public function getItem() {
        return array(
                'order_subtotal'=> array(
                                        'name'=>app::get('b2c')->_('订单总价'),
                                        'path'=>'subtotal_prefilter_after',
                                        'type'=>'order',
                                        'object'=>'b2c_sales_order_item_order',
                                        'operator'=>array('equal','equal1'),
                                        'vtype'=>'unsigned',
                                        'unit'=> array('app'=>'ectools','model'=>'currency','func'=>'changer_odr'),
                                   ),
               'order_subtotal_weight'=> array(
                                        'name'=>app::get('b2c')->_('订单总重量'),
                                        'path'=>'subtotal_weight',
                                        'type'=>'order',
                                        'object'=>'b2c_sales_order_item_order',
                                        'operator'=>array('equal','equal1'),
                                        'vtype'=>'unsigned',
                                   ),
                'order_subtotal_gain_score'=> array(
                                    'name'=>app::get('b2c')->_('订单获得积分总数'),
                                    'path'=>'subtotal_gain_score',
                                    'type'=>'order',
                                    'object'=>'b2c_sales_order_item_order',
                                    'operator'=>array('equal','equal1'),
                                    'vtype'=>'unsigned'),
                'order_subtotal_consume_score'=> array(
                                    'name'=>app::get('b2c')->_('订单消费积分总数'),
                                    'path'=>'subtotal_consume_score',
                                    'type'=>'order',
                                    'object'=>'b2c_sales_order_item_order',
                                    'operator'=>array('equal','equal1'),
                                    'vtype'=>'unsigned'),
                'order_items_quantity'=> array(
                                    'name'=>app::get('b2c')->_('订单商品总数量'),
                                    'path'=>'items_quantity',
                                    'type'=>'order',
                                    'object'=>'b2c_sales_order_item_order',
                                    'operator'=>array('equal','equal1'),
                                    'vtype'=>'digits',
                                    'unit'=>app::get('b2c')->_('个'),
                                    ),
                'order_items_count'=> array(
                                    'name'=>app::get('b2c')->_('订单项总数'),
                                    'path'=>'items_count',
                                    'type'=>'order',
                                    'object'=>'b2c_sales_order_item_order',
                                    'operator'=>array('equal','equal1'),
                                    'vtype'=>'digits',
                                    'unit'=>app::get('b2c')->_('项'),
                                    ),
        );
    }
}
?>

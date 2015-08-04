<?php

 
/**
 * goods_items(cart)
 * $ 2010-05-08 19:38 $
 */
class b2c_sales_order_item_goods extends b2c_sales_order_item
{
    public function getItem() {
        return array(
                    'goods_goods_id'=> array(
                                            'name'=>app::get('b2c')->_('商品'),
                                            'path'=>'obj_items/products/0/goods_id',
                                            'type'=>'goods',
                                            'object'=>'b2c_sales_order_item_goods',
                                            'operator'=>array('contain'),
                                            'input'=>'dialog',
                                            'table'=>'goods@b2c',
                                        ),
                    'goods_product_id'=> array(
                                            'name'=>app::get('b2c')->_('货品'),
                                            'path'=>'obj_items/products/0/product_id',
                                            'type'=>'goods',
                                            'object'=>'b2c_sales_order_item_goods',
                                            'operator'=>array('contain'),
                                            'input'=>'dialog',
                                            'table'=>'products@b2c',
                                        ),
                    'goods_bn'=> array(
                                            'name'=>app::get('b2c')->_('商品货号'),
                                            'path'=>'obj_items/products/0/bn',
                                            'type'=>'goods',
                                            'object'=>'b2c_sales_order_item_goods',
                                            'operator'=>array('equal','contain','contain1'),'vtype'=>'alphaint'
                                 ),
                    'goods_price'=> array(
                                            'name'=>app::get('b2c')->_('销售价'),
                                            'path'=>'obj_items/products/0/price/price',
                                            'type'=>'goods',
                                            'object'=>'b2c_sales_order_item_goods',
                                            'operator'=>array('equal','equal1'),'vtype'=>'unsigned'
                                   ), // table:goods->price (其实是sdb_products 的price)
                    'goods_cost' => array(
                                            'name'=>app::get('b2c')->_('成本价'),
                                            'path'=>'obj_items/products/0/price/cost',
                                            'type'=>'goods',
                                            'object'=>'b2c_sales_order_item_goods',
                                            'operator'=>array('equal','equal1'),'vtype'=>'unsigned'
                                    ), // table:goods->cost
                    'goods_member_lv_price' => array(
                                                 'name'=>app::get('b2c')->_('会员价'),
                                                 'path'=>'obj_items/products/0/price/member_lv_price',
                                                 'type'=>'goods',
                                                 'object'=>'b2c_sales_order_item_goods',
                                                 'operator'=>array('equal','equal1'),'vtype'=>'unsigned'
                                    ), // 指定的商品会员价格,没有指定用price和会员的优惠折扣算出 的值
                    'goods_buy_price' => array(
                                             'name'=>app::get('b2c')->_('出售价'),
                                             'path'=>'obj_items/products/0/price/buy_price',
                                             'type'=>'goods',
                                             'object'=>'b2c_sales_order_item_goods',
                                             'operator'=>array('equal','equal1'),'vtype'=>'unsigned'
                                         ), // 有商品促销优惠后的价格 没有 则是 $member_lv_price
                    'goods_weight' => array(
                                         'name'=>app::get('b2c')->_('商品重量'),
                                         'path'=>'obj_items/products/0/weight',
                                         'type'=>'goods',
                                         'object'=>'b2c_sales_order_item_goods',
                                         'operator'=>array('equal','equal1'),'vtype'=>'unsigned'),
                    'goods_name' => array(
                                         'name'=>app::get('b2c')->_('商品名称'),
                                         'path'=>'obj_items/products/0/name',
                                         'type'=>'goods',
                                         'object'=>'b2c_sales_order_item_goods',
                                         'operator'=>array('equal','contain','contain1'),'vtype'=>'alphanum'
                                    ),
                    'goods_consume_score'=> array(
                                                 'name'=>app::get('b2c')->_('消费积分数'),
                                                 'path'=>'obj_items/products/0/consume_score',
                                                 'type'=>'goods',
                                                 'object'=>'b2c_sales_order_item_goods',
                                                 'operator'=>array('equal','equal1'),'vtype'=>'unsigned'
                                            ), // todo 单商品 可能会有改动
                    'goods_gain_score'=> array(
                                              'name'=>app::get('b2c')->_('获得积分数'),
                                              'path'=>'obj_items/products/0/gain_score',
                                              'type'=>'goods',
                                              'object'=>'b2c_sales_order_item_goods',
                                              'operator'=>array('equal','equal1'),'vtype'=>'unsigned'
                                          ), // todo 单商品 可能会有改动
                    'goods_type_id'=> array(
                                           'name'=>app::get('b2c')->_('商品类型'),
                                           'path'=>'obj_items/products/0/type_id',
                                           'type'=>'goods',
                                           'object'=>'b2c_sales_order_item_goods',
                                           'operator'=>array('contain'),'input'=>'checkbox',
                                           'options'=>'table:SELECT type_id AS id,name FROM sdb_b2c_goods_type'
                                       ),
                    //'goods_brand_id'=> array('name'=>app::get('b2c')->_('商品品牌'),'path'=>'obj_items/products/0/brand_id','type'=>'goods','object'=>'b2c_sales_order_item_goods','operator'=>array('equal','contain'),'input'=>'checkbox','options'=>'table:SELECT brand_id AS id,brand_name AS name FROM sdb_b2c_brand'), // 现在没出现,但出现机率很大 要考虑进去哦 2010-03-25 11:14 wubin
                    //'goods_cat_id'=> array('name'=>app::get('b2c')->_('商品分类'),'path'=>'obj_items/products/0/cat_id','type'=>'goods','object'=>'b2c_sales_order_item_goods','operator'=>array('equal','contain'),'input'=>'checkbox','options'=>'table:SELECT cat_id AS id,cat_name AS name FROM sdb_b2c_goods_cat'), // 现在没出现,但出现机率很大 要考虑进去哦
        );
    }
}
?>

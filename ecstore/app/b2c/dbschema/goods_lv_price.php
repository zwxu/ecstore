<?php 

 
/**
* @table goods_lv_price;
* @package Schemas
* @version 
*/
$db['goods_lv_price']=array (
  'columns' => 
  array (
    'goods_id' => 
    array(
        'type' => 'table:goods',
        'default' => 0,
        'required' => true,
        'pkey' => true,
        'editable' => false
    ),
    'product_id' => 
    array (
      'type' => 'table:products',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'level_id' => 
    array (
      'type' => 'table:member_lv',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'price' => 
    array (
      'type' => 'money',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('商品会员等级价格'),
);

<?php 

 
$db['goods_entity_items']=array ( 
  'columns' => 
  array (
    'items_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'goods_id' =>
    array (
      'type' => 'table:goods',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('商品ID'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'product_id' => 
    array (
      'type' => 'table:products',
      'default' => 0,
      'required' => true,
      'label' => '货品编号',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'card_id' => 
    array (
      'type' => 'varchar(20)',
      'default' => 0,
      'required' => true,
      'label' => '卡号',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'card_psw' => 
    array (
      'type' => 'varchar(20)',
      'default' => 0,
      'required' => true,
      'label' => '密码',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'store_id' => 
    array (
      'type' => 'table:storemanger@business',
      'required' => false,
      'label' => '店铺名称',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'order_id' => 
    array (
      'type' => 'table:orders',
      'required' => false,
      'label' => '使用订单号',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'is_used' => 
    array (
      'type' => 
      array(
        '1' => app::get('b2c')->_('未售出'),
        '2' => app::get('b2c')->_('已售出'),
      ),
      'default' => '1',
      'required' => true,
      'comment' => app::get('b2c')->_('是否发放'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('b2c')->_('是否发放'),
    ),
    'random' =>
    array (
      'type' => 'varchar(20)',
      'width' => 110,
      'comment' => app::get('b2c')->_('卡号密码生成的key'),
      'editable' => false,
    ),
    'send_time' => 
    array (
      'type' => 'time',
      'required' => false,
      'label' => '发放时间',
      'width' => 110,
      'editable' => false,
    ),
    'key' =>
    array (
      'type' => 'varchar(20)',
      'width' => 110,
      'comment' => app::get('b2c')->_('卡号密码生成的key'),
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 46974 $',
);

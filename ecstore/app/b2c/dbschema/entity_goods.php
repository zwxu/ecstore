<?php 

 
$db['entity_goods']=array ( 
  'columns' => 
  array (
    'id' => 
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
    'item_id' => 
    array (
      'type' => 'table:goods_entity_items',
      'label' => '卡号编号',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'member_id' => 
    array (
      'type' => 'table:members',
      'label' => '会员用户名',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'store_id' => 
    array (
      'type' => 'table:storemanger@business',
      'required' => true,
      'default' => 0,
      'label' => '店铺名称',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'order_id' => 
    array (
      'type' => 'table:orders',
      'required' => true,
      'default' => 0,
      'label' => '订单号',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'send_time' => 
    array (
      'type' => 'time',
      'required' => false,
      'label' => '发放时间',
      'width' => 110,
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 46974 $',
);


<?php
$db['sell_log']=array (
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
    'order_id' =>
    array (
      'type' => 'table:orders@b2c',
      'required' => true,
      'default' => 0,
      'label' => __('订单号'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'giftpackage_id' =>
    array (
      'type' => 'table:activity',
      'label' => __('捆绑商品id'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'quantity' => 
    array (
      'type' => 'float unsigned',
      'label' => __('数量'),
      'editable' => false,
      'in_list' => true,
    ),
    'member_id' => 
    array (
      'type' => 'table:account@pam',
      'label' => __('会员 id'),
      'editable' => false,
    ),
  ),
  'comment' => __('捆绑商品购买记录'),
);
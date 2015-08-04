<?php 
$db['comment_orders_point']=array ( 
  'columns' => 
  array (
    'point_id' => array (
        'type' => 'number',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'label' => 'ID',
        'width' => 110,
        'editable' => false,
        'default_in_list' => true,
    ),
    'store_id' => array (
        'type' => 'table:storemanger',
        'label' => app::get('b2c')->_('店铺ID'),
        'required' => true,
        'default' => 0,
    ),
    'member_id' => array(
        'type' => 'table:members@b2c',
        'label' => app::get('b2c')->_('会员ID'),
        'default' => 0,
    ),
    'point' => array (
        'type' => 'decimal(2,1)',
        'label' => app::get('b2c')->_('分数'),
    ),
    'type_id' => array(
        'type' => 'table:comment_goods_type@b2c',
        'label' =>app::get('b2c')->_('评论类型'),
        'default' => 0,
        'required' => true,
    ),
    'order_id' => array (
        'type' => 'table:orders@b2c',
        'label' => app::get('b2c')->_('订单ID'),
        'default' => 0,
        'required' => true,
    ),
    'addon' => array (
        'type' => 'longtext',
        'editable' => false,
    ),
    'disabled' => array(
        'type'=> "enum('false', 'true')",
        'default' =>'false',
        'default_in_list' => true,
    ),
  ),
  'index' => 
  array (
    'ind_store_id' => array (
      'columns' => 
      array (
        0 => 'store_id',
      ),
    ),
    'ind_disabled' => array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
);
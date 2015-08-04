<?php 

 
$db['order_delivery']=array (
  'columns' => 
  array (
    'order_id' => 
    array (
      'type' => 'table:orders',
      'required' => true,
      'pkey' => true,
      'default' => 0,
      'editable' => false,
    ),
    'dlytype' => 
    array (
      'type' => 
      array (
        'delivery' => app::get('b2c')->_('发货单'),
        'reship' => app::get('b2c')->_('退货单'),
      ),
      'default' => 'delivery',
      'required' => true,
      'label' => app::get('b2c')->_('单据类型'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'dly_id' => 
    array (
      'type' => 'varchar(20)',
      'pkey' => true,
      'required' => true,
      'label' => app::get('b2c')->_('关联单号'),
      'width' => 110,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'items' => 
    array (
      'type' => 'text',
      'label' => app::get('b2c')->_('货品明细'),
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 41996 $',
);

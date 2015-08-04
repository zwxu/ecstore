<?php 
 
 
$db['cart']=array (
  'columns' => 
  array (
    'cart_id' => 
    array (
      'type' => 'bigint unsigned',
      'extra' => 'auto_increment',
      'pkey' => true,
      'label' => 'ID',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'member_ident' => 
    array (
      'type' => 'varchar(50)',
      'pkey' => true,
      'required' => true,
      'label' => app::get('b2c')->_('会员ident'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'params' => 
    array (
      'type' => 'serialize',
      'required' => true,
      'label' => app::get('b2c')->_('购物车对象参数'),
      'editable' => false,
      'in_list' => true,
    ),
   ),

  'engine' => 'innodb',
  'version' => '$Rev: 43884 $',
);

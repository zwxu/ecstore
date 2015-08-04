<?php 
 
 
$db['cart_objects']=array (
  'columns' => 
  array (
    'obj_ident' => 
    array (
      'type' => 'varchar(255)',
      'pkey' => true,
      'required' => true,
      'label' => app::get('b2c')->_('对象ident'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
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
    'member_id' => 
    array (
      'type' => 'int(8) ',
      'pkey' => true,
      'required' => true,
      'label' => app::get('b2c')->_('会员 id'),
      'editable' => false,
      'default' => -1,
    ),
    'obj_type' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'label' => app::get('b2c')->_('购物车对象类型'),
      'editable' => false,
      'in_list' => true,
    ),
    'params' => 
    array (
      'type' => 'serialize',
      'required' => true,
      'label' => app::get('b2c')->_('购物车对象参数'),
      'editable' => false,
      'in_list' => true,
    ),
    'quantity' => 
    array (
      'type' => 'float unsigned',
      'required' => true,
      'label' => app::get('b2c')->_('数量'),
      'editable' => false,
      'in_list' => true,
    ),
    'time' =>
    array(
      'type' => 'time',
      'label' => app::get('b2c')->_('时间'),
    ),
  ),
  'index' => 
  array (
    'ind_member_id' => 
    array (
      'columns' => 
      array (
        0 => 'member_id',
      ),
    ),
  ),
  'comment' => app::get('b2c')->_('购物车'),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
  'unbackup' => true,
  'ignore_cache' => true,
);

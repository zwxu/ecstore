<?php 
$db['storemember']=array ( 
  'columns' => 
  array (
    'attach_id' => array (
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
    'store_id' => array (
      'type' => 'table:storemanger',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'pkey'=>false,
    ),
    'member_id' => array (
      'type' => 'table:members@b2c',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'pkey'=>false,
    ),

     'roles_id' => array (
      'type' => 'table:storeroles',
      'label' => app::get('business')->_('店员角色'),
      'required' => true,
      'default' => 0,
      'editable' => false,
      'pkey'=>false,
    ),

    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'd_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => app::get('b2c')->_('排序'),
      'width' => 30,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
    
   ),
    
  'version' => '$Rev$',

);
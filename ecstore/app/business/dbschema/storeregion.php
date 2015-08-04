<?php 
$db['storeregion']=array ( 
  'columns' =>
  array (
    'region_id' =>
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

    'region_name' => 
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('范围名称'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
    ),
 

   'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),

    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
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
    'remark' => 
    array (
      'label' => app::get('b2c')->_('备注'),
      'type' => 'text',
      'width' => 75,
      'in_list' => true,
      'default_in_list' => true,
    ),
    )
);
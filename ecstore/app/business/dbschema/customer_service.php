<?php 

 
$db['customer_service']=array ( 
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
    'type' => 
    array (
      'type' => 
      array(
        'qq' => app::get('business')->_('QQ'),
        'ww' => app::get('business')->_('旺旺'),
      ),
      'default' => 'qq',
      'required' => true,
      'comment' => app::get('business')->_('种类'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('business')->_('种类'),
    ),
    'name' => 
    array (
      'type' => 'varchar(50)',
      'default' => 0,
      'required' => true,
      'label' => '名称',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'number' => 
    array (
      'type' => 'varchar(50)',
      'default' => 0,
      'required' => true,
      'label' => '账号',
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
    'is_defult' => 
    array (
      'type' => 
      array(
        '1' => app::get('business')->_('是'),
        '2' => app::get('business')->_('否'),
      ),
      'default' => '2',
      'required' => true,
      'comment' => app::get('business')->_('是否默认'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('business')->_('是否默认'),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 46974 $',
);

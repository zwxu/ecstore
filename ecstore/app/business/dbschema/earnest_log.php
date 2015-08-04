<?php 
$db['earnest_log']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'store_id' => 
    array (
      'type' => 'table:storemanger',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'earnest_value' => 
    array (
      'type' => 'float',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'last_modify' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'addtime' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'expiretime' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'reason' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'remark' => 
    array (
      'type' => 'varchar(100)',
      'required' => false,
      'default' => '',
      'editable' => false,
    ),
    'orders' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'goods' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'source' => 
    array (
      'type' => 
      array (
        '1' => '虚假发货',
        '2' => '管理员',
        '3' => '前台'
      ),
      'required' => true,
      'default' => '1',
      'editable' => false,
    ),
    'operator' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'comment' => app::get('b2c')->_('店铺保证金变更历史'),
  'version' => '$Rev: 43105 $',
);

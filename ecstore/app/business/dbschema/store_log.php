<?php 
$db['store_log']=array (
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
    'origin_value' => 
    array (
      'type' => 'decimal',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'change_value' => 
    array (
      'type' => 'decimal',
      'required' => true,
      'default' => '0',
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
    'type' => 
    array (
      'type' => 
      array (
        'space' => '图片空间',
        'experience' => '店铺经验',
      ),
      'required' => true,
      'default' => 'experience',
      'editable' => false,
    ),
    'source' => 
    array (
      'type' => 
      array (
        '1' => '评分',
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
  'index' => 
  array (
    'store_log_type' => 
    array (
      'columns' => 
      array (
        0 => 'type',
      ),
    ),
    'store_log_source' => 
    array (
      'columns' => 
      array (
        0 => 'source',
      ),
    ),
  ),
  'engine' => 'innodb',
  'comment' => app::get('b2c')->_('店铺某些信息变更历史'),
  'version' => '$Rev: 43105 $',
);

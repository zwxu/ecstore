<?php 

 
$db['member_lv']=array (
  'columns' => 
  array (
    'member_lv_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(100)',
      'is_title' => true,
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('等级名称'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'dis_count' => 
    array (
      'type' => 'decimal(5,2)',
      'default' => '1',
      'required' => true,
      'label' => app::get('b2c')->_('会员折扣率'),
      'width' => 110,
      'match' => '[0-9\\.]+',
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'pre_id' => 
    array (
      'type' => 'mediumint',
      'editable' => false,
    ),
    'default_lv' => 
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('是否默认'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'deposit_freeze_time' => 
    array (
      'type' => 'int',
      'default' => 0,
      'editable' => false,
    ),
    'deposit' => 
    array (
      'type' => 'int',
      'default' => 0,
      'editable' => false,
    ),
    'more_point' => 
    array (
      'type' => 'int',
      'default' => 1,
      'editable' => false,
    ),
    'lv_type' => 
    array (
      'type' => 
      array (
        'retail' => app::get('b2c')->_('零售'),
        'wholesale' => app::get('b2c')->_('批发'),
        'dealer' => app::get('b2c')->_('代理'),
      ),
      'default' => 'retail',
      'required' => true,
      'label' => app::get('b2c')->_('等级类型'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'point' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('所需积分'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'show_other_price' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'editable' => false,
    ),
    'order_limit' => 
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'order_limit_price' => 
    array (
      'type' => 'money',
      'default' => '0.000',
      'required' => true,
      'editable' => false,
    ),
    'lv_remark' => 
    array (
      'type' => 'text',
      'editable' => false,
    ),
    'experience' => 
    array (
      'label' => app::get('b2c')->_('所需经验值'),
      'type' => 'int(10)',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'expiretime' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
    'ind_name' => 
    array (
      'columns' => 
      array (
        0 => 'name',
      ),
      'prefix' => 'UNIQUE',
    ),
    
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 44523 $',
);

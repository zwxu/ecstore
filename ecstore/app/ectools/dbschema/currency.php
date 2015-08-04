<?php 

 
$db['currency']=array (
  'columns' => 
  array (
    'cur_id' => 
    array (
      'type' => 'int(8)',
      'required' => true,
      'pkey' => true,
      'label' => app::get('ectools')->_('货币ID'),
      'editable' => false,
      'extra' => 'auto_increment',
      'in_list' => false,
    ),
    'cur_name' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'label' => app::get('ectools')->_('货币名称'),
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'cur_sign' => 
    array (
      'type' => 'varchar(5)',
      'label' => app::get('ectools')->_('货币符号'),
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'cur_code' => 
    array (
      'type' => 'varchar(8)',
      'required' => true,
      'default' => '',
      'label' => app::get('ectools')->_('货币代码'),
      'editable' => false,
      'in_list' => true,
      'is_title' => true,
      'default_in_list' => true,
    ),
    
    
    'cur_rate' => 
    array (
      'type' => 'decimal(10,4)',
      'default' => '1.0000',
      'required' => true,
      'label' => app::get('ectools')->_('汇率'),
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'cur_default' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => app::get('ectools')->_('默认货币'),
      'in_list' => true,
      'default_in_list' => true,
    ),
  ),
  'label' => app::get('ectools')->_('货币'),
  'index' => 
  array (
    'uni_ident_type' => 
    array (
      'columns' => 
      array (
        0 => 'cur_code',
      ),
      'prefix' => 'UNIQUE',
    ),
  ),
  'version' => '$Rev: 40654 $',
);

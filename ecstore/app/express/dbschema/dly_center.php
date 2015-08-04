<?php

 
$db['dly_center']=array (
  'columns' => 
  array (
    'dly_center_id' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'in_list' => false,
      'label' => app::get('express')->_('出货点id'),
    ),
    'name' => 
    array (
      'type' => 'varchar(50)',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('express')->_('发货点名称'),
    ),
    'address' => 
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('express')->_('发货点地址'),
    ),
    'region' =>
    array(
      'type' => 'region',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('express')->_('地区'),
    ),
    'zip' => 
    array (
      'type' => 'varchar(20)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'label' => app::get('express')->_('邮编'),
    ),
    'phone' =>
    array(
      'type' => 'varchar(100)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('express')->_('电话'),
    ),
    'uname' =>
    array(
      'type' => 'varchar(100)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('express')->_('姓名'),
    ),
    'cellphone' =>
    array(
      'type' => 'varchar(100)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('express')->_('手机'),
    ),
    'sex' => 
    array (
      'type' => 
      array(
        'female' => app::get('express')->_('女性'),
        'male' => app::get('express')->_('男性'),
      ),
      'default' => 'male',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('express')->_('性别'),
    ),
    'memo' =>
    array(
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('express')->_('备注'),
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 50831 $',
);

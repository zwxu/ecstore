<?php
$db['widgets_proinstance']=array (
  'columns' => 
  array (
    'widgets_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'level' =>
    array (
      'type' => array(
        'system' => app::get('site')->_('系统级'),
        'theme' => app::get('site')->_('模板级'),
      ),
      'required' => true,
      'default' => 'system',
      'label' => app::get('site')->_('实例类型'),
      'in_list' => true,
      'default_in_list' => true,
      'width'  => 60,
      'editable' => false,
    ),
    'flag' =>
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => '',
      'label' => app::get('site')->_('实例名称'),
      'in_list' => true,
      'default_in_list' => true,
      'width'  => 150,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'searchtype' => 'has',
    ),
    'memo' => 
    array (
      'type' => 'varchar(254)',
      'required' => true,
      'default' => '',
      'label' => app::get('site')->_('备注'),
      'in_list' => true,
      'default_in_list' => true,
      'width'  => 250,
      'editable' => false,
    ),
    'widgets_type' => 
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => '',
      'label' => app::get('site')->_('挂件名'),
      'in_list' => true,
      'default_in_list' => true,
      'width'  => 80,
      'editable' => false,
    ),
    'app' =>
    array (
      'type' => 'varchar(30)',
      'default' => '',
      'label' => app::get('site')->_('程序目录'),
      'in_list' => true,
      'default_in_list' => true,
      'width'  => 80,
      'editable' => false,
    ),
    'theme' => 
    array (
      'type' => 'varchar(30)',
      'default' => '',
      'label' => app::get('site')->_('模板目录'),
      'width'  => 80,
      'editable' => false,
    ),
    'title' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'domid' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'border' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'classname' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'tpl' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'params' => 
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'modified' => 
    array (
      'type' => 'time',
      'editable' => false,
    ),
  ),
  'version' => '$Rev$',
  'unbackup' => true,
);


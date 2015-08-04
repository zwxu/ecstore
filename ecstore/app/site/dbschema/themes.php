<?php

$db['themes']=array (
  'columns' => 
  array (
    'theme' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
      'is_title' => true,
      'label'=>app::get('site')->_('目录'),
      'width'=>'90',
      'in_list'=>true,
      'default_in_list'=>true,
    ),
    'name' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'is_title'=>true,
      'label'=>app::get('site')->_('模板名称'),
      'width'=>'200',
      'in_list'=>true,
      'default_in_list'=>true,
    ),
    'stime' => 
    array (
      'type' => 'int unsigned',
      'editable' => false,
    ),
    'author' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'label'=>app::get('site')->_('作者'),
      'width'=>'100',
      'in_list'=>true,
      'default_in_list'=>true,
    ),
    'site' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
      'label'=>app::get('site')->_('网址'),
      'width'=>'200',
      'in_list'=>true,
      'default_in_list'=>true,
    ),
    'version' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'label'=>app::get('site')->_('版本'),
      'width'=>'80',
      'in_list'=>true,
      'default_in_list'=>true,
    ),
    'info' => 
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'config' => 
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'update_url' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'is_used' =>
    array (
      'type' => 'bool',
      'editable' => false,
      'default' => 'false',
    ),
  ),
  'version' => '$Rev: 40918 $',
    'unbackup' => true,
);

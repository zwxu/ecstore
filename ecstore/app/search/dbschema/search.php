<?php


$db['search']=array (
  'columns' =>
  array (
    'search_id' =>
    array (
      'type' => 'bigint unsigned',
      'extra' => 'auto_increment',
      'pkey' => true,
      'label' => 'ID',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'name' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'is_title'=>true,
      'label'=>app::get('site')->_('搜索方式名称'),
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
    'version' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'label'=>app::get('site')->_('版本'),
      'width'=>'80',
      'in_list'=>true,
      'default_in_list'=>true,
    ),
    'is_used' =>
    array (
      'type' => 'bool',
      'editable' => false,
      'default' => 'false',
    ),
  ),
  'version' => '$Rev: 40918 $',
);

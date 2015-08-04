<?php

 
$db['task']=array (
  'columns' => 
  array (
    'task' => array(
	'type'=>'varchar(100)',
	'pkey'=>true,
	'required'=>true,
	'label' => app::get('base')->_('task'),
	'editable'=>false,
	'is_title'=>true,
	'in_list'=>true,
	'default_in_list'=>true,
	),
    'description' => array(
	'required'=>true,
	'type'=>'varchar(255)',
	'label' => app::get('base')->_('描述'),
	'is_title' => true,
	'in_list' => true,
	'default_in_list' => true,
	),
    'rule' => array(
	'type'=>'varchar(255)',
	'label' => app::get('base')->_('规则'),
	'required'=>true,
	'is_title' => true,
	'in_list' => true,
	'default_in_list' => true,
	),
    'last' => array(
	'type'=>'time',
	'label' => app::get('base')->_('最后执行时间'),
	'required'=>true,
	'is_title' => true,
	'in_list' => true,
	'default_in_list' => true,
	),
    'enabled' => array(
	'type'=>'bool',
	'default'=>'true',
	'label' => app::get('base')->_('开启'),
	'required'=>true,
	'is_title' => true,
	'in_list' => true,
	'default_in_list' => true,
	),
  ),
  'version' => '$Rev: 41137 $',
  'ignore_cache' => true,
);

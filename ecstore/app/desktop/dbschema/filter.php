<?php

 
/**
* @table magicvars;
*
* @package Schemas
* @version $
* @license Commercial
*/

$db['filter']=array (
  'columns' => 
  array (
    'filter_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'filter_name' => 
    array (
      'type' => 'varchar(20)',
      'required' => false,
      'label' => app::get('desktop')->_('筛选器名'),
      'class' => 'span-3',
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
    ),
    'user_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'label' => app::get('desktop')->_('用户id'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'model' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'label' => app::get('desktop')->_('表'),
      'class' => 'span-3',
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
    ),
    'filter_query' => 
    array (
      'type' => 'text',
      'hidden' => true,
      'label' => app::get('desktop')->_('筛选条件'),
      'class' => 'span-4',
      'in_list' => true,
      'editable' => false,
    ),
    'ctl'=>array(
      'type' => 'varchar(100)',
      'required' => true,
      'default'=>'',
      'label' => app::get('desktop')->_('控制器'),
      'class' => 'span-3',
      'editable' => false,
    ),
    'app'=>array(
      'type' => 'varchar(50)',
      'required' => true,
      'default'=>'',
      'label' => app::get('desktop')->_('控制器'),
      'class' => 'span-3',
      'editable' => false,
    ),
    'act'=>array(
      'type' => 'varchar(50)',
      'required' => true,
      'default'=>'',
      'label' => app::get('desktop')->_('方法'),
      'class' => 'span-3',
      'editable' => false,
    ),
    'create_time' => 
    array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'label' => app::get('desktop')->_('建立时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
  ),
);
<?php 

$db['activity']=array ( 
  'columns' =>
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('business')->_('id'),
      'width' => 150,
      'comment' => app::get('business')->_('id'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'act_type' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('活动类型'),
      'width' => 150,
      'comment' => app::get('business')->_('活动类型'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'order_id' => 
    array (
      'type' => 'table:orders@b2c',
      'required' => false,
      'editable' => false,
    ),
    'store_id' =>
    array (
      'type' => 'table:storemanger',
      'label' => app::get('business')->_('店铺id'),
      'width' => 150,
      'comment' => app::get('business')->_('店铺id'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'start_time' => 
    array (
      'type' => 'time',
      'label' => app::get('business')->_('开始时间'),
      'width' => 150,
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'end_time' => 
    array (
      'type' => 'time',
      'label' => app::get('business')->_('终止时间'),
      'width' => 150,
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'desc' =>
    array (
      'type' => 'longtext',
      'comment' => app::get('b2c')->_('说明'),
      'editable' => false,
      'label' => app::get('b2c')->_('说明'),
    ),
  ),
  'comment' => app::get('b2c')->_('商家禁止活动表'),
);

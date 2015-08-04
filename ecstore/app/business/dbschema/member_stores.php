<?php 

 
$db['member_stores']=array ( 
  'columns' => 
  array (
    'snotify_id' => array (
       'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'editable' => false,
      'default_in_list' => true,
      'id_title' => true,
    ),
    'store_id' => array (
      'type' => 'table:storemanger',
      'required' => true,
      'label' => app::get('b2c')->_('店铺ID'),
      'in_list' => true,
    ),
    'member_id' => array(
        'type'=>'table:members@b2c',
        'in_list' => true,
         'label' => app::get('b2c')->_('会员用户名'),
       'default_in_list' => true,
    ),
    'status' => array (
      'type' => "enum('ready', 'send', 'progress')",
      'required' => true,
    ),
    'create_time' => 
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('申请时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'disabled' => array (
      'type' => 'bool',
      'default'=>'false',
    ),
    'remark' => array (
      'type' => 'longtext',
      'default'=>'false',
    ),
    'object_type' =>array(
      'type' => 'varchar(100)',
      'default' => 'stores',
    ),
  ),
  'comment' => app::get('b2c')->_('店铺收藏登记'),
   'engine' => 'innodb',
   'version' => '$Rev$',
);

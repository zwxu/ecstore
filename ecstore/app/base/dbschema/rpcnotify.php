<?php

 
$db['rpcnotify']=array (
    'columns' => 
    array (
    'id' => 
    array (
      'type' => 'bigint unsigned',
      'extra' => 'auto_increment',
      'pkey' => true,
      'label' => 'ID',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'width'=>40,
    ),
    'callback' => 
    array (
      'type' => 'varchar(200)',
      'label'=>'API',
      'required' => true,
      'in_list'=>true,
      'width'=>100,
    ),
    'rsp' =>
    array(
      'type' => array(
            'succ' => app::get('base')->_('成功'),
            'fail' => app::get('base')->_('失败'),
      ),
      'label' => app::get('base')->_('状态'),
      'required' => true,
      'width' => 100,
      'in_list' =>true,
      'default_in_list' => true,
    ),
    'msg'=>array(
      'type' => 'varchar(255)',
      'required' => true,
      'width'=>200,
      'label' => app::get('base')->_('信息'),
      'in_list' =>true,
      'default_in_list' => true,
    ),
    'notifytime' => 
    array (
      'type' => 'time',
      'label' => app::get('base')->_('通知时间'),
      'required' => true,
      'width' => 140,
      'in_list'=> true,
      'default_in_list'=> true,
    ),
    'status' =>
    array(
      'type' => array(
            'true' => app::get('base')->_('已读'),
            'false' => app::get('base')->_('未读'),
      ),
      'label' => app::get('base')->_('状态'),
      'default' => 'false',
      'required' => true,
      'width' => 100,
      'in_list' =>true,
      'default_in_list' => true,
    ),
    ),
  'index' => 
  array (
    'ind_status' => 
    array (
      'columns' => 
      array (
        0 => 'status',
      ),
    ),
  ),
    'engine' => 'innodb',
    'version' => '$Rev: 40912 $',
    'ignore_cache' => true,
);


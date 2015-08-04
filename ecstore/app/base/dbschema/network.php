<?php

 
$db['network']=array (
  'columns' => 
  array (
    'node_id' => array (
      'type' => 'number',
      'label' => 'id',
      'required' => true,
      'width' => 100,
      'in_list' => true,
      'default_in_list' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
    ),
    'node_name' => 
    array (
      'type' => 'varchar(255)',
      'label' => app::get('base')->_('名称'),
      'required' => true,
      'width' => 150,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
    'node_url' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('base')->_('网址'),
      'width' => 150,
      'required' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'node_api' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('base')->_('api地址'),
      'width' => 150,
      'required' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'link_status' => 
    array (
      'type' => 
      array (
        'active' => app::get('base')->_('正常'),
        'group' => app::get('base')->_('维护'),
        'wait' => app::get('base')->_('等待对方确认...'),
      ),
      'default' => 'wait',
      'width' => 100,
      'label' => app::get('base')->_('关联类型'),
      'required' => true,
      'in_list' => true,
    ),
    'node_detail' => 
    array (
      'type' => 'varchar(255)',
      'label' => app::get('base')->_('说明'),
      'width' => 300,
    ),
    'token' => 
    array (
      'type' => 'varchar(32)',
      'label' => app::get('base')->_('验证玛'),
    ),
  ),
  'version' => '$Rev: 41137 $',
  'ignore_cache' => true,
);
<?php

 
$db['flow']=array (
  'columns' => 
  array (
    'flow_id' => 
    array (
      'label' => app::get('desktop')->_('序号'),
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'flow_from' => 
    array (
      'label' => app::get('desktop')->_('发送者'),
      'type' => 
      array (
        'user' => app::get('desktop')->_('管理员'),
        'system' => app::get('desktop')->_('系统'),
        'internet' => app::get('desktop')->_('站外'),
        'user' => app::get('desktop')->_('用户'),
      ),
      'default' => 'system',
      'required' => true,
      'in_list' => true,
    ),
    'from_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
    ),
    'subject' => 
    array (
      'label' => app::get('desktop')->_('消息标题'),
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
      'in_list' => true,
      'is_title' => true,
    ),
    'flow_desc' => 
    array (
      'label' => app::get('desktop')->_('消息描述'),
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'editable' => false,
      'in_list' => true,
    ),
    'body' => 
    array (
      'label' => app::get('desktop')->_('内容本体'),
      'type' => 'text',
      'required' => true,
      'default' => '',
      'editable' => false,
      'in_list' => true,
    ),
    'flow_ip' => 
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'send_mode'=>array(
        'type'=>array(
            'direct'=>app::get('desktop')->_('直送'),
            'broadcast'=>app::get('desktop')->_('广播'),
            'fetch'=>app::get('desktop')->_('收取'),
        ),
        'default' => 'direct',
        'required' => true,
    ),
    'flow_type' => 
    array (
      'type' => 'varchar(32)',
      'default' => 'default',
      'required' => true,
      'editable' => false,
    ),
    'send_time'=>array(
        'type'=>'time',
        'required' => true,
    )
  ),
  'comment' => app::get('desktop')->_('信息表'),
  'version' => '$Rev$',
);

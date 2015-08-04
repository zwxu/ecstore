<?php

 
$db['user_flow']=array (
  'columns' => 
  array (
    'user_id' => array (
      'type' => 'table:users',
      'required' => true,
      'pkey' => true,
    ),
    'flow_id' => array (
      'type' => 'table:flow',
      'required' => true,
      'pkey' => true,
    ),
    'unread' => array (
      'type' => 'bool',
      'required' => true,
      'default'=>'true',
    ),
    'note' => array (
      'type' => 'varchar(50)',
      'default'=>'',
    ),
    'has_star' => array (
      'type' => 'bool',
      'required' => true,
      'default'=>'false',
    ),
    'keep_unread' => array (
      'type' => 'bool',
      'required' => true,
      'default'=>'false',
    ),
  ),
  'comment' => app::get('desktop')->_('信息表'),
  'version' => '$Rev$',
  'ignore_cache' => true,
);

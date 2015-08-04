<?php 

 
$db['member_msg']=array (
  'columns' => 
  array (
    'msg_id' => array (
       'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'for_id' => array(
        'type'=>'int',
        'label' =>app::get('b2c')->_('回复哪个信件'),
        'default' => 0,
    ),
    'from_id' => array (
      'type' => 'table:members',
      'required' => true,
    ),
    'from_uname' => array(
        'type'=>'varchar(100)',
        'label' =>app::get('b2c')->_('发信者'),
        'in_list' => true,
       'default_in_list' => true,
    ),
    'from_type' => array(
        'type'=>'int',
        'label' =>app::get('b2c')->_('发信类型'),
        'default' => 0,
    ),
    'to_id' => array (
      'type' => 'table:members',
      'default' =>0,
      'required' => true,
    ),
    'to_uname' => array(
        'type'=>'varchar(100)',
       'default_in_list' => true,
    ),
    'subject' => array (
      'type' => 'varchar(100)',
       'label' => app::get('b2c')->_('消息主题'),
        'in_list' => true,
        'is_title' =>true,
       'default_in_list' => true,
      'required' => true,
    ),
    'content' => array (
      'type' => 'text',
      'label' => app::get('b2c')->_('内容'),
      'required' => true,
    ),
     'order_id' => array (
      'type' => 'bigint(20)',
      'label' => app::get('b2c')->_('订单ID'),
      'default' =>0,
    ),
    'create_time' => 
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('发送时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'to_time' => 
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('发送时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'has_read' => array (
      'type' => 'bool',
      'label' => app::get('b2c')->_('是否已读'),
      'default'=>'false',
    ),
    'keep_unread' => array (
      'type' => 'bool',
        'label' => app::get('b2c')->_('保持未读'),
      'default'=>'false',
    ),
     'has_star' => array (
      'type' => 'bool',
        'label' => app::get('b2c')->_('是否打上星标'),
      'default'=>'false',
    ),
    'has_sent' => array (
      'type' => 'bool',
      'label' => app::get('b2c')->_('是否发送'),
      'default'=>'true',
    ),
  ),
   'index' => 
    array (
    'ind_to_id' => 
    array (
      'columns' => 
      array (
        0 => 'to_id',
        1 => 'has_read',
        2 => 'has_sent',
      ),
    ),
    'ind_from_id' => 
    array (
      'columns' => 
      array (
        0 => 'from_id',
        1 => 'has_read',
        2 => 'has_sent',
      ),
    ),
  ),
  'comment' => app::get('b2c')->_('信息表'),
   'engine' => 'innodb',
   'version' => '$Rev$',
);

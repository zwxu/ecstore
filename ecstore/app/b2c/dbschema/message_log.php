<?php  

   
$db['message_log']=array ( 
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('主键'),
      'width' => 150,
      'comment' => app::get('b2c')->_('主键'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'method' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('请求方法名称'),
      'width' => 180,
      'is_title' => true,
      'required' => true,
      'comment' => app::get('b2c')->_('请求方法名称'),
      'editable' => true,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'request_time' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('请求时间'),
      'width' => 350,
      'comment' => app::get('b2c')->_('请求时间'),
      'editable' => true,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),

	'target' =>
    array (
      'type' => 'varchar(200)',
      'label' => app::get('b2c')->_('发送目标'),
      'width' => 350,
      'comment' => app::get('b2c')->_('发送目标'),
      'editable' => true,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),

    'ip' =>
    array (
      'type' => 'varchar(20)',
      'comment' => app::get('b2c')->_('请求ip'),
      'editable' => false,
      'label' => app::get('b2c')->_('请求ip'),
    ),
    'memo' =>
    array (
      'type' => 'text',
      'comment' => app::get('b2c')->_('备注'),
      'editable' => false,
      'label' => app::get('b2c')->_('备注'),
     'in_list' => false,
      'default_in_list' => false,
    ),
	'type' =>
    array (
      'type' => array(
		'sms'=>'短信',
		'email'=>'邮件'
	  ),
      'comment' => app::get('b2c')->_('类型'),
      'editable' => false,
      'label' => app::get('b2c')->_('类型'),
     'in_list' => false,
      'default_in_list' => false,
    ),
	'is_pass' =>
    array (
      'type' => array(
		'true'=>'通过',
		'false'=>'拒绝'
	  ),
      'comment' => app::get('b2c')->_('是否通过'),
      'editable' => false,
      'label' => app::get('b2c')->_('是否通过'),
     'in_list' => false,
      'default_in_list' => false,
    ),

  ),
  'comment' => app::get('b2c')->_('发送限制表'),
'index' =>
  array (
    'ind_time' =>
    array (
      'columns' =>
      array (
        0 => 'request_time',
      ),
    ),
    'ind_target' =>
    array (
      'columns' =>
      array (
        0 => 'target',
      ),
    ),
	'ind_type' =>
    array (
      'columns' =>
      array (
        0 => 'type',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42798 $',
);

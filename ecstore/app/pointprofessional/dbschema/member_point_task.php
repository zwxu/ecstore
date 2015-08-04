<?php



$db['member_point_task']=array (
  'columns' => 
  array (
	'member_id' => 
    array (
      'type' => 'table:members@b2c',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'task_name' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
	'point' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'addtime' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'enddate' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => '0',
      'editable' => false,
    ),
    'related_id' => 
    array (
      'type' => 'bigint unsigned',
      'editable' => false,
    ),
    'task_type' => 
    array (
      'type' => array (
        1 => app::get('b2c')->_('购物（订单）'),
        2 => app::get('b2c')->_('评论'),
		3 => app::get('b2c')->_('管理员充值'),
      ),
      'required' => true,
      'default' => 1,
      'editable' => false,
    ),
    'remark' => 
    array (
      'type' => 'varchar(100)',
      'required' => false,
      'default' => '',
      'editable' => false,
      'is_title' => true,
    ),
    'operator' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
	'status' => 
    array (
      'type' => array (
        0 => app::get('b2c')->_('未执行'),
        1 => app::get('b2c')->_('已执行'),
      ),
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'ind_task_type' => 
    array (
      'columns' => 
      array (
        0 => 'task_type',
      ),
    ),
    'ind_status' => 
    array (
      'columns' => 
      array (
        0 => 'status',
      ),
    ),   
  ),
  'engine' => 'innodb',
  'comment' => app::get('b2c')->_('积分定时任务临时表'),
  'version' => '$Rev: 5 $',
);
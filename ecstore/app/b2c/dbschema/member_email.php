<?php 
$db['member_email']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'int(10)', 
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'member_id' => 
    array (
      'type' => 'table:members',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'email_type' => 
    array (
      'is_title' => true,
      'type' => 'varchar(50)',
      'required' => true,
      'editable' => false,
    ),
    'status' => 
    array (
      'type' => 
      array (
        '0' => '不允许发送',
        '1' => '允许发送',
      ),
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    

  ),
   'comment' => app::get('b2c')->_('订阅邮件表'),
  'index' =>
  array (
    'ind_id' =>
    array (
      'columns' =>
      array (
        0 => 'id',
      ),
    ),
    'ind_member_id' =>
    array (
      'columns' =>
      array (
        0 => 'member_id',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42752 $',
);

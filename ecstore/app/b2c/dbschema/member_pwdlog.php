<?php 

 
$db['member_pwdlog']=array (
  'columns' => 
  array (
    'pwdlog_id' => 
    array (
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
    'member_id' => 
    array (
      'type' => 'table:members',
      'required' => true,  
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'secret' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'width' => 110,
      'editable' => true,
      'in_list' => true,
    ),
    'expiretime' => 
    array (
      'type' => 'time',
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'has_used' => 
    array (
      'type' => 'tinybool',
      'default' => 'N',
      'required' => true,
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40654 $',
);

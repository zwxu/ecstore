<?php

 
$db['users']=array (
  'columns' => 
  array (
    'user_id' => 
    array (
      'type' => 'table:account@pam',
      'required' => true,
//      'sdfpath' => 'pam_account/account_id',
      'pkey' => true,
      'label' => app::get('desktop')->_('用户名'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'status' => 
    array (
      'type' => 'intbool',
      'default' => '0',
      'label' => app::get('desktop')->_('启用'),
      'width' => 100,
      'required' => true,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'name' => 
    array (
      'type' => 'varchar(30)',
      'label' => app::get('desktop')->_('姓名'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'lastlogin' => 
    array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'label' => app::get('desktop')->_('最后登陆时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'config' => 
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'favorite' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'super' => 
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'label' => app::get('desktop')->_('超级管理员'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'lastip' => 
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    'logincount' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => app::get('desktop')->_('登陆次数'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    
    
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'op_no' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('desktop')->_('编号'),
      'width' => 30,
      'editable' => true,
      'in_list' => true,
    ),
    'memo' => 
    array (
      'type' => 'text',
      'label' => app::get('desktop')->_('备注'),
      'width' => 270,
      'editable' => false,
      'in_list' => true,
    ),
  ),
  'comment' => app::get('desktop')->_('商店后台管理员表'),
  'index' => 
  array (   
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);

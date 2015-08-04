<?php


$db['openid']=array (
  'columns' =>
  array (
   'member_id' => array (
      'type' => 'table:account@pam',
      'sdfpath' => 'pam_account/account_id',
      'label' => app::get('b2c')->_('用户名'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'openid' =>
    array (
      'type' => 'number',
      'pkey' => true,
      'label' => app::get('openid')->_('openid'),
      'width' => 110,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'provider_openid' =>
    array(
      'type' => 'int(20)',
      'label' => app::get('openid')->_('第三方标识'),
    ),
    'provider_code' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('openid')->_('编码'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'nickname' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('openid')->_('昵称'),
    ),
    'realname' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('openid')->_('真实姓名'),
    ),
    'email' =>
    array (
      'type' => 'varchar(200)',
      'label' => 'EMAIL',
    ),
    'gender' =>
    array (
     'type' =>
      array (
        0 => app::get('openid')->_('未知'),
        1 => app::get('openid')->_('男'),
        2 => app::get('openid')->_('女'),
      ),
      'label' => app::get('openid')->_('性别'),
    ),
    'address' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('openid')->_('地址'),
    ),
    'avatar' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('openid')->_('头像'),
    ),
),
);



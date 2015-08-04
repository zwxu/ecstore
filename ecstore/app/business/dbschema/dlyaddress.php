<?php 


$db['dlyaddress']=array ( 
  'columns' =>
  array (
    'da_id' =>
    array (
      'type' => 'int(10)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'in_list' => false,
    ),
    'address' =>
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('b2c')->_('地址'),
    ),
    'region' =>
    array(
      'type' => 'region',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('b2c')->_('地区'),
    ),
    'zip' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'label' => app::get('b2c')->_('邮编'),
    ),
    'phone' =>
    array(
      'type' => 'varchar(100)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('b2c')->_('电话'),
    ),
    'uname' =>
    array(
      'type' => 'varchar(100)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('b2c')->_('联系人'),
    ),
    'mobile' =>
    array(
      'type' => 'varchar(100)',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('b2c')->_('手机'),
    ),
    'company' =>
    array(
      'type' => 'varchar(100)',
      'editable' => false,
      'label' => app::get('b2c')->_('公司名'),
    ),
    'memo' =>
    array(
      'type' => 'longtext',
      'editable' => false,
      'label' => app::get('b2c')->_('备注'),
    ),
    'consign' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('发货地址'),
    ),
    'refund' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('退货地址'),
    ),
    'pickup' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('自提地址'),
    ),
    'store_id'=>array(
      'type'=>'table:storemanger@business',
      'required' => true,
      'label' => app::get('b2c')->_('店铺'),
      'width' => 110,
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 50831 $',
);

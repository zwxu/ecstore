<?php

 
/**
* @table order_coupon_user;
* @package Schemas
* @version $
* @license Commercial
*/

$db['order_coupon_user']=array (
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ), 
    'order_id' =>
    array (
      'type' => 'table:orders@b2c',
      'required' => true,
      'default' => 0,
      'label' => app::get('couponlog')->_('应用订单号'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'cpns_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => app::get('couponlog')->_('优惠券方案ID'),
      'editable' => false,
      'in_list' => true,
      'searchtype' => 'tequal',
      'filtertype' => 'yes',
    ),
    'cpns_name' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('couponlog')->_('优惠券方案名称'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'usetime' => 
    array (
      'type' => 'time',
      'label' => app::get('couponlog')->_('使用时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'total_amount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'label' => app::get('couponlog')->_('订单金额'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'member_id' => 
    array (
      'type' => 'table:account@pam',
      'label' => app::get('couponlog')->_('使用者'),
      'width' => 110,
      'searchtype' => 'has',
      'filtertype' => false,
      'filterdefault' => 'true',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'memc_code' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('couponlog')->_('使用的优惠券号码'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'cpns_type' =>
    array (
      'type' =>
      array (
        0 => 0,
        1 => 1,
        2 => 2,
      ),
      'label'=>'优惠券类型',
      'comment' => app::get('couponlog')->_('优惠券类型0全局 1用户 2外部优惠券'),
      'editable' => false,
    ),
  ),
  'comment' => app::get('couponlog')->_('优惠券使用记录'),
  'index' =>
  array (
    'ind_cpnsid' =>
    array (
      'columns' =>
      array (
        0 => 'cpns_id',
      ),
    ),
    'ind_cpnscode' =>
    array (
      'columns' =>
      array (
        0 => 'memc_code',
      ),
    ),
    'ind_cpnsname' =>
    array (
      'columns' =>
      array (
        0 => 'cpns_name',
      ),
    ),
  ),
);

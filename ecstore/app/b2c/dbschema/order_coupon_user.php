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
    'order_id' =>
    array (
      'type' => 'table:orders',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'comment' => app::get('b2c')->_('应用订单号'),
      'editable' => false,
    ),
    'cpns_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'comment' => app::get('b2c')->_('优惠券方案ID'),
      'editable' => false,
    ),
    'cpns_name' =>
    array (
      'type' => 'varchar(255)',
      'comment' => app::get('b2c')->_('优惠券方案名称'),
      'editable' => false,
    ),
    'usetime' => 
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('使用时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'memc_code' =>
    array (
      'type' => 'varchar(255)',
      'comment' => app::get('b2c')->_('使用的优惠券号码'),
      'editable' => false,
    ),
    'cpns_type' =>
    array (
      'type' =>
      array (
        0 => 0,
        1 => 1,
        2 => 2,
      ),
      'comment' => app::get('b2c')->_('优惠券类型0全局 1用户 2外部优惠券'),
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('优惠券使用记录'),
);

<?php

 
/**
* @table order_coupon_user;
* @package Schemas
* @version $
* @license Commercial
*/

$db['order_coupon_ref']=array (
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
      'label' => app::get('b2c')->_('应用订单号'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
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
  ),
  'comment' => app::get('couponlog')->_('优惠券使用记录关联表'),
  'index' =>
  array (
    'ind_cpnscode' =>
    array (
      'columns' =>
      array (
        0 => 'memc_code',
      ),
    ),
  ),
);

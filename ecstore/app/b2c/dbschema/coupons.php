<?php 

 
/**
* @table coupons;
* @package dbschema
*/

$db['coupons']=array ( 
  'columns' =>
  array (
    'cpns_id' =>
    array (
      'type' => 'number', 
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('id'),
      'width' => 110,
      'comment' => app::get('b2c')->_('优惠券方案id'),
      'editable' => false,
    ),
    'cpns_name' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('优惠券名称'),
      'searchable' => true,
      'width' => 110,
      'comment' => app::get('b2c')->_('优惠券名称'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'filterdefault'=>true,
    ),

    'store_id' =>
        array (
            'type' => 'varchar(255)',
            'default' => '',
            'label' => app::get('b2c')->_('指定店铺'),
            'editable' => false,
     ),

    'issue_type' =>
    array (
      'type' =>
      array (
        0 => app::get('b2c')->_('平台发行'),
        1 => app::get('b2c')->_('店铺发行'),
        //2 => __('外部优惠券'),
      ),
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('发行类型'),
      'width' => 110,
      'comment' => app::get('b2c')->_('发行类型'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'filterdefault'=>true,
    ),

    'pmt_id' =>
    array (
      'type' => 'number',
      'comment' => app::get('b2c')->_('*暂时废弃'),
      'editable' => false,
    ),
    'cpns_prefix' =>
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('优惠券号码'),
      'width' => 110,
      'comment' => app::get('b2c')->_('生成优惠券前缀/号码(当全局时为号码)'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'filterdefault'=>true,
    ),
    'cpns_gen_quantity' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('获取的总数量'),
      'width' => 110,
      'comment' => app::get('b2c')->_('获取的总数量'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'filterdefault'=>true,
    ),
    'cpns_key' =>
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'width' => 110,
      'comment' => app::get('b2c')->_('优惠券生成的key'),
      'editable' => false,
    ),
    'cpns_status' =>
    array (
      'type' => 'intbool',
      'default' => '1',
      'required' => true,
      'label' => app::get('b2c')->_('是否启用'),
      'width' => 110,
      'comment' => app::get('b2c')->_('优惠券方案状态'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'filterdefault'=>true,
    ),
    'cpns_type' =>
    array (
      'type' =>
      array (
        0 => app::get('b2c')->_('一张无限使用'),
        1 => app::get('b2c')->_('多张使用一次'),
        //2 => __('外部优惠券'),
      ),
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('优惠券类型'),
      'width' => 110,
      'comment' => app::get('b2c')->_('优惠券类型'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'filterdefault'=>true,
    ),
    'cpns_point' =>
    array (
      'type' => 'number',
      'default' => NULL,
      'label' => app::get('b2c')->_('兑换所需积分'),
      'width' => 110,
      'comment' => app::get('b2c')->_('兑换优惠券积分'),
      'editable' => false,
      'in_list' => true,
    ),
    'rule_id'=>
        array(
          'type' => 'table:sales_rule_order',
          'sdfpath' => 'rule/rule_id',
          'default' => NULL,
          'comment' => app::get('b2c')->_('相关的订单促销规则ID'), // rule_type ='C'
          'editable' => false,
    ),
    'num_online' =>
        array (
          'type' => 'number',
          'default' => 0,
          'required' => true,
          'label' => app::get('b2c')->_('线上发放数量'),
          'width' => 110,
          'comment' => app::get('b2c')->_('线上发放数量'),
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filterdefault'=>true,
    ),
    'num_online_limit' =>
        array (
          'type' => 'number',
          'default' => 0,
          'required' => true,
          'label' => app::get('b2c')->_('线上用户领取数量限制'),
          'width' => 110,
          'comment' => app::get('b2c')->_('线上用户领取数量限制'),
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filterdefault'=>true,
    ),
    'online_num' =>
        array (
          'type' => 'number',
          'default' => 0,
          'required' => true,
          'label' => app::get('b2c')->_('已发放的数量'),
          'width' => 110,
          'comment' => app::get('b2c')->_('已发放的数量'),
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
          'filterdefault'=>true,
    ),
  ),

  'comment' => app::get('b2c')->_('优惠券表'),
  'index' =>
  array (
    'ind_cpns_prefix' =>
    array (
      'columns' =>
      array (
        0 => 'cpns_prefix',
      ),
    ),
  ),
);

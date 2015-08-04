<?php

$db['ref']=array (
  'columns' => 
  array (
    'product_id' => 
    array (
      'type' => 'table:products@b2c',
      'required' => true,
      'pkey' =>  true, 
      'label' =>  app::get('gift')->_('赠品ID'),
      'width' => 110,
      'editable' => false,
    ),
    'bn' =>
    array (
      'type' => 'varchar(200)',
      'label' =>  app::get('gift')->_('编号'),
      'width' => 110,
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'goods_id' =>
    array (
      'type' => 'bigint unsigned',
      'default' => 0,
      'required' => true,
      'label' =>  app::get('gift')->_('商品ID'),
      'width' => 110,
      'editable' => false,
    ),
    'cat_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'label' => app::get('gift')->_('分类'),
      'width' => 110,
      'editable' => false,
      'default' => 0,
      'in_list' => true,
      'default_in_list' => true,
    ),
     'name' =>
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'default' => '',
      'label' =>  app::get('gift')->_('赠品名称'),
      'width' => 180,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
   'member_lv_ids' => 
    array (
      'type' => 'varchar(255)',
      'label' =>  app::get('gift')->_('会员等级ID'),
      'width' => 110,
      'editable' => false,
      'default' => '',
    ),
    
    'ifrecommend' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'label' =>  app::get('gift')->_('是否作为推荐赠品'),
      'width' => 110,
      'editable' => false,
      'default_in_list' => true,
      'in_list' => true,
    ),
    'marketable' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'label' =>  app::get('gift')->_('是否发布'),
      'width' => 110,
      'editable' => false,
    ),
    'consume_score' => 
    array (
      'type' => 'number',
      'default' => '0',
      'label' =>  app::get('gift')->_('兑换所需积分'),
      'width' => 110,
      'editable' => false,
    ),
    'from_time' => 
    array (
      'type' => 'time',
      'label' =>  app::get('gift')->_('兑换起始时间'),
      'width' => 110,
      'editable' => false,
      'default_in_list' => true,
      'in_list' => true,
    ),
     'to_time' => 
     array (
      'type' => 'time',
      'label' =>  app::get('gift')->_('兑换结束时间'),
      'width' => 110,
      'editable' => false,
      'default_in_list' => true,
      'in_list' => true,
    ),
    'order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' =>  app::get('gift')->_('排序'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'max_buy_store' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' =>  app::get('gift')->_('每人最大购买数量'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'max_limit' =>
    array (
      'type' => 'number',
      'default' => null,
      'label' =>  app::get('gift')->_('赠品数量'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'real_limit' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' =>  app::get('gift')->_('已赠送赠品数量'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
    ),
    'brief' =>
    array (
      'type' => 'varchar(255)',
      'label' =>  app::get('gift')->_('赠品简介'),
      'width' => 110,
      'hidden' => false,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    
  ),
  'index' =>
  array (
    'index_gift' =>
    array (
      'columns' =>
      array (
        0 => 'product_id',
      ),
    ),
   'index_status' =>
    array (
      'columns' =>
      array (
        0 => 'ifrecommend',
      ),
    ),
  ),
  'comment' =>  app::get('gift')->_('赠品表'),
  'version' => '$Rev: 41329 $',
);


<?php 
$db['violation']=array ( 
  'columns' =>
  array (
    'violation_id' =>
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

    'cat_id' => array (
      'type' => 'table:violationcat@business',
      'label' => app::get('business_business')->_('违规类型'),
      'required' => true,
      'default' => 0,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => true,
    ),

    'score_value' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('扣分节点'),
      'default' => 0,
      'required' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
    ),

    'goods_days' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('限制发布商品天数'),
      'default' => 0,
      'required' => true,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => true,
      'default_in_list' => true,
      'editable' => false,
    ),  

    
    'goodsdown_days' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('下架店铺内所有商品天数'),
      'default' => 0,
      'required' => true,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => true,
      'default_in_list' => true,
      'editable' => false,
    ),  

    'news_days' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('商品降权天数'),
      'default' => 0,
      'required' => true,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => true,
      'default_in_list' => true,
      'editable' => false,
    ),

    'news_days_value' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('商品降权值'),
      'default' => 100,
      'required' => true,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => true,
      'default_in_list' => true,
      'editable' => false,
    ),

    'store_days' =>  
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('店铺屏蔽天数'),
      'default' => 0,
      'required' => true,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => true,
      'default_in_list' => true,
      'editable' => false,
    ),

     'storedown_days' =>  
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('关闭店铺天数'),
      'default' => 0,
      'required' => true,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => true,
      'default_in_list' => true,
      'editable' => false,
    ),

     'sales_days' =>  
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('限制参加营销活动天数'),
      'default' => 0,
      'required' => true,
      'in_list' => true,
      'filtertype' => 'number',
      'filterdefault' => true,
      'default_in_list' => true,
      'editable' => false,
    ),

     'earnest_money' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => false,
      'label' => app::get('b2c')->_('支付违约金'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
      'orderby'=>true,

    ),

    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),

    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),

    'd_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => app::get('b2c')->_('排序'),
      'width' => 30,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),

    'remark' => 
    array (
      'label' => app::get('b2c')->_('备注'),
      'type' => 'text',
      'width' => 75,
      'in_list' => true,
      'default_in_list' => false,
    ),
  ),
   'version' => '$Rev: 40654 $',
);
<?php 
$db['storeviolation']=array ( 
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

    'cat_id' => array (
      'type' => 'table:violationcat@business',
      'label' => app::get('business')->_('违规类型'),
      'required' => true,
      'default' => 0,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'pkey'=>false,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => true,
    ),

     'store_id' =>
    array (
      'type' => 'table:storemanger@business',
      'label' => app::get('b2c')->_('店铺id'),
      'width' => 150,
      'comment' => app::get('b2c')->_('店铺id'),
      'editable' => false,
      'pkey'=>false,
      'in_list' => true,
      'default_in_list' => false,
    ),

    'goods_starttime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('限制发布商品开始时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'goods_endtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('限制发布商品结束时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'goodsdown_starttime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('下架所有商品开始时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'goodsdown_endtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('下架所有商品结束时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

     'news_starttime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('商品降权开始时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'news_endtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('商品降权结束时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

     'news_value' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('商品降权值'),
      'default' =>100,
      'required' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => false,
    ),


      'store_starttime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('店铺屏蔽开始时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'store_endtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('店铺屏蔽结束时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
   'storedown_starttime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('关闭店铺开始时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'storedown_endtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('关闭店铺结束时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'sales_starttime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('限制参加营销活动开始时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'sales_endtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('限制参加营销活动结束时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

     'score' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('business')->_('扣除分数'),
      'default' => 0,
      'required' => true,
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
    ),

    
    'earnest' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => false,
      'label' => app::get('b2c')->_('扣除违约金'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
      'orderby'=>true,

    ),

    'processed' =>
    array (
      'type' => array(
            '0'=>'未处理',
            '1'=>'已处理',
            '9'=>'总计',
      ),
      'default' => '0',
      'label' => app::get('b2c')->_('违规处理状态'),
      'width' => 150,
      'comment' => app::get('b2c')->_('违规处理状态'),
      'editable' => true,
      'in_list' => true,
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
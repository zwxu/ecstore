<?php 

$db['brand']=array ( 
  'columns' =>
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('id'),
      'width' => 150,
      'comment' => app::get('b2c')->_('id'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'brand_id' =>
    array (
      'type' => 'table:brand@b2c',
      'label' => app::get('b2c')->_('品牌id'),
      'width' => 150,
      'comment' => app::get('b2c')->_('品牌id'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'store_id' =>
    array (
      'type' => 'table:storemanger',
      'label' => app::get('b2c')->_('店铺id'),
      'width' => 150,
      'comment' => app::get('b2c')->_('店铺id'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'brand_name' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('品牌名称'),
      'width' => 180,
      'is_title' => true,
      'comment' => app::get('b2c')->_('品牌名称'),
      'editable' => true,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'brand_keywords' =>
    array (
      'type' => 'longtext',
      'label' => app::get('b2c')->_('品牌别名'),
      'width' => 150,
      'comment' => app::get('b2c')->_('品牌别名'),
      'editable' => false,
      'searchtype' => 'has',
       'in_list' => true,
      'default_in_list' => true,
    ),
	'store_cat' => 
    array (
      'type' => 'table:goods_cat@b2c',
      'label' => app::get('b2c')->_('所属分类'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'brand_url' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('品牌网址'),
      'width' => 350,
      'comment' => app::get('b2c')->_('品牌网址'),
      'editable' => true,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'brand_desc' =>
    array (
      'type' => 'longtext',
      'comment' => app::get('b2c')->_('品牌介绍'),
      'editable' => false,
      'label' => app::get('b2c')->_('品牌介绍'),
    ),
    'fail_reason' =>
    array (
      'type' => 'longtext',
      'comment' => app::get('b2c')->_('品牌申请未通过原因'),
      'editable' => false,
      'label' => app::get('b2c')->_('品牌申请未通过原因'),
    ),
    'brand_logo' =>
    array (
      'type' => 'varchar(255)',
      'comment' => app::get('b2c')->_('品牌图片标识'),
      'editable' => false,
      'label' => app::get('b2c')->_('品牌图片标识'),
     'in_list' => false,
      'default_in_list' => false,
    ),
    'brand_aptitude' =>
    array (
      'type' => 'varchar(255)',
      'comment' => app::get('b2c')->_('品牌资质文件'),
      'editable' => false,
      'label' => app::get('b2c')->_('品牌资质文件'),
     'in_list' => false,
      'default_in_list' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'comment' => app::get('b2c')->_('失效'),
      'editable' => false,
      'label' => app::get('b2c')->_('失效'),
      'in_list' => false,
      'deny_export' => true,
    ),
    'status' =>
    array (
      'type' => array(
            '0'=>'待审核',
            '1'=>'审核通过',
            '2'=>'审核不通过',
      ),
      'default' => '0',
      'label' => app::get('b2c')->_('申请状态'),
      'width' => 150,
      'comment' => app::get('b2c')->_('申请状态'),
      'editable' => true,
      'in_list' => true,
    ),
    'type' =>
    array (
      'type' => array(
            '0'=>'新品牌申请',
            '1'=>'品牌使用',
      ),
      'default' => '0',
      'label' => app::get('b2c')->_('申请类型'),
      'width' => 150,
      'comment' => app::get('b2c')->_('申请类型'),
      'editable' => true,
      'in_list' => true,
    ),
  ),
  'comment' => app::get('b2c')->_('品牌关联表'),
);

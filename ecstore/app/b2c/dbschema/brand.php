<?php 
 

$db['brand']=array ( 
  'columns' =>
  array (
    'brand_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('品牌id'),
      'width' => 150,
      'comment' => app::get('b2c')->_('品牌id'),
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
      'required' => true,
      'comment' => app::get('b2c')->_('品牌名称'),
      'editable' => true,
      'searchtype' => 'has',
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
    'brand_setting' =>
    array(
        'type' => 'serialize',
        'label' => app::get('b2c')->_('品牌设置'),
        'deny_export' => true,
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
    'ordernum' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('排序'),
      'width' => 150,
      'comment' => app::get('b2c')->_('排序'),
      'editable' => true,
      'in_list' => true,
    ),
   
    'fav_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    
  ),
  'comment' => app::get('b2c')->_('品牌表'),
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_ordernum' =>
    array (
      'columns' =>
      array (
        0 => 'ordernum',
      ),
    ),
  ),
  'version' => '$Rev: 40654 $',
);

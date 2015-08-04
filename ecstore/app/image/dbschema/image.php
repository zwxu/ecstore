<?php

 
$db['image']=array (
  'columns' => 
  array (
    'image_id' => 
    array (
      'type' => 'char(32)',
      'label'=>app::get('image')->_('图片Id'),
      'required' => true,
      'pkey' => true,
      'width'=>250,
      'in_list'=>true,
      'default_in_list'=>false,
    ),
    'storage'=>array(
      'label'=>app::get('image')->_('存储引擎'),
      'type' => 'varchar(50)',
      'default' => 'filesystem',
      'required' => true,
      'in_list'=>true,
      'width'=>100,
      'default_in_list'=>false,
    ),
    'image_name'=>array(
      'label'=>app::get('image')->_('图片名称'),
      'type' => 'varchar(50)',
      'required' => false,
      'width'=>100,
      'default_in_list'=>true,
    ),
    
    'ident'=>array(
      'type' => 'varchar(200)',
      'required' => true,
    ),
    'url'=>array(
      'label'=>app::get('image')->_('网址'),
      'type'=>'varchar(200)',
      'required' => true,
      'width'=>300,
      'in_list'=>false,
    ),
    'l_ident'=>array(
      'type' => 'varchar(200)',
    ),
    'l_url'=>array(
      'type' => 'varchar(200)',
    ),
    'm_ident'=>array(
      'type' => 'varchar(200)',
    ),
    'm_url'=>array(
      'type' => 'varchar(200)',
    ),
    's_ident'=>array(
      'type' => 'varchar(200)',
    ),
    's_url'=>array(
      'type' => 'varchar(200)',
    ),    
    'width'=>array(
       'label'=>app::get('image')->_('宽度'),
      'type' => 'number',
      'in_list'=>true,
      'default_in_list'=>false,
    ),
    'height'=>array(
      'label'=>app::get('image')->_('高度'),
      'type' => 'number',
      'in_list'=>true,
      'default_in_list'=>false,
    ),
    'watermark'=>array(
        'type'=>'bool',
        'default' => 'false',
        'label'=>app::get('image')->_('有水印'),
        'in_list'=>true,
        'default_in_list'=>true,
    ),
    'last_modified' => array (
      'label'=>app::get('image')->_('更新时间'),
      'type' => 'last_modify',
      'width'=>180,
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list'=>true,
      'default_in_list'=>true,
      'filtertype' => 'yes'
    ),
    'store_id' => 
    array (
      'type' => 'table:storemanger@business',
      'label' => '店铺名称',      
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
      'default' => 0,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40913 $',
);

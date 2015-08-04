<?php 
$db['comment_stores_point']=array ( 
  'columns' => 
  array (
    'id' => array (
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
    'store_id' => array (
      'type' => 'table:storemanger',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'pkey'=>false,
    ),
   
   'type_id' => array (
      'type' => 'table:comment_goods_type@b2c',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'pkey'=>false,
    ),

   'store_region' => 
        array (
          'type' => 'varchar(50)',
          'label' => app::get('b2c')->_('经营范围'),
          'width' => 75,
    ),

    'avg_point' =>
    array (
      'type' => 'decimal(8,2)',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('平均分'),
     
    ),

    'avg_percent' =>
    array (
      'type' => 'decimal(8,2)',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('平均百分比'),
   
    ),
    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
   ),
  'comment_stores_point' => app::get('b2c')->_('店铺评分表'),
  'engine' => 'innodb',
  'version' => '$Rev$',
);
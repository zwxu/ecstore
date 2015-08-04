<?php 
 
 
$db['comment_goods_type']=array (
  'columns' => 
  array (
    'type_id' => array (
        'type' => 'number',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'label' => 'ID',
        'width' => 110,
        'editable' => false,
        'default_in_list' => true,
    ),
    'name' => array (
        'type' => 'varchar(100)',
        'label' => app::get('b2c')->_('评论类型名称'),
         'required' => true,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
  ),
   
  'comment_goods_type' => app::get('b2c')->_('商品评论类型表'),
   'engine' => 'innodb',
   'version' => '$Rev$',
);

<?php 

$db['goods_dly']=array (
  'columns' => 
  array (
    'goods_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'dly_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'manual' => 
    array (
      'type' => 
      array (
        'normal' => app::get('b2c')->_('正商品'),
        'package' => app::get('b2c')->_('捆绑商品'),
      ),
      'pkey' => true,
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('商品运费关系表'),
);
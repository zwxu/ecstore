<?php 
$db['goods_cat_conn']=array ( 
  'columns' => 
  array (
    'goods_id' => 
    array (
      'type' => 'table:goods@b2c',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'cat_id' => 
    array (
      'type' => 'table:goods_cat',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
  ),
);

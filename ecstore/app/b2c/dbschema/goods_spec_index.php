<?php 

 
$db['goods_spec_index']=array (
  'columns' => 
  array (
    'type_id' => 
    array (
      'type' => 'table:goods_type',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'spec_id' => 
    array (
      'type' => 'table:specification',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'spec_value_id' => 
    array (
      'type' => 'table:spec_values',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'goods_id' => 
    array (
      'type' => 'table:goods',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'product_id' => 
    array (
      'type' => 'table:products',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('商品规格索引表'),
  'version' => '$Rev: 40654 $',
);

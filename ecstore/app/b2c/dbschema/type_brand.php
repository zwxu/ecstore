<?php 

 
$db['type_brand']=array (
  'columns' => 
  array (
    'type_id' => 
    array (
      'type' => 'table:goods_type',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'brand_id' => 
    array (
      'type' => 'table:brand',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'brand_order' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
  ),
  'version' => '$Rev: 40654 $',
  'index' =>
  array (
    'ind_brand_id' =>
    array (
      'columns' =>
      array (
        0 => 'brand_id',
      ),
    ),
    'ind_type_id_brand_id' =>
    array (
      'columns' =>
      array (
        0 => 'type_id',
        1 => 'brand_id',
      ),
    ),
  ),
);

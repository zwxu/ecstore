<?php 

 
$db['order_objects']=array (
  'columns' => 
  array (
    'obj_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'order_id' => 
    array (
      'type' => 'table:orders',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'obj_type' => 
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'obj_alias' => 
    array (
      'type' => 'varchar(100)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'goods_id' => 
    array (
      'type' => 'table:goods',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'bn' => 
    array (
      'type' => 'varchar(40)',
      'editable' => false,
      'is_title' => true,
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'amount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'quantity' => 
    array (
      'type' => 'float',
      'default' => 1,
      'required' => true,
      'editable' => false,
    ),
    'weight' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'score' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'ind_obj_bn' =>
    array (
        'columns' =>array(
            0 => 'bn',
        ),
        'type' => 'hash',
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);

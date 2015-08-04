<?php  
 $db['goods_promotion_price']=array ( 
  'columns' => 
  array (
    'goods_id' => 
    array (
      'type' => 'table:goods@b2c',
      'required' => true,
      'default' => 0,
      'pkey' => true,
    ),
    'ref_id' =>
    array (
        'type' => 'int(8)',
        'default' => 0,
        'required' => true,
        'label'=>__('活动ID'),
    ),
    'p_price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true
    ),
    'p_name'=>array(
        'type'=>'varchar(200)',
        'default' => '',
        'label'=>__('活动名称'),
        'editale'=>false,
    ),
    'p_type' => 
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'from_time'=>array(
        'type'=>'time',
        'label'=>__('活动开始时间'),
    ),
    'to_time'=>array(
        'type'=>'time',
        'label'=>__('活动结束时间'),
    ),
  ),
  'index' => 
  array (
    'ind_type_ref' => 
    array (
      'columns' => 
      array (
        0 => 'p_type',
        1 => 'ref_id',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42376 $',
);
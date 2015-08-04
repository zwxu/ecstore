<?php 

 
$db['spec_values']=array (
  'columns' => 
  array (
    'spec_value_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'spec_id' => 
    array (
      'type' => 'table:specification',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'spec_value' => 
    array (
      'type' => 'varchar(100)',
      'default' => '',
      'required' => true,
      'editable' => false,
      'is_title' => true,
    ),
    'alias' => 
    array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => app::get('b2c')->_('规格别名'),
      'width' => 180,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'spec_image' => 
    array (
      'type' => 'table:image@image',
      'default' => '',
      'editable' => false,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'default' => 50,
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('商店中商品规格值'),
  'version' => '$Rev: 42046 $',
);

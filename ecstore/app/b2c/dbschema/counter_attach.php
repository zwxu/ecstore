<?php 

 
$db['counter_attach']=array (
  'columns' => 
  array (
    'counter_value' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('b2c')->_('计数值'),
      'width' => 110,
      'default' => 0,
      'editable' => true,
      'in_list' => true,
    ),

   'attach_id' => 
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('关联id'),
      'width' => 110,
      'pkey' => true,
      'editable' => true,
      'in_list' => true,
    ),
    'counter_id' => array(
        'type' => 'table:counter',
        'required' => true,
        'pkey' => true,
        'label' => 'ID',
    ),
  ),
  'comment' => app::get('b2c')->_('计数表'),
    'index' =>
  array (
    'uni_value' =>
    array (
      'columns' =>
      array (
        0 => 'counter_value',
      ),
  ),
  ),
  'version' => '$Rev$',
);

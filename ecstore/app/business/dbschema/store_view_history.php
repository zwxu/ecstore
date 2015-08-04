<?php 

 
$db['store_view_history']=array (
  'columns' => 
  array (
    'member_id' => 
    array (
      'type' => 'table:members@b2c',
      'required' => true,
      'pkey' => true,
    ),
    'store_id' => 
    array (
      'type' => 'table:storemanger',
      'required' => true,
      'pkey' => true,
    ),
    'last_modify' => 
    array (
      'type' => 'last_modify',
      'label' => '更新时间',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'orderby' => true,
    ),
  ),
  'version' => '$Rev: 40654 $',
);


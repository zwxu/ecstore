<?php 
 $db['last']=array ( 
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'int',
      'required' => true,
      'default' => 0,
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
  'index' => 
  array (
    'ind_id' => 
    array (
      'columns' => 
      array (
        0 => 'id',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42376 $',
);
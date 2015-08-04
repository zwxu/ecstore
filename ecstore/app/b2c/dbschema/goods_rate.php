<?php 

 
/**
* @table goods_rate;
*
* @package Schemas
* @version $
* @license Commercial
*/

$db['goods_rate']=array (
  'columns' => 
  array (
    'goods_1' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'goods_2' => 
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
        'left' => app::get('b2c')->_('单向'),
        'both' => app::get('b2c')->_('关联'),
      ),
      'editable' => false,
    ),
    'rate' => 
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('相关商品表'),
);

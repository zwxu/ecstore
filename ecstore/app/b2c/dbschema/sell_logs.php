<?php 
/**
* @table sell_logs;
* @package Schemas
* @version $
* @license Commercial
*/

$db['sell_logs']=array (
  'columns' =>
  array (
    'log_id' =>
    array (
      'type' => 'mediumint(8)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'member_id' =>
    array (
      'type' => 'table:members',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'name' =>
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'editable' => false,
    ),
    'price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
    ),
    'product_id' =>
    array (
      'type' => 'mediumint(8)',
      'default' => 0,
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
    'product_name' =>
    array (
      'type' => 'varchar(200)',
      'default' => '',
      'editable' => false,
    ),
    'spec_info' =>
    array (
      'type' => 'varchar(200)',
      'default' => '',
      'editable' => false,
    ),
    'number' =>
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
    ),
    'createtime' =>
    array (
      'type' => 'time',
      'editable' => false,
    ),
  ),
  'index' =>
  array (
    'ind_goods_id' =>
    array (
      'columns' =>
      array (
        0 => 'member_id',
        1 => 'product_id',
        2 => 'goods_id',
      ),
    ),
  ),
);
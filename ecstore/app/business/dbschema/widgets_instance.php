<?php 

 
$db['widgets_instance']=array ( 
  'columns' => 
  array (
    'widgets_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'store_id' => 
    array (
      'required' => true,
      'type' => 'table:storemanger',
    ),
    'core_file' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
      'is_title' => true,
    ),
    'core_slot' => 
    array (
      'type' => 'tinyint unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'core_id' => 
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    'widgets_type' => 
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'app' =>
    array (
      'type' => 'varchar(30)',
      'default' => '',
      'editable' => false,
    ),
    'theme' => 
    array (
      'type' => 'varchar(30)',
      'default' => '',
      'editable' => false,
    ),
    'widgets_order' => 
    array (
      'type' => 'tinyint unsigned',
      'default' => 5,
      'required' => true,
      'editable' => false,
    ),
    'title' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'domid' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'border' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'classname' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'tpl' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'params' => 
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'modified' => 
    array (
      'type' => 'time',
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'ind_wgbase' => 
    array (
      'columns' => 
      array (
        0 => 'core_file',
        1 => 'core_id',
        2 => 'widgets_order',
      ),
    ),
    'ind_wginfo' => 
    array (
      'columns' => 
      array (
        0 => 'core_file',
        1 => 'core_slot',
        2 => 'widgets_order',
      ),
    ),
  ),
  'version' => '$Rev$',
    'unbackup' => true,
);

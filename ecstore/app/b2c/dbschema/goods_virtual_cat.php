<?php 

 
/**
* @table goods_virtual_cat;
* @package Schemas
* @version $
*/

$db['goods_virtual_cat']=array (
  'columns' => 
  array (
    'virtual_cat_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('虚拟分类ID'),
      'width' => 110,
      'editable' => false,
    ),
    'virtual_cat_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('虚拟分类名称'),
      'width' => 110,
      'editable' => false,
    ),
    'filter' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'type_id' => 
    array (
      'type' => 'int(10)',
      'label' => app::get('b2c')->_('类型'),
      'width' => 110,
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'parent_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'label' => app::get('b2c')->_('虚拟分类父ID'),
      'width' => 110,
      'editable' => false,
    ),
    'cat_id' => 
    array (
      'type' => 'int(10)',
      'editable' => false,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('排序'),
      'width' => 110,
      'editable' => false,
    ),
    'cat_path' => 
    array (
      'type' => 'varchar(100)',
      'default' => ',',
      'editable' => false,
    ),
    'child_count' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
    ),
    'url' => array(
        'type' => 'varchar(200)',
        'default' => '',
        'required' => true,
        'width' => 110,
        'editable' => false
    )
  ),
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
    'ind_p_order' => 
    array (
      'columns' => 
      array (
        0 => 'p_order',
      ),
    ),
    'ind_cat_path' => 
    array (
      'columns' => 
      array (
        0 => 'cat_path',
      ),
    ),
  ),
);

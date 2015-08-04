<?php 


$db['goods_import_tpl']=array (
  'columns' =>
  array (
    'tpl_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'label' => app::get('b2c')->_('模板编号'),
      'is_title' => true,
      'width' => 110,
      'searchtype' => 'has',
      'editable' => false,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'store_id' => 
    array (
      'type' => 'table:storemanger',
      'required' => true,
      'default' => 0,
    ),
     'cat_id' =>
    array (
      'type' => 'table:goods_cat@b2c',
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('分类'),
      'width' => 75,
      'editable' => true,
      'filtertype' => 'yes',
    ),    
     'type_id' =>
    array (
      'type' => 'table:goods_type@b2c',
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('类型'),
      'width' => 75,
      'editable' => true,
      'filtertype' => 'yes',
    ),
    'cat_name' =>
    array (
      'type' => 'varchar(1000)',
      'editable' => false,
    ),
    'vcode' =>
    array (
      'type' => 'char(4)',
      'editable' => false,
    ),
   'createtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('下载时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
    ),
   'last_modified' =>
    array (
      'label' => app::get('b2c')->_('最后更新时间'),
      'type' => 'last_modify',
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
  ),
  'index' =>
  array (
    'ind_store_id' =>
    array (
      'columns' =>
      array (
        0 => 'store_id',
      ),
    ),
    'ind_createtime' =>
    array (
      'columns' =>
      array (
        0 => 'createtime',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42376 $',
);

<?php 

 
$db['goods_type_props_value']=array (
  'columns' => 
  array (
    'props_value_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('属性值序号'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
  ),
    'props_id' => 
    array (
      'type' => 'table:goods_type_props',
      'required' => true,
      'label' => app::get('b2c')->_('属性序号'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
  ),
    'name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('类型名称'),
      'is_title' => true,
      'width' => 150,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'order_by' => array(
        'type' => 'int',
        'required' => true,
        'default' => 0,
    ),
    'alias' => 
    array (
      'type' => 'varchar(255)',
       'required' => true,
      'default' => '',
     'editable' => false,
    ),
    'lastmodify' => 
    array (
      'label' => app::get('b2c')->_('最后更新时间'),
      'width' => 150,
      'type' => 'time',
      'hidden' => 1,
      'in_list' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('商品属性值表'),
  'index' => 
  array (
    'ind_props_id' => 
    array (
      'columns' => 
      array (
        0 => 'props_id',
      ),
    ),
  ),
  'version' => '$Rev: 40654 $',
);

<?php 

 
$db['specification']=array (
  'columns' => 
  array (
    'spec_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('规格id'),
      'width' => 150,
      'editable' => false,
      'in_list' => false,
    ),
    'spec_name' => 
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'label' => app::get('b2c')->_('规格名称'),
      'width' => 180,
      'editable' => true,
      'in_list' => true,
      'is_title' => true,
      'default_in_list' => true,
    ),
    
    'spec_show_type' => 
    array (
      'type' => 
      array (
        'select' => app::get('b2c')->_('下拉'),
        'flat' => app::get('b2c')->_('平铺'),
      ),
      'default' => 'flat',
      'required' => true,
      'label' => app::get('b2c')->_('显示方式'),
      'width' => 75,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'spec_type' => 
    array (
      'type' => 
      array (
        'text' => app::get('b2c')->_('文字'),
        'image' => app::get('b2c')->_('图片'),
      ),
      'default' => 'text',
      'required' => true,
      'label' => app::get('b2c')->_('类型'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'spec_memo' => 
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'label' => app::get('b2c')->_('规格备注'),
      'width' => 350,
      'editable' => false,
      'in_list' => true,
    'default_in_list' => true,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'deny_export' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'deny_export' => true
    ),
    'alias' => 
    array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => app::get('b2c')->_('规格别名'),
      'width' => 180,
      'in_list' => true,
    ),
  ),
  'comment' => app::get('b2c')->_('商店中商品规格'),
  'version' => '$Rev: 40654 $',
);

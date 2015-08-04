<?php 

 
$db['goods_type_spec']=array (
  'columns' => 
  array (
    'spec_id' => 
    array (
      'type' => 'table:specification',
      'pkey' => true,
      'default' => 0,
      'editable' => false,
    ),
    'type_id' => 
    array (
      'type' => 'table:goods_type',
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'spec_style' => 
    array (
      'type' => 
      array (
        'select' => app::get('b2c')->_('下拉'),
        'flat' => app::get('b2c')->_('平面'),
        'disabled' => app::get('b2c')->_('禁用'),
      ),
      'default' => 'flat',
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('类型 规格索引表'),
  'version' => '$Rev: 40912 $',
);

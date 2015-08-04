<?php
$db['activity']=array (
  'columns' => 
  array (
    'act_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('活动ID'),
      'width' => 110,
      'editable' => false,
    ),
    'act_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('活动名称'),
      'width' => 110,
      'editable' => false,
      'is_title' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'source' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'original_id' => 
    array (
      'type'=>'mediumint(8)',
      'label' => '原活动ID',
      'editable' => false,
    ),
    'banner' => 
    array (
      'type' => 'varchar(32)',
      'label' => '广告图片',
      'editable' => false,
    ),
    'logo' => 
    array (
      'type' => 'varchar(32)',
      'label' => '小图片',
      'editable' => false,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('排序'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
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
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
);
